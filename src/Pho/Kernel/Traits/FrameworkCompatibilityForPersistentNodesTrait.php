<?php

namespace Pho\Kernel\Traits;

use Pho\Lib\Graph;
use Pho\Lib\Graph\ID;
use Pho\Framework;

/**
 * Compat layer between the kernel and lower level packages
 * 
 * This trait is a compatibility layer between the kernel and 
 * lower level packages, namely pho-lib-graph and pho-framework.
 * Both of these packages provide hydration functions useful to
 * implement persistence at higher levels. This trait is 
 * responsible of implementing these hydration/persistence methods
 * for the kernel.
 * 
 * @author Emre Sokullu <emre@phonetworks.org>
 */
trait FrameworkCompatibilityForPersistentNodesTrait {

    protected function onAdd(Graph\NodeInterface $node): void
    {
        // this must be a Graph or SubGraph
        // persist it for members()
        // update index
    }

    protected function onRemove(ID $node_id): void
    {
         // this must be a Graph or SubGraph
        // persist it for members()
        // update index
    }

    // clustertrait
    protected function hydratedGet(ID $node_id): Graph\NodeInterface
    {
        return $this->kernel->utils()->node($node_id);
    }

    // clustertrait
    // not caching on purpose. 
    // [?] it is now.
    protected function hydratedMembers(): array
    {
        foreach($this->node_ids as $node_id) {
            $this->nodes[$node_id] = $this->kernel->utils()->node($node_id);
        }
        return $this->nodes;
    }
    
    // node
    protected function hydratedContext(): Graph\GraphInterface
    {
        $this->context = $this->kernel->utils()->node($this->context_id);
        return $this->context;
    }

    // particletrait
    protected function hydratedCreator(): Framework\Actor
    {
        $this->creator = $this->kernel->utils()->node($this->creator_id);
        return $this->creator;
    }

}