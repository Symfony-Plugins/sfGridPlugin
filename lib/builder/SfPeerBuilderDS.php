<?php

//require_once 'propel/engine/builder/om/php5/PHP5PeerBuilder.php';

/*
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony
 * @subpackage propel
 * @author     Leon van der Ree <leon@fun4me.demon.nl>
 * @version    SVN: $Id: SfPeerBuilder.php
 */
class SfPeerBuilderDS extends SfPeerBuilder
{
  protected function addSelectMethods(&$script)
  {
    $this->addAddSelectColumnsAliased($script);
    
    $this->addGetCustomColumns($script);
    $this->addAddCustomSelectColumns($script);

    parent::addSelectMethods($script);
  }

  /**
   * Adds the addSelectColumnsAliased() method.
   * @param      string &$script The script will be modified in this method.
   */
  protected function addAddSelectColumnsAliased(&$script)
  {
    $script .= "
  /**
   * Add all the columns needed to create a new object.
   *
   * Note: any columns that were marked with lazyLoad=\"true\" in the
   * XML schema will not be added to the select list and only loaded
   * on demand.
   *
   * @param      criteria object containing the columns to add.
   * @param      string \$alias The alias for the current table.
   * @throws     PropelException Any exceptions caught during processing will be
   *     rethrown wrapped into a PropelException.
   */
  public static function addSelectColumnsAliased(Criteria \$criteria, \$alias)
  {
";
    foreach ($this->getTable()->getColumns() as $col) {
      if (!$col->isLazyLoad()) {
        $script .= "
    \$criteria->addSelectColumn(".$this->getPeerClassname()."::alias(\$alias, ".$this->getPeerClassname()."::".$this->getColumnName($col)."));
";
      } // if !col->isLazyLoad
    } // foreach
    $script .="
  }
";
  } // addAddSelectColumnsAliased()

  protected function addGetCustomColumns(&$script)
  {
    $script .= "
  /**
   * To be overruled in the Peer method, returning customColumnNames and the custom clause query.
   *
   * @return array  an associative array that contains columnName-clause pairs
   */
  public static function getCustomColumns()
  {
    return array();
  }
";
  } // addGetCustomColumns()
  
  /**
   * Adds the addCustomSelectColumns() method.
   * @param      string &$script The script will be modified in this method.
   */
  protected function addAddCustomSelectColumns(&$script)
  {
    $script .= "
  /**
   * Allow to add custom columns to an object.
   *
   * @param      criteria object containing the columns to add.
   * @throws     PropelException Any exceptions caught during processing will be
   *     rethrown wrapped into a PropelException.
   */
  public static function addCustomSelectColumns(Criteria \$criteria)
  {
    foreach (".$this->getPeerClassname()."::getCustomColumns() as \$name => \$clause )
    {
      \$criteria->addAsColumn(\$name, \$clause);
    }
  }
";
  } // addAddCustomSelectColumns()  
  
}
