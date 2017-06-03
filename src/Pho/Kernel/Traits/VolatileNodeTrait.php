<?php

namespace Pho\Kernel\Traits;

use Pho\Kernel\Kernel;
use Pho\Kernel\Acl;
use Pho\Framework;

trait VolatileNodeTrait  {

    use PersistentNodeTrait;

    public function persist(bool $skip = false): void {}

}