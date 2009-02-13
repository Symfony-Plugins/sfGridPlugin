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
   * @var string
   */
  protected $action; 
  /**
   * @var string
   */
  protected $mapping;
  
  /**
   * The constructor for the sfWidgetLink class, allowing to render links in a column
   *
   * @param sfGrid $grid        an instance of the sfGrid, containing a datasource
   * @param string $action      the name of the action the link is directing to
   * @param string $key_column  the column name from the datasource that should be used as key
   * @param array $mapping      a mapping to translate the column-name to a parameter in the url
   */
  public function __construct($grid, $action, $key_column = null, $mapping = array())
  {
    $this->grid       = $grid;
    $this->action     = $action; 
    $this->key_column = $key_column;
    $this->mapping    = $mapping;
  }
  
  /**
   * Returns the internal uri for the current widget 
   *
   * @return string
   */
  public function getUri()
  {
    $uri = sfContext::getInstance()->getModuleName().'/'.$this->action.'?';
    
    if ($this->key_column)
    {
      $source = $this->grid->getDataSource();
      $key = $this->key_column;
      
      if (isset($this->mapping[$key]))
      {
        $key = $this->mapping[$key];
      }
      
      $uri .= $key.'='.$source[$this->key_column];
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
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url', 'Tag'));
    
    return link_to($value, $this->getUri());
  }
}