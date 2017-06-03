<?php

namespace Pho\Kernel\Foundation\UserOut;

use Pho\Kernel\Traits\HydratingEdgeTrait;
use Pho\Lib\Graph\NodeInterface;
use Pho\Lib\Graph\PredicateInterface;
use Pho\Framework;

class Create extends \Pho\Framework\ActorOut\Write {

    use HydratingEdgeTrait;

    const HEAD_LABEL = "product";
    const HEAD_LABELS = "products";
    const TAIL_LABEL = "creator";
    const TAIL_LABELS = "creators";
    const SETTABLES = [Framework\Frame::class, Framework\Object::class]; /* inherits the values in Edits */

    public function __construct(NodeInterface $tail, NodeInterface $head, ?PredicateInterface $predicate = null) 
    {
        parent::__construct($tail, $head, $predicate);
        $this->kernel = $GLOBALS["kernel"];
        //$this->persist();
    }

}