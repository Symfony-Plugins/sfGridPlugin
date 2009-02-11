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

$t = new lime_test(54, new lime_output_color());

// Iterator interface
$t->diag('Iterator interface');
$f = new sfGridFormatterHtml(new sfGridMock());
$i = 0;
foreach ($f as $key => $row)
{
  $t->is($key, $i, 'sfGridFormatterHtml implements the Iterator interface');
  $t->isa_ok($row, 'sfGridFormatterHtmlRow', 'sfGridFormatterHtml implements the Iterator interface');
  $t->is($row->getIndex(), $i, 'sfGridFormatterHtml implements the Iterator interface');
  
  ++$i;
}

$f = new sfGridFormatterHtml(new sfGridMock(array('id', 'name'), 0));
$t->is(count(iterator_to_array($f)), 0, 'sfGridFormatterHtml implements the Iterator interface');

// Countable interface
$t->diag('Countable interface');
$f = new sfGridFormatterHtml(new sfGridMock());
$t->is(count($f), 9, 'sfGridFormatterHtml implements the Countable interface');

// ->renderColumnHead()
$t->diag('->renderColumnHead()');
$f = new sfGridFormatterHtml($g = new sfGridMock());
$t->is($f->renderColumnHead('id'), '<th>Id</th>', '->renderColumnHead() returns the head of a column');
$t->is($f->renderColumnHead('name'), '<th>Name</th>', '->renderColumnHead() returns the head of a column');

$g->setSortable('id');
try
{
  $f->renderColumnHead('id');
  $t->fail('->renderColumnHead() throws a "LogicException" if the given column is set to sortable, but no URI has been set');
}
catch (LogicException $e)
{
  $t->pass('->renderColumnHead() throws a "LogicException" if the given column is set to sortable, but no URI has been set');
}
$g->setUri('http://test.com');
$t->is($f->renderColumnHead('id'), '<th><a href="http://test.com?sort=id&sort_order=asc">Id</a></th>', '->renderColumnHead() returns the head of a column');
$g->setUri('http://test.com?param=value&sort=column&sort_order=desc');
$t->is($f->renderColumnHead('id'), '<th><a href="http://test.com?param=value&sort=id&sort_order=asc">Id</a></th>', '->renderColumnHead() returns the head of a column');

$g->setSort('id', 'asc');
$t->is($f->renderColumnHead('id'), '<th><a class="sort_asc" href="http://test.com?param=value&sort=id&sort_order=desc">Id</a></th>', '->renderColumnHead() returns the head of a column');

$g->setSort('id', 'desc');
$t->is($f->renderColumnHead('id'), '<th><a class="sort_desc" href="http://test.com?param=value&sort=id&sort_order=asc">Id</a></th>', '->renderColumnHead() returns the head of a column');

$f = new sfGridFormatterHtml(new sfGridMock(array('name')));
try
{
  $f->renderColumnHead('id');
  $t->fail('->renderColumnHead() throws a "LogicException" if the given column has not been configured');
}
catch (LogicException $e)
{
  $t->pass('->renderColumnHead() throws a "LogicException" if the given column has not been configured');
}

// ->renderHead()
$t->diag('->renderHead()');
$f = new sfGridFormatterHtml(new sfGridMock());
$output = <<<EOF
<thead>
<tr>
  <th>Id</th>
  <th>Name</th>
</tr>
</thead>

EOF;
$t->is($f->renderHead(), $output, '->renderHead() returns the head of the HTML table');

$f = new sfGridFormatterHtml(new sfGridMock(array('name')));
$output = <<<EOF
<thead>
<tr>
  <th>Name</th>
</tr>
</thead>

EOF;
$t->is($f->renderHead(), $output, '->renderHead() includes only configured columns');

// ->renderPager()
$t->diag('->renderPager()');
$g = new sfGridMock();
$g->getPager()->setMaxPerPage(2);
$f = new sfGridFormatterHtml($g);
try
{
  $f->renderPager();
  $t->fail('->renderPager() throws a "LogicException" if no URI has been set');
}
catch (LogicException $e)
{
  $t->pass('->renderPager() throws a "LogicException" if no URI has been set');
}

$g->setUri('http://test.com?param=value&page=100');
$output = <<<EOF
<div>
  1
  <a href="http://test.com?param=value&page=2">2</a>
  <a href="http://test.com?param=value&page=3">3</a>
  <a href="http://test.com?param=value&page=4">4</a>
  <a href="http://test.com?param=value&page=5">5</a>
  <a href="http://test.com?param=value&page=2">&raquo;</a>
  <a href="http://test.com?param=value&page=5">&raquo;|</a>
</div>

EOF;
$t->is($f->renderPager(), $output, '->renderPager() renders the pager');

$g->setUri('http://test.com');
$output = <<<EOF
<div>
  1
  <a href="http://test.com?page=2">2</a>
  <a href="http://test.com?page=3">3</a>
  <a href="http://test.com?page=4">4</a>
  <a href="http://test.com?page=5">5</a>
  <a href="http://test.com?page=2">&raquo;</a>
  <a href="http://test.com?page=5">&raquo;|</a>
</div>

EOF;
$t->is($f->renderPager(), $output, '->renderPager() renders the pager');

$g->getPager()->setPage(2);
$output = <<<EOF
<div>
  <a href="http://test.com?page=1">&laquo;</a>
  <a href="http://test.com?page=1">1</a>
  2
  <a href="http://test.com?page=3">3</a>
  <a href="http://test.com?page=4">4</a>
  <a href="http://test.com?page=5">5</a>
  <a href="http://test.com?page=3">&raquo;</a>
  <a href="http://test.com?page=5">&raquo;|</a>
</div>

EOF;
$t->is($f->renderPager(), $output, '->renderPager() renders the pager');

$g->getPager()->setPage(4);
$output = <<<EOF
<div>
  <a href="http://test.com?page=1">|&laquo;</a>
  <a href="http://test.com?page=3">&laquo;</a>
  <a href="http://test.com?page=1">1</a>
  <a href="http://test.com?page=2">2</a>
  <a href="http://test.com?page=3">3</a>
  4
  <a href="http://test.com?page=5">5</a>
  <a href="http://test.com?page=5">&raquo;</a>
</div>

EOF;
$t->is($f->renderPager(), $output, '->renderPager() renders the pager');

$g->getPager()->setPage(5);
$output = <<<EOF
<div>
  <a href="http://test.com?page=1">|&laquo;</a>
  <a href="http://test.com?page=4">&laquo;</a>
  <a href="http://test.com?page=1">1</a>
  <a href="http://test.com?page=2">2</a>
  <a href="http://test.com?page=3">3</a>
  <a href="http://test.com?page=4">4</a>
  5
</div>

EOF;
$t->is($f->renderPager(), $output, '->renderPager() renders the pager');


// ->renderFoot()
$t->diag('->renderFoot()');
$g = new sfGridMock();
$g->setUri('http://test.com');
$f = new sfGridFormatterHtml($g);
$output = <<<EOF
<tfoot>
<tr>
  <th colspan="2">
    9 results
  </th>
</tr>
</tfoot>

EOF;
$t->is($f->renderFoot(), $output, '->renderFoot() renders the foot of the HTML table');

$g->getPager()->setMaxPerPage(2);
$pager = sfGridFormatterHtml::indent($f->renderPager(), 2);
$output = <<<EOF
<tfoot>
<tr>
  <th colspan="2">
$pager
    9 results (page 1 of 5)
  </th>
</tr>
</tfoot>

EOF;
$t->is($f->renderFoot(), $output, '->renderFoot() renders the foot of the HTML table');

$g->getPager()->setPage(2);
$pager = sfGridFormatterHtml::indent($f->renderPager(), 2);
$output = <<<EOF
<tfoot>
<tr>
  <th colspan="2">
$pager
    9 results (page 2 of 5)
  </th>
</tr>
</tfoot>

EOF;
$t->is($f->renderFoot(), $output, '->renderFoot() renders the foot of the HTML table');

$f = new sfGridFormatterHtml(new sfGridMock(array('name')));
$output = <<<EOF
<tfoot>
<tr>
  <th colspan="1">
    9 results
  </th>
</tr>
</tfoot>

EOF;
$t->is($f->renderFoot(), $output, '->renderFoot() includes only the configured columns');

$f = new sfGridFormatterHtml(new sfGridMock(array('id', 'name'), 1));
$output = <<<EOF
<tfoot>
<tr>
  <th colspan="2">
    1 results
  </th>
</tr>
</tfoot>

EOF;
$t->is($f->renderFoot(), $output, '->renderFoot() renders the number of results');

$f = new sfGridFormatterHtml(new sfGridMock(array('id', 'name'), 0));
$output = <<<EOF
<tfoot>
<tr>
  <th colspan="2">
    0 results
  </th>
</tr>
</tfoot>

EOF;
$t->is($f->renderFoot(), $output, '->renderFoot() renders the number of results');

// ->renderBody()
$t->diag('->renderBody()');
$f = new sfGridFormatterHtml(new sfGridMock());
$output = <<<EOF
<tbody>
<tr>
  <td>1</td>
  <td>Fabien</td>
</tr>
<tr>
  <td>2</td>
  <td>Francois</td>
</tr>
<tr>
  <td>3</td>
  <td>Jonathan</td>
</tr>
<tr>
  <td>4</td>
  <td>Fabian</td>
</tr>
<tr>
  <td>5</td>
  <td>Kris</td>
</tr>
<tr>
  <td>6</td>
  <td>Nicolas</td>
</tr>
<tr>
  <td>7</td>
  <td>Fabian</td>
</tr>
<tr>
  <td>8</td>
  <td>Dustin</td>
</tr>
<tr>
  <td>9</td>
  <td>Carl</td>
</tr>
</tbody>

EOF;
$t->is($f->renderBody(), $output, '->renderBody() returns the body of the HTML table');

$f = new sfGridFormatterHtml(new sfGridMock(array('name')));
$output = <<<EOF
<tbody>
<tr>
  <td>Fabien</td>
</tr>
<tr>
  <td>Francois</td>
</tr>
<tr>
  <td>Jonathan</td>
</tr>
<tr>
  <td>Fabian</td>
</tr>
<tr>
  <td>Kris</td>
</tr>
<tr>
  <td>Nicolas</td>
</tr>
<tr>
  <td>Fabian</td>
</tr>
<tr>
  <td>Dustin</td>
</tr>
<tr>
  <td>Carl</td>
</tr>
</tbody>

EOF;
$t->is($f->renderBody(), $output, '->renderBody() includes only configured columns');

// ->render()
$t->diag('->render()');
$f = new sfGridFormatterHtml(new sfGridMock());
$output = $f->renderHead().$f->renderFoot().$f->renderBody();
$t->is($f->render(), $output, '->render() returns the content of the HTML table');
