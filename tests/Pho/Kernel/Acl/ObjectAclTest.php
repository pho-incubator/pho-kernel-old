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

class ObjectAclTest extends AclTestCase {


    // 750
    public function testObjectAcl() {
        $this->kernel->logger()->info("Testing %s", __FUNCTION__);
        $content = new Foundation\Content($this->kernel, $this->user, $this->kernel->graph());
        $this->created[] = $content->id();
        $acl = $content->acl();
        $this->go($acl, $content);
    }

    // 750
    public function testObjectAclWithSerialization() {
        $this->kernel->logger()->info("Testing %s", __FUNCTION__);
        $content = new Foundation\Content($this->kernel, $this->user, $this->kernel->graph());
        $this->created[] = $content->id();
        //eval(\Psy\sh());
        $content_serialized = unserialize(serialize($content));
        //eval(\Psy\sh());        
        $acl = $content_serialized->acl();
        $this->go($acl, $content_serialized);
    }

    private function go($acl, $content) {
        // creator
        $this->assertTrue($acl->manageable($this->user), "actor's role is: ".$acl->resolveRole($this->user)." .. actor is: ".$this->user->id()." .. creator is: ".$content->creator()->id());
        $this->assertTrue($acl->writeable($this->user));
        $this->assertTrue($acl->readable($this->user));
        $this->assertTrue($acl->executable($this->user));

        // stranger, they are in the same graph though.
        $this->assertFalse($acl->manageable($this->stranger));
        $this->assertFalse($acl->writeable($this->stranger));
        //eval(\Psy\sh());
        $this->assertTrue($acl->readable($this->stranger), 
            "actor's role is: ".$acl->resolveRole($this->stranger)
            ." .. actor is: ".$this->stranger->id()
            ." .. actor's context is: ".$this->stranger->context()->id()
            ." .. object's context is: ".$content->context()->id()
            ." .. does object's context contain actor? ".  ($content->context()->contains($this->stranger->id()) ? "Y" : "N")
        );
        $this->assertTrue($acl->executable($this->stranger));


        // anonymous, doesn't belong to this graph
        $this->assertFalse($acl->manageable($this->anonymous));
        $this->assertFalse($acl->writeable($this->anonymous));
        $this->assertFalse($acl->readable($this->anonymous));
        $this->assertFalse($acl->executable($this->anonymous));
    }
}