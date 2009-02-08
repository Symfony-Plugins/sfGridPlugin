<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface sfGridFormatterInterface extends Iterator, Countable
{
  public function render();
  
  public function renderHead();
  
  public function renderFoot();
  
  public function renderBody();
  
  public function renderColumnHead($column);
}