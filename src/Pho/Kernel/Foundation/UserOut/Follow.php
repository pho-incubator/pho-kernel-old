<?php

namespace Pho\Kernel\Foundation\UserOut;

use Pho\Kernel\Traits\HydratingEdgeTrait;
use Pho\Lib\Graph\NodeInterface;
use Pho\Lib\Graph\PredicateInterface;
use Pho\Framework;

class Follow extends Subscribe {

    const HEAD_LABEL = "follow";
    const HEAD_LABELS = "follows";
    const TAIL_LABEL = "follower";
    const TAIL_LABELS = "followers";
    const SETTABLES = [Framework\Actor::class]; /* inherits the values in Edits */

    public function __construct(NodeInterface $tail, Framework\Frame $head, ?PredicateInterface $predicate = null) 
    {
        parent::__construct($tail, $head, $predicate);
        $head->add($tail);
    }

}