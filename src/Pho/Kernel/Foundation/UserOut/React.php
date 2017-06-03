<?php

namespace Pho\Kernel\Foundation\UserOut;

use Pho\Kernel\Traits\HydratingEdgeTrait;
use Pho\Lib\Graph\NodeInterface;
use Pho\Lib\Graph\PredicateInterface;
use Pho\Framework;

class React extends Subscribe {

    const HEAD_LABEL = "reactee";
    const HEAD_LABELS = "reactees";
    const TAIL_LABEL = "reactor";
    const TAIL_LABELS = "reactors";
    const SETTABLES = [Framework\Frame::class]; /* inherits the values in Edits */

    public function __construct(NodeInterface $tail, Framework\Frame $head, ?PredicateInterface $predicate = null) 
    {
        parent::__construct($tail, $head, $predicate);
        $head->add($tail);
    }

}