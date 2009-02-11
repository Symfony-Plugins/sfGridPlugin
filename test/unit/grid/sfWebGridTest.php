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
  public function __construct() {}
  
  public function getGetParameters()
  {
    return array(
      'sort'        => 'id',
      'sort_order'  => 'desc',
      'page'        => 2,
      'param'       => 'value',
    );
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
$t->is($g->getUri(), 'http://test.com', '->bind() sets the grid URI to the URI of the given request');
$t->is($g->getSortColumn(), 'id', '->bind() sets the sort column using the GET parameter "sort"');
$t->is($g->getSortOrder(), 'desc', '->bind() sets the sort order using the GET parameter "sort_order"');
$t->is($g->getPager()->getPage(), 2, '->bind() sets the current page using the GET parameter "page"');