<?php

namespace Pho\Kernel\Foundation;

use Pho\Kernel\Traits\EditableGraphTrait;
use Pho\Framework;
use Pho\Kernel\Kernel;
use Pho\Kernel\Acl;

class Group extends Framework\Frame {
    
    use EditableGraphTrait;

     /**
     * u:: (group owner) f -- can do anything
     * s:: (members) 7 -- anything except manage the group, rad, post, join (see all members only in this case)
     * g:: (people in the same context) 5 -- read contents, ..., join (and see  all members because 4 is  enabled)
     * o:: (people outside) 41 -- ..., ..., subscribe (read limited group info and friends? who are members)
     */
    const DEFAULT_MODE = 0x0f751;

    /**
     * how owner can change the settings
     * e may change sticky bit, allowing others to delete the group and change ACL (practically anything he can do, disabled by default)
     * f can't change his own settings, must remain intact
     * 8 can't give members "manage" privilege, can do anything else: take their write permission to make the group read-only etc.
     * a can't give people in the same network "manage" or "write" privilege, can do anything else. can take subscribe to make it invite-only or read to make it private.
     * a can't give outsiders "manage" or "write" privilege, can do anything else
     */
    const DEFAULT_MASK = 0xef8aa;

    const EDGES_IN = [UserOut\Consume::class, UserOut\Join::class, UserOut\Create::class, UserOut\Subscribe::class];

    public function __construct(Kernel $kernel, Framework\Actor $actor, Framework\ContextInterface $context)
    { 
        parent::__construct($actor, $context);
        $this->loadNodeTrait($kernel);
    }

}