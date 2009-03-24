<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfGridFormatterHtmlRow implements ArrayAccess
{
  protected
    $grid     = null,
    $index    = null;

  protected $highlightCondition = array();

  public function __construct(sfGrid $grid, $index)
  {
    $this->initialize($grid, $index);
  }

  public function initialize(sfGrid $grid, $index)
  {
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

    $css = '';
    if (isset($this->highlightCondition['column']))
    {
      if ($source[$this->highlightCondition['column']] === $this->highlightCondition['value'])
      {
        $css = ' class="'.$this->highlightCondition['class'].'"';
      }
    }

    $data = "<tr".$css.">\n";
    foreach ($this->grid->getWidgets() as $column => $widget)
    {
      // First render the body. Possible that the cssTd is changed by it.
      $tagBody = $widget->render($column, $source[$column]);
      
      // Check the css options.      
      if ($widget->getOption('cssTd'))
      {
        $arrOptions = array('class' => $widget->getOption('cssTd'));
      }
      else
      {
        $arrOptions = array();
      }
      $data .= '  '.$widget->renderContentTag('td',
                                         $tagBody,
                                         $arrOptions)
              ."\n";
    }

    return $data . "</tr>\n";
  }

  /**
   * Sets the condition to add a css-class to a row
   *
   * @param string $column  the column name
   * @param mixed  $value   the value the column should be equal to
   * @param string $class   the css-class the row should get
   */
  public function setRowHighlightCondition($column, $value = true, $class='active')
  {
    $this->highlightCondition = array(
      'column' => $column,
      'value'  => $value,
      'class'  => $class,
    );
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