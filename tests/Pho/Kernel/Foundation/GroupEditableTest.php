<?php

/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pho\Kernel;

class GroupEditableTest extends TestCase {

    private $created = [];

    public function tearDown() {
        foreach($this->created as $c) {
            $this->kernel->database()->del($c);
        }
      parent::tearDown();
    }

    public function testSimple() {
        $this->kernel->logger()->info("Testing %s", __FUNCTION__);
        $user = new Foundation\User($this->kernel);
        $user2 = new Foundation\User($this->kernel);
        $group = new Foundation\Group($this->kernel, $user, $this->kernel->graph());
        $this->assertInstanceOf(Foundation\VirtualGroup::class, $group->editors());
        $this->assertTrue($group->acl()->writeable($user));
        $this->assertFalse($group->acl()->writeable($user2));
        //$group->editors()->add($user2);
        $user2->join($group->editors());
        $this->assertTrue($group->editors()->contains($user2->id()));
        $group_again  = $this->kernel->utils()->node($group->id()->toString());
        
        //eval(\Psy\sh());
        $this->assertTrue($group_again->editors()->contains($user2->id()));
        
        //$this->assertTrue($group->acl()->writeable($user2));
    }

}