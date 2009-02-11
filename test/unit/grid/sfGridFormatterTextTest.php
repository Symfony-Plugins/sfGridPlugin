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

$t = new lime_test(4, new lime_output_color());

// Iterator interface
$t->diag('Iterator interface');
$f = new sfGridFormatterText(new sfGridMock());
$i = 0;
foreach ($f as $key => $row)
{
  $t->is($key, $i, 'sfGridFormatterText implements the Iterator interface');
  $t->isa_ok($row, 'sfGridFormatterTextRow', 'sfGridFormatterText implements the Iterator interface');
  $t->is($row->getIndex(), $i, 'sfGridFormatterText implements the Iterator interface');
  
  ++$i;
}

$f = new sfGridFormatterText(new sfGridMock(array('id', 'name'), 0));
$t->is(count(iterator_to_array($f)), 0, 'sfGridFormatterText implements the Iterator interface');

// Countable interface
$t->diag('Countable interface');
$f = new sfGridFormatterText(new sfGridMock());
$t->is(count($f), 9, 'sfGridFormatterText implements the Countable interface');