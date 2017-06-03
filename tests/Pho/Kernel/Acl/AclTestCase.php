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

class AclTestCase extends Kernel\TestCase {

    protected $user, $stranger, $anonymous;
    protected $created = [];

    public function setUp() {
        parent::setUp();
        $this->user = new Foundation\User($this->kernel);
        $this->stranger = new Foundation\User($this->kernel);
        $this->anonymous = new Foundation\Anonymous($this->kernel);
    }

    public function tearDown() {
        unset($this->user);
        unset($this->stranger);
        unset($this->anonymous);
        foreach($this->created as $c) {
            $this->kernel->database()->del($c);
        }
        parent::tearDown();
    }

}