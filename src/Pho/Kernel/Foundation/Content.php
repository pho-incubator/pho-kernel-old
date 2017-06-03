<?php

namespace Pho\Kernel\Foundation;

use Pho\Kernel\Traits\EditableNodeTrait;
use Pho\Framework;
use Pho\Kernel\Kernel;
use Pho\Kernel\Acl;

class Content extends Framework\Object {

    use EditableNodeTrait;

    /**
     * u:: (content owner) f -- can do anything
     * s:: (same as g:: in this case) 5 -- read and react
     * g:: (people in the same context) 5 -- read and react
     * o:: (people outside) 0 -- ..., ..., subscribe (read limited group info and friends? who are members)
     */
    const DEFAULT_MODE = 0x0f550;

    /**
     * how owner can change the settings
     * e may change sticky bit, allowing others to delete the content and change ACL (practically anything he can do, disabled by default)
     * f can't change his own settings, must remain intact
     * 8 may allow subscribers (same as belo) to change the content as well or play with visibility but not delete.
     * 8 may allow group members to change the content as well or play with visibility but not delete.
     * acan't give outsiders "manage" or "write" privilege, can do anything else
     */
    const DEFAULT_MASK = 0xef88a;

    public function __construct(Kernel $kernel, Framework\Actor $actor, Framework\ContextInterface $context)
    { 
        parent::__construct($actor, $context);
        $this->loadNodeTrait($kernel);
    }
}