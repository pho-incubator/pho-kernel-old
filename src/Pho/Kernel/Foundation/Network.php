<?php

namespace Pho\Kernel\Foundation;

use Pho\Framework;
use Pho\Kernel\Kernel;

class Network extends Group {

    public function __construct(Kernel $kernel, Framework\Actor $creator)
    { 
        parent::__construct($kernel, $creator, $kernel["world"]);
        $creator->changeContext($this);
    }

}