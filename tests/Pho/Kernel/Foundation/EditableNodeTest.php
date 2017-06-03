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

class EditableNodeTest extends TestCase {

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
        $user_expected_to_be_identical = $this->network->get($user->id());
        $this->assertEquals($user->id(), $user_expected_to_be_identical->id());
        $this->created[] = $user->id();
    }

    public function testAttributeSave() {
        $this->kernel->logger()->info("Testing %s", __FUNCTION__);
        $user = new Foundation\User($this->kernel);
        $user->attributes()->age = 18;
        $user_expected_to_be_identical = $this->network->get($user->id());
        $this->assertEquals(18, $user_expected_to_be_identical->attributes()->age);
        $this->created[] = $user->id();
    }

    public function testAttributeSave_Serialized() {
        $this->kernel->logger()->info("Testing %s", __FUNCTION__);
        $user = new Foundation\User($this->kernel);
        $user->attributes()->age = 18;
        $user_expected_to_be_identical = unserialize(serialize($user));
        $this->assertEquals(18, $user_expected_to_be_identical->attributes()->age);
        $this->created[] = $user->id();
    }

    public function testAttributeSave_FetchedFromDB() {
        $this->kernel->logger()->info("Testing %s", __FUNCTION__);
        $user = new Foundation\User($this->kernel);
        $this->created[] = $user->id();
        $user->attributes()->age = 18;
        $user_expected_to_be_identical = $this->kernel->utils()->node((string)$user->id());
        $this->assertEquals(18, $user_expected_to_be_identical->attributes()->age);
    }

}