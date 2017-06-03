<?php

namespace Pho\Kernel\Traits;

use Pho\Kernel\Kernel;
use Pho\Kernel\Acl;
use Pho\Framework;
use Pho\Lib\Graph\ID;
use Pho\Lib\Graph;
use Pho\Kernel\Foundation;

trait PersistentNodeTrait {

    use FrameworkCompatibilityForPersistentNodesTrait;

    protected $kernel, $graph, $acl;

    public function loadNodeTrait(Kernel $kernel): void
    {
        $this->kernel = $kernel;
        $this->acl = Acl\AclFactory::seed($kernel, $this, self::DEFAULT_MODE);
        //$this->persist($this->loadEditorsFrame());
        
        $this->persist($this->loadEditorsFrame());
    }

    public function loadEditorsFrame(): bool
    {
        return false; // placeholder
    }

    public function observeAttributeBagUpdate(\SplSubject $subject): void
    {
        $this->persist();
    }

    public function acl(): Acl\AbstractAcl
    {
        return $this->acl;
    }

    public function persist(bool $skip = false): void
    {
        //eval(\Psy\sh());
        if($skip) return;
        $this->kernel->database()->set(sprintf("node:%s", $this->id()), serialize($this));
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        if(isset($this->acl))
            $array["acl"] = $this->acl->toArray();
        return $array;
    }

    public function serialize(): string
    {
        $this->kernel->logger()->info("About to serialize the node  %s, a %s", $this->id(), $this->label());
        $x = serialize($this->toArray());
        $this->kernel->logger()->info("The node serialized as: %s", $x);
        return $x;
    }

  public function unserialize(/* mixed */ $data): void
  {
    $this->kernel = $GLOBALS["kernel"];
    $data = unserialize($data);
    $this->id = ID::fromString($data["id"]);
    $this->kernel->logger()->info("Unserialization begins for %s", $this->id());
    $this->attributes = new Graph\AttributeBag($this, $data["attributes"]);
    $this->kernel->logger()->info("The edge list is as follows: %s", print_r($data["edge_list"], true));
    $this->edge_list = new Graph\EdgeList($this, $data["edge_list"]);
    if((string) ID::root() == $data["context"]) {
        $this->context = new Foundation\World($this->kernel);
        $this->context_id = $data["context"];
    }
    else {
        $this->context_id = $data["context"];
    }
    
    $this->creator_id = $data["creator"];
    if(isset($data["current_context"])) { // Actor
        $this->current_context = $this->kernel->utils()->node($data["current_context"]);
    }
    if(isset($data["members"])) { // Frame
        $this->kernel->logger()->info(
            "Extracting members for the frame %s: %s",
            $this->id(),
            print_r($data["members"], true)
        );
        $this->loadNodesFromIDArray($data["members"]);
    }
    if(isset($data["acl"])) {
        $this->acl = Acl\AclFactory::seed($this->kernel, $this, $data["acl"]["permissions"]);
    }

    if(isset($data["editors"])) {
        $this->editors = $this->kernel->utils()->node($data["editors"]);
    }

    $this->setupEdges();
    //$this->loadNodeTrait($this->kernel);
  }


     protected function _callSetter(string $name, array $args): \Pho\Lib\Graph\EntityInterface
     {
         $edge = parent::_callSetter($name, $args);
         $this->kernel->logger()->info("Saving edge %s", $edge->id());
         $this->kernel->database()->set(sprintf("edge:%s", $edge->id()), serialize($edge));
         $this->persist();
         if($edge->tail()->id()==$this->id()) {
            $edge->head()->persist();
         }
         else {
            $edge->tail()->persist();
         }
         return $edge;
     }

     public function hydratedEdge(string $id): Graph\EdgeInterface
   {
       $this->kernel->logger()->info("Hydrating edge %s", $id);
        return $this->kernel->utils()->edge($id);
   }

   public function destroy(): void
   {
    $edges_in = $this->edges()->in();
    $edges_out = $this->edges()->out();
    foreach($edges_in as $edge) {
        $edge->destroy();
    }
    foreach($edges_out as $edge) {
        if($edge->predicate()->binding()) {
            $this->kernel->logger()->info("Deleting edge head node %s with label: %s", $edge->head()->id(), $edge->head()->label());
            $edge->head()->destroy();
        }
        $edge->destroy();
    }
    $this->kernel->logger()->info("Node %s with label: %s has been called for deletion", $this->id(), $this->label());
   $this->kernel->database()->del(sprintf("node:%s", $this->id()));
   parent::destroy();
   }


}