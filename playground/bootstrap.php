<?php
/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/************************************************************
 * This script shows you how to interact with the Pho Kernel
 *
 * @author Emre Sokullu
 ************************************************************/

 // 1. Initiate the autoloaders first.
require(__DIR__."/../vendor/autoload.php");

// 2. Set up the configuration.
$configs = array();
$configs["services"]["database"] = "redis://127.0.0.1:6379";
$configs["services"]["storage"] = "filesystem:///tmp/pho";
$configs["services"]["index"] = "mysql://root:root@127.0.0.1:3306/pho";

// 3. For the sake of this demonstration, let's create a Redis Client that we
// can play with. You won't need to do this otherwise.
$redis = new Predis\Client($configs["services"]["database"]["uri"]);

// 4. Time to boot up the pho kernel.
use Pho\Kernel;
$kernel = new Kernel\Kernel($configs);
$kernel->boot();

// 5. $network is the main graph.
$network = $kernel->graph();

// 6. the $admin is created automatically, it is the founder.
$admin = $network->creator();

// 7. let's create an object
$content = new Kernel\Foundation\Content($kernel, $admin, $network);


/********************************************************
 * In this section we will define some general-purpose
 * functions that you may use
 ********************************************************/

/**
 * Lists Redis keys
 *
 * @return void
 */
function ls(): void
{
  global $redis;
  print_r($redis->keys("*"));
}

/**
 * Cleans up the redis database. Use with caution!
 *
 * @return void
 */
function cleanup(): void
{
  global $redis;
  $redis->flushdb();
}

