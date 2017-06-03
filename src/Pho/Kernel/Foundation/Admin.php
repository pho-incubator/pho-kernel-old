<?php

namespace Pho\Kernel\Foundation;

use Pho\Framework;
use Pho\Kernel\Kernel;

class Admin extends User {

    public function __construct(Kernel $kernel)
    { 
        $this->graph = $kernel["world"];
        Framework\Actor::__construct($this->graph);
        $this->loadNodeTrait($kernel);
    }

}