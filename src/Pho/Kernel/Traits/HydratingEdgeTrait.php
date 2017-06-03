<?php
namespace Pho\Kernel\Traits;

use Pho\Lib\Graph;

trait HydratingEdgeTrait {
    
    private $kernel;

    private function persist(): void
    {
        $this->ensureKernel();
        $this->kernel->database()->set(sprintf("edge:%s", $this->id()), serialize($this));
        $this->persist();
    }

    private function ensureKernel(): void
    {
        if(!isset($this->kernel))
            $this->kernel = $GLOBALS["kernel"];
    }

    public function hydratedHead(): Graph\NodeInterface 
    {
        $this->ensureKernel();
        $this->head = $this->kernel->utils()->node($this->head_id);
        return $this->head;
    }
    
    public function hydratedTail(): Graph\NodeInterface
    {
        $this->ensureKernel();
        $this->tail =  $this->kernel->utils()->node($this->tail_id);
        return $this->tail;
    }
    
    public function hydratedPredicate(): Graph\PredicateInterface
    {
        $this->predicate = (new $this->predicate);
        return $this->predicate;
    }

    public function destroy(): void
   {
        $this->ensureKernel();
        $this->kernel->database()->del(sprintf("edge:%s", $this->id()));
   }
}