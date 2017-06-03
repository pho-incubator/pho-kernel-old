<?php

namespace Pho\Kernel\Traits;

use Pho\Lib\Graph\NodeInterface;
use Pho\Lib\Graph\ID;

trait EditableGraphTrait {

    use EditableNodeTrait;
    
    public function onAdd(NodeInterface $node): void
    {
        $this->persist();
    }

    public function onRemove(ID $node_id): void
    {
        $this->persist();
    }

     public function hydratedGet(ID $node_id): NodeInterface
    {
        return $this->kernel->utils()->node((string)$node_id);
    }

    public function hydratedMembers(): array
    {
        foreach($this->node_ids as $node_id) {
            $node_id = (string) $node_id;
            $this->nodes[$node_id] = $this->kernel->utils()->node($node_id);
        }
        return $this->nodes;
    }
}