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
}
