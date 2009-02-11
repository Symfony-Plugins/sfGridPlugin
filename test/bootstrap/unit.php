<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!isset($_SERVER['SYMFONY']))
{
  throw new RuntimeException('Could not find symfony core libraries.');
}

$_test_dir = realpath(dirname(__FILE__).'/..');

// register symfony files
require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

// register plugin libraries
require_once $_SERVER['SYMFONY'].'/autoload/sfSimpleAutoload.class.php';
sfSimpleAutoload::getInstance()->addDirectory(dirname(__FILE__).'/../../lib');
sfSimpleAutoload::register();

// load lime
require_once $_SERVER['SYMFONY'].'/vendor/lime/lime.php';
