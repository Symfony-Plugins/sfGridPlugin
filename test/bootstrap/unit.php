<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app = 'test';
$env = 'dev';


$plugin_dir = realpath(dirname(__FILE__).'/..');

if (!isset($_SERVER['SYMFONY']))
{
  //throw new RuntimeException('Could not find symfony core libraries.');
//  $_SERVER['SYMFONY'] = $_test_dir.'/../../../lib/symfony';
  $_SERVER['SYMFONY'] = $plugin_dir.'/../../../../sf1.4';  
}

// register symfony files
require_once $_SERVER['SYMFONY'] . '/test/bootstrap/unit.php';
require_once $_SERVER['SYMFONY'] . '/lib/autoload/sfSimpleAutoload.class.php';

$autoload = sfSimpleAutoload::getInstance(sys_get_temp_dir().DIRECTORY_SEPARATOR.sprintf('sf_autoload_unit_propel_%s.data', md5(__FILE__)));
$autoload->addDirectory($plugin_dir.'/../lib');
$autoload->addDirectory($plugin_dir.'/../../sfDataSourcePlugin/lib');
$autoload->register();

//// initialize Context, required for url_for routing context and loading helpers
//require_once(dirname(__FILE__).'/../../../../config/ProjectConfiguration.class.php');
//
//$configuration = ProjectConfiguration::getApplicationConfiguration($app, $env, isset($debug) ? $debug : true);
//sfContext::createInstance($configuration);

// load lime
//require_once $_SERVER['SYMFONY'].'/vendor/lime/lime.php';

class ProjectConfiguration extends sfProjectConfiguration
{
}

class Configuration extends sfApplicationConfiguration
{
  protected $plugins = array('sfPropelPlugin');
}

class TestContext extends sfContext
{ 
  public function loadFactories()
  {
    $this->factories['controller'] = new sfFrontWebController($this);
  }
  
  public function shutdown()
  {
    
  }
  
}

$configuration = new Configuration('unitTest', true);
TestContext::createInstance($configuration, null, 'TestContext');
