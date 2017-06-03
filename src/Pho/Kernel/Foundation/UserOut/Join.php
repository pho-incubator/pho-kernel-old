<?php

namespace Pho\Kernel\Foundation\UserOut;

use Pho\Kernel\Traits\HydratingEdgeTrait;
use Pho\Lib\Graph\NodeInterface;
use Pho\Lib\Graph\PredicateInterface;
use Pho\Framework;

class Join extends Subscribe {

    const HEAD_LABEL = "container";
    const HEAD_LABELS = "containers";
    const TAIL_LABEL = "member";
    const TAIL_LABELS = "members";
    const SETTABLES = [Framework\Frame::class]; /* inherits the values in Edits */

    public function __construct(NodeInterface $tail, Framework\Frame $head, ?PredicateInterface $predicate = null) 
    {
        parent::__construct($tail, $head, $predicate);
        $head->add($tail);
    }

}