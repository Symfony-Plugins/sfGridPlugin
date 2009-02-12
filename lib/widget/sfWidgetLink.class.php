<?php

/*
 * This file is part of the symfony package.
 * (c) Leon van der Ree <leon@fun4me.demon.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfWidgetLink extends sfWidget
{
  /**
   * @var sfGrid
   */
  protected $grid;
  /**
   * @var string
   */
  protected $key_column; 
  
  /**
   * Enter description here...
   *
   * @param sfGrid $grid
   * @param string $key_column
   */
  public function __construct($grid, $key_column = null)
  {
    $this->grid= $grid;
    $this->key_column = $key_column;
  }
  
  public function getUri()
  {
    $uri = $this->grid->getUri();
    
    if ($this->key_column)
    {
      $source = $this->grid->getDataSource();
      $uri = sfGridFormatterHtml::makeUri($uri, array($this->key_column => $source[$this->key_column]));
    }
    
    return $uri;
  }
  
  /**
   * @param  string $name        The element name
   * @param  string $value       The value displayed in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    return $this->renderContentTag('a', $value, array_merge(array('href' => $this->getUri()), $attributes));
  }
}