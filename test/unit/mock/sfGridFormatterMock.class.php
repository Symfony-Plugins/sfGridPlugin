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
  /**
   * constructor of a Grid Formatter
   * 
   * @param sfGrid $grid
   */
  public function __construct(sfGrid $grid)
  {
  }

  /**
   * Renders a default text
   */
  public function render()
  {
    return 'rendered';
  }
}