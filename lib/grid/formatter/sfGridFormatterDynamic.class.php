<?php
/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 * Leon van der Ree <leon@fun4me.demon.nl> 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This abstract class contains basic functionality about how to iterate 
 * over the rows in your DataSource, to render the grid (into text, html, json, xml, etc).
 *
 */
abstract class sfGridFormatterDynamic implements sfGridFormatterInterface, Iterator, Countable
{
  /**
   * The row formatter
   *
   * @var sfGridFormatter * Row // TODO: define a rowFormatterInterface
   */
  protected
    $row        = null;

  /**
   * @var sfGrid
   */
  protected $grid = null;

  /**
   * the row-cursor
   * 
   * @var int
   */
  protected    
    $cursor     = 0;
    
    
  /**
   * constructor of a Grid Formatter
   * 
   * @param sfGrid $grid
   */
  public function __construct(sfGrid $grid)
  {
    $this->grid = $grid;

//    $this->row = new sfGridFormatter * Row($grid, 0);
  }
    

  public function current()
  {
    $this->row->initialize($this->grid, $this->cursor);

    return $this->row;
  }

  public function next()
  {
    ++$this->cursor;
  }

  public function key()
  {
    return $this->cursor;
  }

  public function rewind()
  {
    $this->cursor = 0;
  }

  public function valid()
  {
    return $this->cursor < count($this);
  }

  public function count()
  {
    return count($this->grid);
  }  
}