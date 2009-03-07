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

  /**
   * Adds the methods for retrieving, initializing, adding objects that are related to this one by foreign keys.
   * @param      string &$script The script will be modified in this method.
   */
  protected function addRefFKMethods(&$script)
  {
    foreach ($this->getTable()->getReferrers() as $refFK) {
      if ($refFK->isLocalPrimaryKey()) {
        $this->addPKRefFKGet($script, $refFK);
        $this->addPKRefFKSet($script, $refFK);
      } else {
        $this->addRefFKClear($script, $refFK);
        $this->addRefFKInit($script, $refFK);

        // added
        $this->addRefFKTouch($script, $refFK);

        $this->addRefFKGet($script, $refFK);
        $this->addRefFKCount($script, $refFK);
        $this->addRefFKAdd($script, $refFK);
        $this->addRefFKGetJoinMethods($script, $refFK);
      }
    }
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

    for (\$i=\$startcol; \$i<count(\$attributeNames); \$i++)
    {
      //replace dots with underscores
      \$attributeName = str_replace('.', '_', \$attributeNames[\$i]);

      // dynamically add attributes
      \$this->setCustomColumnValue(\$attributeName, \$row[\$i]);
    }
  }
";
  }

  /**
   * Adds the method that returns the referrer fkey collection.
   * @param      string &$script The script will be modified in this method.
   */
  protected function addRefFKGet(&$script, ForeignKey $refFK)
  {
    $table = $this->getTable();
    $tblFK = $refFK->getTable();

    $peerClassname = $this->getStubPeerBuilder()->getClassname();
    $fkPeerBuilder = $this->getNewPeerBuilder($refFK->getTable());
    $relCol = $this->getRefFKPhpNameAffix($refFK, $plural = true);

    $collName = $this->getRefFKCollVarName($refFK);
    $lastCriteriaName = $this->getRefFKLastCriteriaVarName($refFK);

    $className = $fkPeerBuilder->getObjectClassname();

    $script .= "
  /**
   * Gets an array of $className objects which contain a foreign key that references this object.
   *
   * If this collection has already been initialized, it returns the collection.
   * Otherwise if this ".$this->getObjectClassname()." has previously been saved, it will retrieve
   * related $relCol from storage. If this ".$this->getObjectClassname()." is new, it will return
   * an empty collection or the current collection, the criteria is ignored on a new object.
   *
   * @param      PropelPDO \$con
   * @param      Criteria \$criteria
   * @return     array {$className}[]
   * @throws     PropelException
   */
  public function get$relCol(\$criteria = null, PropelPDO \$con = null)
  {";

    $script .= "
    if (\$criteria === null) {
      \$criteria = new Criteria($peerClassname::DATABASE_NAME);
    }
    elseif (\$criteria instanceof Criteria)
    {
      \$criteria = clone \$criteria;
    }

    if (\$this->$collName === null) {
      if (\$this->isNew()) {
         \$this->$collName = array();
      } else {
";
    foreach ($refFK->getLocalColumns() as $colFKName) {
      // $colFKName is local to the referring table (i.e. foreign to this table)
      $lfmap = $refFK->getLocalForeignMapping();
      $localColumn = $this->getTable()->getColumn($lfmap[$colFKName]);
      $colFK = $refFK->getTable()->getColumn($colFKName);

      $clo = strtolower($localColumn->getName());

      $script .= "
        \$criteria->add(".$fkPeerBuilder->getColumnConstant($colFK).", \$this->$clo);
";
    } // end foreach ($fk->getForeignColumns()

    $script .= "
        ".$fkPeerBuilder->getPeerClassname()."::addSelectColumns(\$criteria);
        \$this->$collName = ".$fkPeerBuilder->getPeerClassname()."::doSelect(\$criteria, \$con);
      }
    } else {
      // criteria has no effect for a new object
      if (!\$this->isNew() && !is_array(\$this->$collName)) {
        // the following code is to determine if a new query is
        // called for.  If the criteria is the same as the last
        // one, just return the collection.
";
    foreach ($refFK->getLocalColumns() as $colFKName) {
      // $colFKName is local to the referring table (i.e. foreign to this table)
      $lfmap = $refFK->getLocalForeignMapping();
      $localColumn = $this->getTable()->getColumn($lfmap[$colFKName]);
      $colFK = $refFK->getTable()->getColumn($colFKName);
      $clo = strtolower($localColumn->getName());
      $script .= "

        \$criteria->add(".$fkPeerBuilder->getColumnConstant($colFK).", \$this->$clo);
";
    } // foreach ($fk->getForeignColumns()
    $script .= "
        ".$fkPeerBuilder->getPeerClassname()."::addSelectColumns(\$criteria);
        if (!isset(\$this->$lastCriteriaName) || !\$this->".$lastCriteriaName."->equals(\$criteria)) {
          \$this->$collName = ".$fkPeerBuilder->getPeerClassname()."::doSelect(\$criteria, \$con);
        }
      }
    }
    \$this->$lastCriteriaName = \$criteria;
    return \$this->$collName;
  }
";
  } // addRefererGet()


  /**
   * Adds the method that touches the referrer fkey collection. (so we don't need to rerun queries when it remained null during init)
   * @param      string &$script The script will be modified in this method.
   */
  protected function addRefFKTouch(&$script, ForeignKey $refFK) {

    $relCol = $this->getRefFKPhpNameAffix($refFK, $plural = true);
    $collName = $this->getRefFKCollVarName($refFK);

    $script .= "
  /**
   * Touches the $collName collection (array). (make it array() when null or keep it the way it is)
   *
   * This just sets the $collName collection to an empty array if it was null;

   * @return     void
   */
  public function touch$relCol()
  {
    if (!isset(\$this->$collName) ||  (\$this->$collName == null))
    {
      \$this->$collName = array();
    }
  }
";
  } // addRefererTouch()

}
