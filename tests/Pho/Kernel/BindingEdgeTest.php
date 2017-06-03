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

use Pho\Lib\Graph;
use Pho\Framework;

/**
     * Let's see if the product nodes also get deleted when a binding
     * edge's tail is destroyed.
     */
class BindingEdgeTest extends TestCase {

    private $created = [];

    private $user, $friend, $content1, $content2, $friendship1, $friendship2, $product1, $product2;
    private  $user_id, $friend_id, $content1_id, $content2_id, $product1_id, $product2_id, $friendship1_id, $friendship2_id;

    public function setUp() {
        parent::setUp();
        $this->user = new Foundation\User($this->kernel);
        $this->friend = new Foundation\User($this->kernel);
        $this->content1 = new Foundation\Content($this->kernel, $this->user, $this->kernel->graph());
        $this->content2 = new Foundation\Content($this->kernel, $this->user, $this->kernel->graph());
        $this->friendship1 = $this->user->subscribe($this->friend);
        $this->friendship2 = $this->friend->subscribe($this->user);
        $this->product1 = $this->user->create($this->content1);
        $this->product2 = $this->user->create($this->content2);
        $this->user_id = $this->user->id();
        $this->friend_id = $this->friend->id();
        $this->content1_id = $this->content1->id();
        $this->content2_id = $this->content2->id();
        $this->product1_id = $this->product1->id();
        $this->product2_id = $this->product2->id();
        $this->friendship1_id = $this->friendship1->id();
        $this->friendship2_id = $this->friendship2->id();
    }

    public function tearDown() {
        foreach($this->created as $c) {
            $this->kernel->database()->del($c);
        }
      parent::tearDown();
    }
    
    public function test1() {
        $this->user->destroy();
        $this->expectException(Exceptions\NodeDoesNotExistException::class);
        $this->kernel->utils()->node($this->user_id);
    }

    public function test2() {
        $this->user->destroy();
        $this->expectException(Exceptions\NodeDoesNotExistException::class);
        $this->kernel->utils()->node($this->content1_id);
    }
    
    public function test3() {
        $this->user->destroy();
        $this->expectException(Exceptions\NodeDoesNotExistException::class);
        $this->kernel->utils()->node($this->content2_id);
    }

    public function test4() {
        $this->user->destroy();
        $this->expectException(Exceptions\EdgeDoesNotExistException::class);
        $this->kernel->utils()->edge($this->product1_id);
    }

    public function test5() {
        $this->user->destroy();
        $this->expectException(Exceptions\EdgeDoesNotExistException::class);
        $this->kernel->utils()->edge($this->product2_id);
    }

    /**
     * Shouldn't affect the other user
     */
    public function test6() {
        $this->user->destroy();
        $this->assertEquals($this->friend_id, $this->kernel->utils()->node($this->friend_id)->id());
    }

    public function test7() {
        $this->user->destroy();
        $this->expectException(Exceptions\EdgeDoesNotExistException::class);
        $this->kernel->utils()->edge($this->friendship1_id);
    }

    public function test8() {
        $this->user->destroy();
        $this->expectException(Exceptions\EdgeDoesNotExistException::class);
        $this->kernel->utils()->edge($this->friendship2_id);
    }

}