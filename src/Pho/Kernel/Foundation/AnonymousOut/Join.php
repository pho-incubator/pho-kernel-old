<?php

namespace Pho\Kernel\Foundation\UserOut;

use Pho\Kernel\Traits\HydratingEdgeTrait;
use Pho\Lib\Graph\NodeInterface;
use Pho\Lib\Graph\PredicateInterface;
use Pho\Framework;

class Join extends Subscribe {

    const HEAD_LABEL = "member";
    const HEAD_LABELS = "members";
    const TAIL_LABEL = "owner";
    const TAIL_LABELS = "owners";
    const SETTABLES = [Framework\Frame::class]; /* inherits the values in Edits */

    public function __construct(NodeInterface $tail, Framework\Frame $head, ?PredicateInterface $predicate = null) 
    {
        parent::__construct($tail, $head, $predicate);
        $head->add($tail);
    }

}