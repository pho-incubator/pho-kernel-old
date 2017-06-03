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

class UserEdgeJoinTest extends TestCase {

    private $created = [];

    public function tearDown() {
        foreach($this->created as $c) {
            $this->kernel->database()->del($c);
        }
      parent::tearDown();
    }

    public function testSimple() {
        \Pho\Lib\Graph\Logger::setVerbosity(1);
        $this->kernel->logger()->info("Testing %s", __FUNCTION__);
        $user = new Foundation\User($this->kernel);
        $group = new Foundation\Group($this->kernel, $user, $this->kernel->graph());
        $user->join($group);
        $this->assertTrue($user->hasContainer($group->id()));
        //$this->assertTrue($user->hasContainer($this->kernel->graph()->id()));
        $this->assertContains($user, $this->kernel->graph()->members());
        $this->assertContains($user, $group->members());
        \Pho\Lib\Graph\Logger::setVerbosity(0);
    }

}