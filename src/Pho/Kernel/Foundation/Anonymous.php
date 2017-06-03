<?php

namespace Pho\Kernel\Foundation;

use Pho\Kernel\Traits\VolatileNodeTrait;
use Pho\Framework;
use Pho\Kernel\Kernel;

class Anonymous extends User {

    use VolatileNodeTrait;

    const DEFAULT_MODE = 0x00000;
    const DEFAULT_MASK = 0xfffff;

    public function __construct(Kernel $kernel) {
            Framework\Actor::__construct($kernel["world"]);
            $this->loadNodeTrait($kernel);
    }

}