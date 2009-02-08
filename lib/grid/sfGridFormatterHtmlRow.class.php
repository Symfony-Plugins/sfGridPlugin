<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfGridFormatterHtmlRow implements ArrayAccess
{
  protected
    $grid     = null,
    $index    = null;
    
  public function __construct(sfGrid $grid, $index)
  {
    $this->initialize($grid, $index);
  }
    
  public function initialize(sfGrid $grid, $index)
  {
    if ($index >= count($grid))
    {
      throw new OutOfBoundsException(sprintf('The row with index "%s" does not exist', $index));
    }
    
    $this->grid = $grid;
    $this->index = $index;
  }
  
  public function getGrid()
  {
    return $this->grid;
  }
  
  public function getIndex()
  {
    return $this->index;
  }
  
  public function render()
  {
    $source = $this->grid->getDataSource();
    $source->seek($this->index);
    
    $data = "<tr>\n";
    foreach ($this->grid->getWidgets() as $column => $widget)
    {
      $data .= "  <td>" . $widget->render($column, $source[$column]) . "</td>\n";
    }
    
    return $data . "</tr>\n";
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