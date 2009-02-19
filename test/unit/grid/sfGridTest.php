<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../mock/sfGridFormatterMock.class.php');
require_once(dirname(__FILE__).'/../mock/sfDataSourceMock.class.php');

class sfGridTest extends sfGrid
{
  public 
    $configured = false;
    
  public function configure()
  {
    $this->configured = true;
    $this->setColumns(array('id', 'name'));
  }
}

class sfGridFormatterInvalid {}

class sfWidgetMock extends sfWidget 
{
  public function render($name, $value = null, $attributes = array(), $errors = array()) {}
}

class sfWidgetMock2 extends sfWidget 
{
  public function render($name, $value = null, $attributes = array(), $errors = array()) {}
}

$t = new lime_test(51, new lime_output_color());

// ->__construct()
$t->diag('->__construct()');
$g = new sfGridTest(array(
  array('id' => 1, 'name' => 'Fabien'),
  array('id' => 2, 'name' => 'Francois'),
));
$t->ok($g->getDataSource() instanceof sfDataSourceInterface, '->__construct() accepts an array as data source');

$g = new sfGridTest(new sfDataSourceMock());
$t->ok($g->getDataSource() instanceof sfDataSourceInterface, '->__construct() accepts a class implementing sfDataSourceInterface as data source');

try
{
  $g = new sfGridTest('foobar');
  $t->fail('->__construct() throws an "InvalidArgumentException" when other data sources are given');
}
catch (InvalidArgumentException $e)
{
  $t->pass('->__construct() throws an "InvalidArgumentException" when other data sources are given');
}

$g = new sfGridTest(new sfDataSourceMock());
$t->ok($g->configured, '->__construct() calls the method ->configure()');

// ->getDataSource()
$t->diag('->getDataSource()');
$g = new sfGridTest($s = new sfDataSourceMock());
$t->ok($g->getDataSource() !== $s, '->getDataSource() returns the data source of the pager');
$t->ok($g->getDataSource() === $g->getPager()->getDataSource(), '->getDataSource() returns the data source of the pager');

// ->getColumns(), ->setColumns(), ->hasColumn()
$t->diag('->getColumns(), ->setColumns(), ->hasColumn()');
$g = new sfGridTest(new sfDataSourceMock());
$columns = array('id', 'name');
$g->setColumns($columns);
$t->is($g->getColumns(), $columns, '->getColumns() returns the configured columns of the grid');

$t->ok($g->hasColumn('name'), '->hasColumn() returns "true" if the column is configured');
$t->ok(!$g->hasColumn('foobar'), '->hasColumn() returns "false" if the column is configured');

try
{
  $g->setColumns(array(new stdClass));
  $t->fail('->setColumns() throws an "InvalidArgumentException" if any of the given column names is not a string');
}
catch (LogicException $e)
{
  $t->pass('->setColumns() throws an "InvalidArgumentException" if any of the given column names is not a string');
}
try
{
  $g->setColumns(array('id', 'foobar'));
  $t->fail('->setColumns() throws a "LogicException" if any of the given columns does not exist in the data source');
}
catch (LogicException $e)
{
  $t->pass('->setColumns() throws a "LogicException" if any of the given columns does not exist in the data source');
}

// ->getFormatter(), ->setFormatter()
$t->diag('->getFormatter(), ->setFormatter()');
$g = new sfGridTest(new sfDataSourceMock());
$t->is($g->getFormatter(), null, '->getFormatter() returns "null" when no formatter has been associated');
$f = new sfGridFormatterMock();
$g->setFormatter($f);
$t->is($g->getFormatter(), $f, '->getFormatter() returns the formatter associated with the grid');

// ->setFormatterName()
$t->diag('->setFormatterName()');
$g = new sfGridTest(new sfDataSourceMock());
$g->setFormatterName('mock');
$t->isa_ok($g->getFormatter(), 'sfGridFormatterMock', '->setFormatterName() sets a formatter by its name');
try
{
  $g->setFormatterName('foobar');
  $t->fail('->setFormatterName() throws an "UnexpectedValueException" if the given formatter does not exist');
}
catch (UnexpectedValueException $e)
{
  $t->pass('->setFormatterName() throws an "UnexpectedValueException" if the given formatter does not exist');
}
try
{
  $g->setFormatterName('invalid');
  $t->fail('->setFormatterName() throws an "UnexpectedValueException" if the given formatter does not implement sfGridFormatterInterface');
}
catch (UnexpectedValueException $e)
{
  $t->pass('->setFormatterName() throws an "UnexpectedValueException" if the given formatter does not implement sfGridFormatterInterface');
}

// ->render()
$t->diag('->render()');
$g = new sfGridTest(new sfDataSourceMock());

try
{
  $g->render();
  $t->fail('->render() throws a "LogicException" if no formatter has been set');
}
catch (LogicException $e)
{
  $t->pass('->render() throws a "LogicException" if no formatter has been set');
}

$g->setFormatter(new sfGridFormatterMock());
$t->is($g->render(), 'rendered', '->render() renders the grid as HTML');

// Countable interface
$t->diag('Countable interface');
$g = new sfGridTest(new sfDataSourceMock());
$t->is(count($g), 9, 'sfGrid implements the Countable interface');

// ->setSort(), ->getSort()
$t->diag('->setSort(), ->getSort()');
$g = new sfGridTest(new sfDataSourceMock());
$g->setSort('id');
$t->is($g->getSortColumn(), 'id', '->setSort() sets the sort order to sfGrid::DESC by default');
$t->is($g->getSortOrder(), sfGrid::ASC, '->setSort() sets the sort order to sfGrid::DESC by default');
$t->is($g->getDataSource()->sortedBy, array('id', sfDataSourceInterface::ASC), '->setSort() sorts the data source');
$g->setSort('id', sfGrid::DESC);
$t->is($g->getSortOrder(), sfGrid::DESC, '->setSort() accepts the constant sfGrid::DESC');
$t->is($g->getDataSource()->sortedBy, array('id', sfDataSourceInterface::DESC), '->setSort() sorts the data source');
$g->setSort('id', sfGrid::ASC);
$t->is($g->getSortOrder(), sfGrid::ASC, '->setSort() accepts the constant sfGrid::ASC');
$t->is($g->getDataSource()->sortedBy, array('id', sfDataSourceInterface::ASC), '->setSort() sorts the data source');
$g->setSort('id', 'desc');
$t->is($g->getSortOrder(), sfGrid::DESC, '->setSort() accepts the string "desc"');
$t->is($g->getDataSource()->sortedBy, array('id', sfDataSourceInterface::DESC), '->setSort() sorts the data source');
$g->setSort('id', 'asc');
$t->is($g->getSortOrder(), sfGrid::ASC, '->setSort() accepts the string "asc"');
$t->is($g->getDataSource()->sortedBy, array('id', sfDataSourceInterface::ASC), '->setSort() sorts the data source');
$g->setColumns(array('id'));
$g->setSort('name');
$t->is($g->getSortColumn(), 'name', '->setSort() also works with columns that exist in the data source but have not been configured');

try
{
  $g->setSort('id', 'foobar');
  $t->fail('->setSort() throws a "DomainException" when the sort order is invalid');
}
catch (DomainException $e)
{
  $t->pass('->setSort() throws a "DomainException" when the sort order is invalid');
}

try
{
  $g->setSort('foobar');
  $t->fail('->setSort() throws a "LogicException" when the given column does not exist');
}
catch (LogicException $e)
{
  $t->pass('->setSort() throws a "LogicException" when the given column does not exist');
}

// ->getPager()
$t->diag('->getPager()');
$g = new sfGrid($s = new sfDataSourceMock());
$t->isa_ok($g->getPager(), 'sfDataSourcePager', '->getPager() returns a pager for the data source');
$t->is($g->getPager()->getDataSource()->count(), $s->count(), '->getPager() returns a pager for the data source');

// ->setUri()
$t->diag('->setUri()');
$g = new sfGrid(new sfDataSourceMock());
$g->setUri('http://mydomain.com');
$t->is($g->getUri(), 'http://mydomain.com', '->setUri() sets the URI used by formatters');
$g->setUri('http://mydomain.com?param=value');
// Bernhard, just like mentioned in sfGridFormatterHtmlTest I changed this, so no more parameters (these can be stored in the user session)... 
$t->is($g->getUri(), 'http://mydomain.com', '->setUri() sets the URI used by formatters');
//try
//{
//  $g->setUri('invaliduri');
//  $t->fail('->setUri() throws an "UnexpectedValueException" if not given a valid URI, got: '.$g->getUri());
//}
//catch (UnexpectedValueException $e)
//{
//  $t->pass('->setUri() throws an "UnexpectedValueException" if not given a valid URI');
//}

// ->setSortable(), ->getSortable()
$t->diag('->setSortable(), ->getSortable()');
$g = new sfGrid(new sfDataSourceMock());
try
{
  $g->setSortable('id');
  $t->fail('->setSortable() throws a "LogicException" if the given column has not been configured');
}
catch (LogicException $e)
{
  $t->pass('->setSortable() throws a "LogicException" if the given column has not been configured');
}
$g->setColumns(array('id', 'name'));
$g->setSortable(array('id', 'name'));
$t->is($g->getSortable(), array('id', 'name'), '->setSortable() accepts multiple column names as array');
$g->setSortable('id');
$t->is($g->getSortable(), array('id'), '->setSortable() accepts a single column name as string');
$g->setSortable(sfGrid::ALL);
$t->is($g->getSortable(), array('id', 'name'), '->setSortable() accepts the constant sfGrid::ALL');

// ->setWidget(), ->getWidget(), ->setWidgets(), ->getWidgets()
$t->diag('->setWidget(), ->getWidget(), ->setWidgets(), ->getWidgets()');
$g = new sfGridTest(new sfDataSourceMock());
$widgets = array(
  'id'   => new sfWidgetMock(),
  'name' => new sfWidgetMock(),
);
$g->setWidgets($widgets);
$t->is($g->getWidgets(), $widgets, '->setWidgets() sets the widgets of the grid');

$widgets['name'] = new sfWidgetMock2();
$g->setWidgets(array('name' => $widgets['name']));
$t->is($g->getWidgets(), $widgets, '->setWidgets() allows setting widgets for only some columns without modifying other widgets');

try
{
  $g->setWidgets(array('id' => new sfWidgetMock(), 'foobar' => new sfWidgetMock()));
  $t->fail('->setWidgets() throws a "LogicException" when any of the given column names was not configured');
}
catch (LogicException $e)
{
  $t->pass('->setWidgets() throws a "LogicException" when any of the given column names was not configured');
}

$widgets['id'] = new sfWidgetMock2();
$g->setWidget('id', $widgets['id']);
$t->is($g->getWidgets(), $widgets, '->setWidget() sets single widgets');

$t->is($g->getWidget('id'), $widgets['id'], '->getWidget() returns single widgets by column name');
try
{
  $g->getWidget('foobar');
  $t->fail('->getWidget() throws a "LogicException" when the given column name was not configured');
}
catch (LogicException $e)
{
  $t->pass('->getWidget() throws a "LogicException" when the given column name was not configured');
}

try
{
  $g->setWidget('foobar', new sfWidgetMock());
  $t->fail('->setWidget() throws a "LogicException" when the given column name was not configured');
}
catch (LogicException $e)
{
  $t->pass('->setWidget() throws a "LogicException" when the given column name was not configured');
}

$g = new sfGridTest(new sfDataSourceMock());
$t->is(array_keys($g->getWidgets()), $g->getColumns(), 'sfWidgetText', '->getWidgets() returns sfWidgetText instances for every column by default');
foreach ($g->getWidgets() as $widget)
{
  $t->isa_ok($widget, 'sfWidgetText', '->getWidgets() returns sfWidgetText instances for every column by default');
}
