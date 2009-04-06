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

    $this->addGetRelations($script);

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


  protected function addGetRelations(&$script)
  {
    $table = $this->getTable();
    $thisTableObjectBuilder = $this->getNewObjectBuilder($table);

    $relations = array();
    //find all foreignKeys from this table
    foreach ($table->getForeignKeys() as $fk)
    {
      $relationName = $thisTableObjectBuilder->getFKPhpNameAffix($fk, $plural = false);
      $joinTable = $table->getDatabase()->getTable($fk->getForeignTableName());
      $joinedTableObjectBuilder = $this->getNewObjectBuilder($joinTable);
      $joinedTablePeerBuilder = $this->getNewPeerBuilder($joinTable);
      $joinClassName = $joinedTableObjectBuilder->getObjectClassname();

      $lfMap = $fk->getLocalForeignMapping();
      $leftKeys = array();
      $rightKeys = array();
      
      foreach ($fk->getLocalColumns() as $columnName ) 
      {
        array_push($leftKeys,  $this->getColumnConstant($table->getColumn($columnName)) );
        array_push($rightKeys, $joinedTablePeerBuilder->getColumnConstant($joinTable->getColumn( $lfMap[$columnName])) );
      }

      $relations[$relationName] = array(
        'relatedClass' => $joinClassName,
        'oneToMany' => false,
        'associateMethod' => 'add'.$joinedTableObjectBuilder->getRefFKPhpNameAffix($fk, $plural = false),
        'leftKeys'  => $leftKeys,
        'rightKeys' => $rightKeys,
        'joinType'  => constant($this->getJoinBehavior()),
      );
    }

    //find all foreignKeys to this table, from other tables
    foreach ($this->getTable()->getReferrers() as $refFK) {
      if (!$refFK->isLocalPrimaryKey()) {
        $joinTable = $refFK->getTable();

        $joinedTableObjectBuilder = $this->getNewObjectBuilder($joinTable);

        $joinedTableObjectBuilder = $this->getNewObjectBuilder($joinTable);
        $joinedTablePeerBuilder = $this->getNewPeerBuilder($joinTable);
        $joinClassName = $joinedTableObjectBuilder->getObjectClassname();

        $relationName = $joinedTableObjectBuilder->getRefFKPhpNameAffix($refFK, $plural = true);

        $lfMap = $refFK->getLocalForeignMapping();
        $leftKeys = array();
        $rightKeys = array();
        foreach ($refFK->getLocalColumns() as $foreignColumnName) {
          array_push($leftKeys,  $this->getColumnConstant($table->getColumn( $lfMap[$foreignColumnName])) );
          array_push($rightKeys, $joinedTablePeerBuilder->getColumnConstant($joinTable->getColumn($foreignColumnName)) );
        }

        $relations[$relationName] = array(
          'relatedClass' => $joinClassName,
          'oneToMany' => true,
          'associateMethod' => 'set'.$thisTableObjectBuilder->getFKPhpNameAffix($refFK, $plural = false),
          'leftKeys'  => $leftKeys,
          'rightKeys' => $rightKeys,
          'joinType'  => constant($this->getJoinBehavior()),
        );
      }
    }

    $relations = var_export($relations, true);

    // remove ' around constants
    $relations = preg_replace('/\'(\w*::\w*)\'/',
                              '\1',  
                              $relations
                             );
    
    $script .= <<<EOF


  static public function getRelations()
  {
    return $relations;
  }
EOF;
  }

}
