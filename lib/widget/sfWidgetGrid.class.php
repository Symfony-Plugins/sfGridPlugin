<?php

/*
 * This file is part of the symfony package.
 * (c) Sergio Fabian Vier <sergio.vier@alyssa-it.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetGrid is the base class for all grid widgets.
 *
 * @package    lib
 * @subpackage widget
 * @author     Sergio Fabian Vier <sergio.vier@alyssa-it.com>
 * @version    SVN: $Id$
 */
class sfWidgetGrid extends sfWidget
{

  /**
   * the Grid reference
   * @var sfGrid
   */
  protected $grid = null;

  /**
   * Render a plain value
   *
   * Extend this class and override this method for pre-process this value.
   *
   * @see sfWidget#render()
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    return $value;
  }

  /**
   * Set the grid reference
   *
   * @param sfGrid $grid
   */
  public function setGrid(sfGrid $grid)
  {
    $this->grid = $grid;
  }

  /**
   * Returns the grid reference
   *
   * @return sfGrid
   */
  public function getGrid()
  {
    if ($this->grid !== null){
      return $this->grid;
    }
    // realy, throw a exception?
    throw new LogicException(sprintf("The widget '%s' class has not setted the 'grid' option.", getclass($this)));
  }

}