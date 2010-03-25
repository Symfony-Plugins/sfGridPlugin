<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../mock/sfGridMock.class.php');
require_once(dirname(__FILE__).'/../mock/sfWidgetMock.class.php');

$t = new lime_test(9, new lime_output_color());

// ->__construct()
$t->diag('->__construct()');
$grid = new sfGridMock();
$f = new sfGridFormatterHtmlRow($grid, 1);
$t->is($f->getIndex(), 1, '__construct() initializes the formatter');

// We don't throw out of bound exceptions anymore, we do this lazy
//try
//{
//  new sfGridFormatterHtmlRow($grid, 1000);
//  $t->fail('__construct() throws an "OutOfBoundsException" if the given index does not exist');
//}
//catch (OutOfBoundsException $e)
//{
//  $t->pass('__construct() throws an "OutOfBoundsException" if the given index does not exist');
//}

// ->initialize()
$t->diag('->initialize()');
$f = new sfGridFormatterHtmlRow(new sfGridMock(), 0);

$grid = new sfGridMock();
$f->initialize($grid, 1);
$t->is($f->getIndex(), 1, 'initialize() initializes the formatter');

// We don't throw out of bound exceptions anymore, we do this lazy
//try
//{
//  $f->initialize($grid, 1000);
//  $t->fail('initialize() throws an "OutOfBoundsException" if the given index does not exist');
//}
//catch (OutOfBoundsException $e)
//{
//  $t->pass('initialize() throws an "OutOfBoundsException" if the given index does not exist');
//}

// -> render()
$t->diag('->render()');
$g = new sfGridMock();
$g->setWidget('id', new sfWidgetMock());
$g->setWidget('name', new sfWidgetMock());
$f = new sfGridFormatterHtmlRow($g, 1);
$output = <<<EOF
<tr>
  <td>%2%</td>
  <td>%Francois%</td>
</tr>

EOF;
$t->is($f->render(), $output, 'render() renders a grid row using the grid widgets');

// ArrayAccess interface
$t->diag('ArrayAccess interface');
$f = new sfGridFormatterHtmlRow(new sfGridMock(), 1);
$t->is($f['id'], '2', 'sfGridFormatterHtmlRow implements the ArrayAccess interface');
$t->is($f['name'], 'Francois', 'sfGridFormatterHtmlRow implements the ArrayAccess interface');
$t->ok(isset($f['id']), 'sfGridFormatterHtmlRow implements the ArrayAccess interface');
$t->ok(!isset($f['foobar']), 'sfGridFormatterHtmlRow implements the ArrayAccess interface');

try
{
  $f['foo'] = 'bar';
  $t->fail('sfGridFormatterHtmlRow throws a "LogicException" when indices are modified');
}
catch (LogicException $e)
{
  $t->pass('sfGridFormatterHtmlRow throws a "LogicException" when indices are modified');
}

try
{
  unset($f['name']);
  $t->fail('sfGridFormatterHtmlRow throws a "LogicException" when indices are modified');
}
catch (LogicException $e)
{
  $t->pass('sfGridFormatterHtmlRow throws a "LogicException" when indices are modified');
}
