<?php

namespace Pho\Kernel\Foundation;

use Pho\Kernel\Traits\EditableNodeTrait;
use Pho\Framework;
use Pho\Kernel\Kernel;
use Pho\Kernel\Acl;

class ContentDraft extends Framework\Object {

    use VolatileNodeTrait;

    /**
     * u:: (content owner) f -- can do anything
     * s:: (same as g:: in this case) 5 -- read and react
     * g:: (people in the same context) 5 -- read and react
     * o:: (people outside) 0 -- ..., ..., subscribe (read limited group info and friends? who are members)
     */
    const DEFAULT_MODE = 0x0f000;

    /**
     * how owner can change the settings
     * fffff - can't change anything
     * acan't give outsiders "manage" or "write" privilege, can do anything else
     */
    const DEFAULT_MASK = 0xfffff;

    public function __construct(Kernel $kernel, Framework\Actor $actor, Framework\ContextInterface $context)
    { 
        parent::__construct($actor, $context);
        $this->loadNodeTrait($kernel);
    }

    public function post(/* context */)
    {
        
    }
}