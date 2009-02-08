<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfGridFormatterMock implements sfGridFormatterInterface
{
  public function render()
  {
    return 'rendered';
  }
  
  public function renderHead() {}
  public function renderFoot() {}
  public function renderBody() {}
  public function renderColumnHead($column) {}
  public function current() {}
  public function next() {}
  public function rewind() {}
  public function key() {}
  public function valid() {}
  public function count() {}
}