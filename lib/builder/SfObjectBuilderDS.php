<?php

require_once 'propel/engine/builder/om/php5/PHP5ObjectBuilder.php';

/*
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony
 * @subpackage propel
 * @author     Leon van der Ree <leon@fun4me.demon.nl>
 * @version    SVN: $Id: SfObjectBuilderDS.php
 */
class SfObjectBuilderDS extends SfObjectBuilder
{
  protected function addClassBody(&$script)
  {
    parent::addClassBody($script);

    $this->addCustomColumnCheckerMethod($script);
    $this->addCustomColumnAccessorMethod($script);
    $this->addCustomColumnMutatorMethod($script);
    $this->addCustomColumnHydationMethod($script);

    $this->addCustomColumnAccessorGetMethod($script);
  }


  protected function addAttributes(&$script)
  {
    parent::addAttributes($script);

      $script .= '
  /**
   * The holder for all custom columns of an query
   * @var array
   */
  protected $customColumns = array();
';
  }

  protected function addCustomColumnCheckerMethod(&$script)
  {
    $script .= '

  /**
   * Returns if the Custom Column has been set.
   *
   * @param string $key The name of the custom column
   *
   * @return bool       True is the custom column has been set
   */
  public function hasCustomColumn($key)
  {
    return isset($this->customColumns[$key]);
  }
';
  }

  protected function addCustomColumnAccessorMethod(&$script)
  {
    $script .= '

  /**
   * Returns the Custom Column of a hydrated result.
   *
   * @param string $key The name of the custom column
   *
   * @return mixed      The value of the custom column
   */
  public function getCustomColumnValue($key)
  {
    return $this->customColumns[$key];
  }
';
  }

  protected function addCustomColumnMutatorMethod(&$script)
  {
    $script .= '
  /**
   * Sets the culture.
   *
   * @param string $key  The name of the custom column
   * @param mixed $value The value from the custom column
   *
   * @return void
   */
  public function setCustomColumnValue($key, $value)
  {
    $this->customColumns[$key] = $value;
  }
';
  }
  
  protected function addCustomColumnAccessorGetMethod(&$script)
  {
    $originalHead = "public function __call(\$method, \$arguments)
  {";
    
    $extraBody = "
  public function __call(\$method, \$arguments)
  {
    //automatically define getters for new Attributes
    if (strpos(\$method, 'get') === 0)
    {
      \$attribute = substr(\$method,3);
      
      if (\$this->hasCustomColumn(\$attribute))
      {
        return \$this->getCustomColumnValue(\$attribute);
      }
    }
    
    ";
    
    // add extra check for Getters
    $script = str_replace($originalHead, $extraBody, $script);
  }
  

  protected function addCustomColumnHydationMethod(&$script)
  {
    $script .= "
  /**
   * Hydrates (populates) the custom columns with (lef over) values from the database resultset.
   *
   * An offset (0-based \"start column\") is specified so that objects can be hydrated
   * with a subset of the columns in the resultset rows.  This is needed, since the previous
   * rows are from the already hydrated objects. 
   *
   * @param      array \$row The row returned by PDOStatement->fetch(PDO::FETCH_NUM)
   * @param      int \$startcol 0-based offset column which indicates which restultset column to start with.
   * @param      Criteria \$criteria
   * @throws     PropelException  - Any caught Exception will be rewrapped as a PropelException.
   */
  public function hydrateCustomColumns(\$row, \$startcol, Criteria \$criteria)
  {
    \$attributeNames = array_merge(\$criteria->getSelectColumns(), array_keys(\$criteria->getAsColumns()));
    
    //replace dots with underscores
    \$attributeName = str_replace('.', '_', \$attributeNames[\$startcol]);
    
    // dynamically add attributes
    \$this->setCustomColumnValue(\$attributeName, \$row[\$startcol]);
  }  
";
  }
    
}
