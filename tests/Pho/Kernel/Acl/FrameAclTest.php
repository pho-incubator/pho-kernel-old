<?php

/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pho\Kernel\Acl;

use Pho\Kernel;
use Pho\Kernel\Foundation;

class FrameAclTest extends AclTestCase {

    // 754
    public function testFrameAcl() {
        $this->kernel->logger()->info("Testing %s", __FUNCTION__);
        $group = new Foundation\Group($this->kernel, $this->user, $this->kernel->graph());
        $this->created[] = $group->id();
        $acl = $group->acl();

        $this->go($acl, $group);
        
    }


    public function testFrameAclSerialized() {
        $this->kernel->logger()->info("Testing %s", __FUNCTION__);
        $group = new Foundation\Group($this->kernel, $this->user, $this->kernel->graph());
        $this->created[] = $group->id();

        $group_serialized = unserialize(serialize($group));
        $this->go($group_serialized->acl(), $group_serialized);
        
    }

    private function go($acl, $group) {
        // creator
        $this->assertTrue($acl->manageable($this->user));
        $this->assertTrue($acl->writeable($this->user));
        $this->assertTrue($acl->readable($this->user));
        $this->assertTrue($acl->executable($this->user));

        // $GLOBALS["dur"] = true;
        // stranger, they are in the same graph but not a member
        $this->assertFalse($acl->manageable($this->stranger));
        $this->assertFalse($acl->writeable($this->stranger));
        $this->assertTrue($acl->readable($this->stranger));
        $this->assertTrue($acl->executable($this->stranger));

        // stranger, now a member
        $this->stranger->subscribe($group);
        $this->assertFalse($acl->manageable($this->stranger));
        $this->assertTrue($acl->writeable($this->stranger));
        $this->assertTrue($acl->readable($this->stranger));
        $this->assertTrue($acl->executable($this->stranger));

        // anonymous, doesn't belong to this graph
        $this->assertFalse($acl->manageable($this->anonymous));
        $this->assertFalse($acl->writeable($this->anonymous));
        $this->assertFalse($acl->readable($this->anonymous));
        $this->assertTrue($acl->executable($this->anonymous));
    }

}