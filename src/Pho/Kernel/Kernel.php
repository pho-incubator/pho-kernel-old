<?php

/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pho\Kernel;

use Pho\Lib\Graph;
use Pho\Framework;
use Pho\Kernel\Services\Exceptions\AdapterNonExistentException;

/**
 * Pho Kernel is a programmable social graph interface.
 *
 * Pho Kernel is static, therefore you cannot run multiple kernels in a single
 * PHP thread. Yo may however halt the kernel (via halt() method) and relaunch.
 *
 * Example usage:
 *  ```php
 *  $kernel = new Pho\Kernel\Kernel();
 *  $kernel->boot();
 *  $network = $kernel->graph();
 *  print_r($network->members());
 *  ```
 * 
 * @method Services\ServiceInterface config() Returns kernel configuration as a Zend\Config\Config object.
 * @method Services\ServiceInterface events() Returns events broker.
 * @method Services\ServiceInterface logger() Returns Logger service.
 * @method Services\ServiceInterface database() Returns database service.
 * @method Services\ServiceInterface storage() Returns blob storage service.
 *
 * @author Emre Sokullu
 */
class Kernel extends \Pimple {

  /**
   * @var boolean
   */
  private $is_running = false;

  /**
   * @var bool
   */
  private $is_configured = false;

  /**
   * @var array
   */
  private $class_registry = [];

  /**
   * Constructor.
   *
   * @param $settings  array  Service configurations.
   */
  public function __construct( array $settings = [] )
  {
    $GLOBALS["kernel"] = &$this;
    $this->reconfigure( $settings );
    
  }

  public function halt(): void
  {
    if(!$this->is_running) {
      throw new Exceptions\KernetNotRunningException();
    }
    $this->is_running = false;
    unset($GLOBALS["kernel"]);
  }


  /**
   * Sets up the kernel settings.
   * 
   * Configuration variables can be passed to the kernel either
   * at construction (e.g. ```$kernel = new Kernel(...);```)
   * or afterwards, using this function ```$kernel->reconfigure(...);```
   * Please note, this function should be run before calling the
   * *boot* function, or otherwise it will not have an effect
   * and throw the {@link Pho\Kernel\Exceptions\KernelIsAlreadyRunningException} exception.
   *
   * @param array $settings Service configurations.
   *
   * @throws KernelIsAlreadyRunningException When run after the kernel has booted up.
   */
  public function reconfigure( array $settings = [] ): void
  {
    if($this->is_running) {
      throw new Exceptions\KernelAlreadyRunningException("You cannot reconfigure a running kernel.");
    }
    $this["settings"] = $settings;
    $this["config"] = $this->share(function($c) {
        $config =  new \Zend\Config\Config(include __DIR__ . DIRECTORY_SEPARATOR . "Defaults.php");
        $config->merge(new \Zend\Config\Config($c["settings"]));
        return $config;
    });
    $this->is_configured = true;
  }

  /**
   * Initializes the kernel.
   * 
   * Once the configuration is set, run "boot" to start the kernel.
   * Please note, you will not be able to reconfigure the kernel or
   * register new nodes after this point, or you will encounter the
   * {@link Pho\Kernel\Exceptions\KernelIsAlreadyRunningException} 
   * exception.
   *
   * @throws KernelIsAlreadyRunningException When run after the kernel has booted up.
   *
   */
  public function boot(): void
  {
    if($this->is_running) {
      throw new Exceptions\KernelAlreadyRunningException();
    }
    $this->is_running = true;
    $this->setupServices();
    $this["utils"] = $this->share(function($c) {
      return new Utils($c);
    });
    $this->seedRoot();
    $this->registerListeners($this["graph"]);

    $this->events()->emit("kernel.booted_up");
  }

  /**
    * Sets up kernel services.
    *
    * Private method that readies kernel services according to user settings and system
    * defaults. The services don't initialize right away but start when requested.
    */
   private function setupServices(): void
   {
       $service_factory = new Services\ServiceFactory($this);
       foreach($this->config()->services->toArray() as $key => $service) {
           $this[$key] = $this->share( function($c) use($key, $service, $service_factory) {
             $parameters = parse_url($service); // first parameter scheme, the rest optional ones.
             try {
               return $service_factory->create($key, array_shift($parameters), $parameters); 
             }
             catch(AdapterNonExistentException $e) {
              $this->logger()->warning("The service %s - %s does not exist.", $key, $service);
             }
        });
       }
   }

   /**
    * @internal
    *
    * This magic method is used as a shortcut to kernel services. It works
    * in conjunction with Pimple container. 
    *
    * @param string $name Method name.
    * @param string $arguments Method arguments.
    * @return mixed
    */
   public function __call($name, $arguments) {
        if( $this->is_configured && 
        ( in_array($name, ["config", "utils", "graph", "world"]) || // preset ones.
          in_array($name, array_keys($this["config"]->services->toArray())))
        
        ) {
            return $this[$name];
        }
   }

   /**
    * Registers listeners that are default to the kernel.
    *
    * @param AbstractContext $graph The graph object to start traversal from.
    */
    private function registerListeners(Graph\GraphInterface $graph): void
   {
     $this->logger()->info(sprintf(
       "Registering listeners."
     ));

     $nodes = array_values($graph->members());
     //array_unshift($nodes, $graph);
     $node_count = count($nodes);

     $this->logger()->info(sprintf(
       "Total # of nodes for the graph \"%s\": %s", $graph->id(), (string) $node_count
     ));

     for($i=0; $i<$node_count; $i++) {

       $node = $nodes[$i];

       $this->logger()->info(sprintf(
         "Registering listeners for node %s, a %s", $node->id(), $node->label()
       ));

       $ref = new \ReflectionObject($node);
       $ref_methods = $ref->getMethods( \ReflectionMethod::IS_PUBLIC );

/*       $this->logger()->info(sprintf(
         "Node methods are: %s", print_r($ref_methods, true)
       ));
*/
       array_walk($ref_methods, function($item, $key) use ($node) {
         if(preg_match("/^handle([A-Z][a-z]+)([A-Z][a-z]+)$/",$item->name,$item_parts)) {

           $this->logger()->info(sprintf(
             "Adding a listener on %s with id %s", $node->label(), $node->id()
           ));

           $this->events()->on(
            strtolower($item_parts[1].".".$item_parts[2]), [$node, $item->name]
          );
         }
       });

       // recursiveness
       if($node instanceof Graph\GraphInterface and $node->id() != $graph->id())   {
         $this->registerListeners($node);
       }

       // memory management.
       // clean up after use.
       unset($nodes[$i]); // $nodes[$i] = null;
       // gc_collect_cycles(); // perhaps give the user option to choose Fast Boot vs Memory Controlled Boot (good for resource-constrainted distributed systems that may tolerate slow boot)

     }
   }


   /**
    * Ensures that there is a root Graph attached to the kernel.
    * Used privately by the kernel.
    */
   private function seedRoot(): void
   {
       
       $this["world"] = $this->share(function($c) {
        return new Foundation\World($c);
       });
       $network_id = $this->database()->get("configs:network_id");
       $creator_id = $this->database()->get("configs:creator_id");

       if(isset($network_id) && isset($creator_id)) {
         $this->logger()->info(
           "Existing network with id: %s and creator: %s", 
           $network_id,
           $creator_id
         );

         $creator = $this->utils()->node($creator_id);
         $this["graph"] = $this->share(function($c) use($network_id) {
            return $c["utils"]->node($network_id);
          });

       }
       else {
          if(isset($network_id)) {
            throw new Exceptions\LostCreatorException();
          }
          else if (isset($creator_id)) {
            throw new Exceptions\LostNetworkException();
          }
          
          
          $creator = new Foundation\Admin($this); // will turn into admin by Network
          $this["graph"] = $this->share(function($c) use($creator) {
            return new Foundation\Network($c, $creator);
          });
         $this->database()->set("configs:network_id", $this->graph()->id());
         $this->database()->set("configs:creator_id", $creator->id());
         $this->logger()->info(sprintf(
           "New network with id: %s and creator: %s", 
           $this->graph()->id(),
           $creator->id()
         ));
       }
       
   }

   public function register(array $classes): void
   {
     $type = function(string $class): string
     {
        if($class instanceof Framework\Actor) return "actor";
        if($class instanceof Framework\Object) return "object";
        if($class instanceof Framework\Frame) return "graph";
     };
     foreach($classes as $class) {
        if(!$class instanceof Framework\ParticleInterface) {
          $this->logger()->warning();
          continue;
        }
        $this->class_registry[$type($class)][] = $class;
     }
   }



  /**
   * Retrieves the root graph.
   * 
   * After the kernel is booted, you can call this function to
   * get the root node in the graph (which is the graph itself)
   * and play with it.
   *
   * @return Pho\Lib\Graph\GraphInterface
   */
  /*public function graph(): Graph\GraphInterface
  {
    if(!$this->is_running) {
      throw new Exceptions\KernelNotRunningException("You must boot up the kernel before requesting the root graph.");
    }
    return $this->graph; 
  }*/


  /**
   * Initializes a session
   * 
   * Please note this method does not perform the actual 
   * authentication. It orders the kernel to accept
   * given actor with the authentication permission, 
   * hence creates a new session. The actual authentication
   * should take place at higher levels via password,
   * SAML, oAuth, JWT or any of the methods that the job 
   * requires.
   * 
   * @param Pho\Lib\Graph\ID Actor ID.
   *
   * @return Pho\Kernel\Session
   */
  /*
  public function authenticate(Graph\ID $actor_id): Session
  {
    if(!$this->is_running) {
      throw new Exceptions\KernelNotRunningException("You must boot up the kernel before requesting the authentication interface.");
    }
    try {
      $actor = $this->utils()->node($actor_id);
    }
    catch(\Exception $e) {
      throw $e;
    }
    if(!$actor instanceof Nodes/Foundation/Actor) {
      throw new Exceptions\InvalidTypeException(sprintf("The id \"%s\" does not pertain to an Actor node.", (string) $node_id));
    }
    return Session::initialize($actor);
  }
  */

}