<?php

//require_once 'propel/engine/builder/om/php5/PHP5PeerBuilder.php';

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony
 * @subpackage propel
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: SfPeerBuilder.php 14378 2008-12-29 20:04:39Z Kris.Wallsmith $
 */
class SfPeerBuilderDS extends SfPeerBuilder
{
  protected function addSelectMethods(&$script)
  {
    $this->addAddSelectColumnsAliased($script);
  	
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

}
