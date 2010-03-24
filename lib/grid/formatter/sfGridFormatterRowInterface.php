<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 * (c) Leon van der Ree <leon@fun4me.demon.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This interface is used to render rows of a Grid
 * 
 */
interface sfGridFormatterRowInterface extends ArrayAccess
{
/**
   * Constructs a new sfGridFormatter * Row to render rows
   *
   * @param sfGrid $grid  the grid that this row-formatter should render
   * @param int $index    The index to which to set the internal row pointer
   */
  public function __construct(sfGrid $grid, $index);

  /**
   * Initialises the new sfGridFormatter * Row
   *
   * @param sfGrid $grid  the grid that this row-formatter should render
   * @param int $index    The index to which to set the internal row pointer
   */
  public function initialize(sfGrid $grid, $index);
  
  /**
   * Renders the row
   *
   * @return string
   */
  public function render();
}