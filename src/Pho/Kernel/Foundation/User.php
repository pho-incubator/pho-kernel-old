<?php

namespace Pho\Kernel\Foundation;

use Pho\Kernel\Traits\PersistentNodeTrait;
use Pho\Framework;
use Pho\Kernel\Kernel;


class User extends Framework\Actor {

    use PersistentNodeTrait;

    /**
     * u:: (myself) f -- can do anything
     * s:: (subscribers or friends) 7 -- read profile, send message, see friends
     * g:: (people in the same context) 5 -- read profile, ..., subscribe (and see friends because 4 is enabled)
     * o:: (people outside) 2 -- ..., ..., subscribe (read limited profile)
     */
    const DEFAULT_MODE = 0x0f751;

    /**
     * how owner can change the settings
     * f can't change sticky bit, must remain unchanged
     * f can't change his own settings, must remain intact
     * 8 can't give subscribers "manage" privilege, can do anything else
     * 8 can't give people in the same network "manage" privilege, can do anything else
     * 8 can't give outsiders "manage" privilege, can do anything else
     */
    // https://www.cyberciti.biz/tips/understanding-linux-unix-umask-value-usage.html
    const DEFAULT_MASK = 0xff888;

    

    public function __construct(Kernel $kernel)
    { 
        $this->graph = $kernel["graph"];
        parent::__construct($this->graph);
        $this->loadNodeTrait($kernel);
    }

    public function subscribes(Framework\ParticleInterface $particle): Framework\ParticleInterface
    {
        if(!$particle->acl()->executable($this)) {

        }
        $edge = parent::subscribes($particle);
        $this->kernel["database"]->set($edge->id(), json_encode($edge->toArray()));
        return $edge();
    }

    public function draftContent(string $content): ContentDraft
    {

        //return new ContentDraft($this->id(), $this->pwd(), $content);
    }

}