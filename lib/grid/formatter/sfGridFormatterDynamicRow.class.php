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
abstract class sfGridFormatterDynamicRow implements sfGridFormatterRowInterface
{
  protected
    $grid             = null,
    $index            = null;

  /**
   * Constructs a new sfGridFormatter Row to render rows
   *
   * @param sfGrid $grid  the grid that this row-formatter should render
   * @param int $index    The index to which to set the internal row pointer
   */
  public function __construct(sfGrid $grid, $index)
  {
    $this->initialize($grid, $index);
  }

  /**
   * Initialises the new sfGridFormatter Row 
   *
   * @param sfGrid $grid  the grid that this row-formatter should render
   * @param int $index    The index to which to set the internal row pointer
   */  
  public function initialize(sfGrid $grid, $index)
  {
    $this->grid = $grid;
    $this->index = $index;
  }  
  
  /**
   * Returns the internal row pointer
   *
   * @return int
   */
  public function getIndex()
  {
    return $this->index;
  }
  
  public function offsetGet($key)
  {
    $source = $this->grid->getDataSource();
    $source->seek($this->index);

    return $source[$key];
  }

  public function offsetSet($key, $value)
  {
    throw new LogicException('Modification of fields is not allowed');
  }

  public function offsetExists($key)
  {
    return $this->grid->hasColumn($key);
  }

  public function offsetUnset($key)
  {
    throw new LogicException('Modification of fields is not allowed');
  }
}