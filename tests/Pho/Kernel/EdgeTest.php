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

class EdgeTest extends TestCase {

    private $created = [];

    public function tearDown() {
        foreach($this->created as $c) {
            $this->kernel->database()->del($c);
        }
      parent::tearDown();
    }

    public function testUserConsume() {
        $this->kernel->logger()->info("Testing %s", __FUNCTION__);
        $user = new Foundation\User($this->kernel);
        $content = new Foundation\Content($this->kernel, $user, $this->kernel->graph());
        $edge = $user->consume($content);
        $this->assertInstanceOf(Foundation\UserOut\Consume::class, $edge);
        $this->assertInstanceOf(Framework\ActorOut\Read::class, $edge);
        $this->created[] = $user->id();
        $this->created[] = $content->id();
        $this->created[] = $edge->id();
    }

    /**
     * //_expectedException \Exception
     * 
     * Noteworthy, because Writes is an edge defined in the Pho\Framework
     * Need to redefine all edges.
     */
    /*
    public function testUserWrite() {
        $this->kernel->logger()->info("Testing %s", __FUNCTION__);
        $user = new Foundation\User($this->kernel);
        $content = new Foundation\Content($this->kernel, $user, $this->kernel->graph());
        $this->created[] = $user->id();
        $this->created[] = $content->id();
        $edge = $user->writes($content);
        //$this->assertInstanceOf(\Pho\Framework\ActorOut\Writes::class, $edge);
    }
    */

    public function testUserSubscribe() {
        $this->kernel->logger()->info("Testing %s", __FUNCTION__);
        $user = new Foundation\User($this->kernel);
        $content = new Foundation\Content($this->kernel, $user, $this->kernel->graph());
        $this->assertCount(0, $user->getSubscriptions());
        $this->assertCount(0, $content->getSubscribers());
        $subscription = $user->subscribe($content);
        $this->assertInstanceOf(Foundation\UserOut\Subscribe::class, $subscription);
        $subscription = $user->subscribe($content)();
        $this->assertInstanceOf(Foundation\Content::class, $subscription);
        $this->assertCount(2, $user->getSubscriptions());
        $this->assertCount(2, $content->getSubscribers());
        $this->created[] = $user->id();
        $this->created[] = $content->id();
        //$this->created[] = $user->id();
    }
    
     public function testUserSubscribePersistence() {
        $this->kernel->logger()->info("Testing %s", __FUNCTION__);
        $user = new Foundation\User($this->kernel);
        $content = new Foundation\Content($this->kernel, $user, $this->kernel->graph());
        $subscription = $user->subscribe($content)();
        $edge = $content->edges()->in()->current();
        $this->created[] = $user->id();
        $this->created[] = $content->id();
        $this->created[] = $edge->id();
        $this->assertInstanceOf(\Pho\Lib\Graph\EdgeInterface::class, $edge);
        $this->assertEquals($subscription->id(), $content->id());
        $this->assertInstanceOf(\Pho\Lib\Graph\EdgeInterface::class, $this->kernel->utils()->edge($edge->id()));
    }


    public function testPersistentUserSubscriptions() {
        $this->kernel->logger()->info("Testing %s", __FUNCTION__);
        $user = new Foundation\User($this->kernel);
        $content = new Foundation\Content($this->kernel, $user, $this->kernel->graph());
        $subscription = $user->subscribe($content);
        $edge = $content->edges()->in()->current();
        $user_id = $user->id();
        $content_id = $content->id();
        $edge_id = $edge->id();
        unset($user);
        unset($content);
        unset($subscription);
        unset($edge);
        $user_again = $this->kernel->utils()->node($user_id);
        $content_again = $this->kernel->utils()->node($content_id);
        //eval(\Psy\sh());
        $subscriptions_again = $user_again->getSubscriptions();
        $edge_again = $subscriptions_again[0]->edges()->in()->current();
        $this->assertEquals($edge_id, $edge_again->id());
        $this->assertEquals($content_id, $subscriptions_again[0]->id());
    }

    /**
     * Unlike the testPersistenceUserSubscriptions,
     * This is a test with an edge that is completely rewritten with new
     * labels.
     * 
     * @return void
     */
    public function testPersistenceUserProducts() {
        $this->kernel->logger()->info("Testing %s", __FUNCTION__);
        $user = new Foundation\User($this->kernel);
        $content = new Foundation\Content($this->kernel, $user, $this->kernel->graph());
        $subscription = $user->create($content);
        $edge = $content->edges()->in()->current();
        $user_id = $user->id();
        $content_id = $content->id();
        $edge_id = $edge->id();
        unset($user);
        unset($content);
        unset($subscription);
        unset($edge);
        $user_again = $this->kernel->utils()->node($user_id);
        $content_again = $this->kernel->utils()->node($content_id);
        //eval(\Psy\sh());
        $creations_again = $user_again->getProducts();
        $edge_again = $creations_again[0]->edges()->in()->current();
        $this->assertEquals($edge_id, $edge_again->id());
        $this->assertEquals($content_id, $creations_again[0]->id());
    }


}