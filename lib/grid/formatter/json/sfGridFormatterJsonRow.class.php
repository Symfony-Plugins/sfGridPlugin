<?php

/*
 * This file is part of the symfony package.
 * Leon van der Ree <leon@fun4me.demon.nl> 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A formatter that renders a row as JSON
 *
 */
class sfGridFormatterJsonRow extends sfGridFormatterDynamicRow
{

  /**
   * Renders a row to an array
   *
   * @return string
   */
  public function render()
  {
    $source = $this->grid->getDataSource();
    $source->seek($this->index);

    $arrData = array();
    foreach ($this->grid->getWidgets() as $column => $widget)
    {
      $arrData[$column] = $widget->render($column, $source[$column]);
    }

    return $arrData;
  }

}