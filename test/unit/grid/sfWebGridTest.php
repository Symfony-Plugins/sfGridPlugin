<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../mock/sfDataSourceMock.class.php');

// initialize Context, required for url_for routing context
require_once(dirname(__FILE__).'/../../../../../config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration($app, $env, isset($debug) ? $debug : true);
sfContext::createInstance($configuration);  

$context = sfContext::getInstance();
$context->set('user', new FakeUser());


class FakeUser
{
  public function setAttribute()
  {
  }
  
  public function getAttribute()
  {
    return null;
  }
  
  public function shutdown()
  {
  }
  
}

class sfWebGridTest extends sfWebGrid
{
  public function configure()
  {
    $this->setColumns(array('id', 'name'));
    $this->getPager()->setMaxPerPage(2);
  }
}

class sfWebRequestMock extends sfWebRequest
{
  protected $getParams = array(
    'sort'        => 'id',
    'type'        => 'desc',
    'page'        => 2,
    'param'       => 'value',
  );
  
  public function __construct() {
    $this->parameterHolder = new sfParameterHolder();

    $this->parameterHolder->add($this->getParams);
  }
      
  public function getGetParameters()
  {
    return $this->getParams;
  }

  public function getParameter($name, $default = null)
  {
    return $this->parameterHolder->get($name, $default);
  }
  
  
  public function getUri()
  {
    return 'http://test.com';
  }
}

$t = new lime_test(4, new lime_output_color());

// ->bind()
$t->diag('->bind()');
$request = new sfWebRequestMock();
$g = new sfWebGridTest(new sfDataSourceMock());
$g->bind($request);
$t->is($g->getUri(), '/', '->bind() sets the grid URI to the URI of the given request, relative path!');
$t->is($g->getSortColumn(), 'id', '->bind() sets the sort column using the GET parameter "sort"');
$t->is($g->getSortOrder(), 'desc', '->bind() sets the sort order using the GET parameter "type"');
$t->is($g->getPager()->getPage(), 2, '->bind() sets the current page using the GET parameter "page"');