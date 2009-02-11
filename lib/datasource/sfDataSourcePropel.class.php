<?php

/*
 * This file is part of the symfony package.
 * (c) Leon van der Ree <leon@fun4me.demon.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This class implements the interface sfDataSourceInterface for accessing
 * data stored in Propel tables.
 * 
 * You can either pass a model name or an Criteria object to the constructor.
 * 
 * <code>
 * // fetches all user objects
 * $source = new sfDataSourcePropel('User');
 * 
 * // fetches user objects from Criteria
 * $criteria = new Criteria();
 * UserPeer::addSelectColumns($criteria);
 * 
 * $source = new sfDataSourcePropel($criteria);
 *  
 * </code>
 * 
 * This class will work the same way no matter how you instantiate it. 
 * 
 * You can iterate the data source like any other data source. It will always 
 * provide an array of columns.
 * 
 * <code>
 * // unified data source iteration
 * $source = new sfDataSourcePropel('User');
 * for ($source->rewind(); $source->valid(); $source->next())
 * {
 *   echo $source['username'];
 * }
 * 
 * // iteration with foreach specific to this driver
 * $source = new sfDataSourcePropel('User');
 * foreach ($source as $user)
 * {
 *   echo $source['username']; 
 * }
 * </code>
 * 
 * @package    symfony
 * @subpackage grid
 * @author     Leon van der Ree <leon@fun4me.demon.nl>
 * @version    SVN: $Id$
 */
class sfDataSourcePropel extends sfDataSource
{
  /**
   * @var Criteria criteria
   */
  protected $criteria = null;
  /**
   * @var array    data
   */
  protected $data     = null;
    
  /**
   * Constructor.
   * 
   * The data source can be given as a table-name, or a Criteria object.
   * If you pass a Criteria object, it will be cloned because it needs to be 
   * modified internally.
   * 
   * <code>
   * // fetches all user objects
   * $source = new sfDataSourcePropel('User');
   * 
   * // fetches user objects from Criteria
   * $criteria = new Criteria();
   * UserPeer::addSelectColumns($criteria);
   * 
   * $source = new sfDataSourcePropel($criteria);
   * </code>
   * 
   * @param  mixed $source             The data source 
   * @throws UnexpectedValueException  Throws an exception if the source is a
   *                                   string, but not an existing class name
   * @throws UnexpectedValueException  Throws an exception if the source is a
   *                                   valid class name that does not inherit
   *                                   Doctrine_Record
   * @throws InvalidArgumentException  Throws an exception if the source is
   *                                   neither a valid model class name nor an
   *                                   instance of Doctrine_Query or 
   *                                   Doctrine_Collection.
   */
  public function __construct($source)
  {
    // the source can be passed as model class name...
    if (is_string($source))
    {
      // then it must be an existing class
      if (!class_exists($source))
      {
        throw new UnexpectedValueException(sprintf('Class "%s" does not exist', $source));
      }
      $reflection = new ReflectionClass($source);
      // that class must be a child of Propels BaseObject
      if (!$reflection->isSubclassOf('BaseObject'))
      {
        throw new UnexpectedValueException(sprintf('Class "%s" is no Propel based class', $source));
      }
      
      $tmp = new $source();
      $peer = $tmp->getPeer();
      
      $this->criteria = new Criteria(); 
      $peer->addSelectColumns($this->criteria);
    }
    // ...the source can also be passed as Criteria ...
    elseif ($source instanceof Criteria)
    {
      $this->criteria = clone $source;
    }
    else
    {
      throw new InvalidArgumentException('The source must be an instance of Criteria or a propel class name');
    }
  }
  
  /**
   * Returns whether the data has already been loaded from the database.
   * 
   * @return boolean Whether the data has already been loaded
   */
  private function isDataLoaded()
  {
    return $this->data !== null;
  }
  
  /**
   * Loads the data from the database and places it in an array.
   */
  private function loadData()
  {
    $stmt = BasePeer::doSelect($this->criteria, $con = null);
    
    $this->data = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Returns the value of the given field of the current record while iterating.
   * 
   * @param  string $field The name of the field
   * @return mixed         The value of the given field in the current record 
   */
  public function offsetGet($field)
  {
    $current = $this->current();
    return $current[$field];
  }
  
  /**
   * Returns the current record while iterating. If the internal row pointer does
   * not point at a valid row, an exception is thrown.
   * 
   * @return array                 The current row
   * @throws OutOfBoundsException  Throws an exception if the internal row
   *                               pointer does not point at a valid row.
   */
  public function current()
  {
    if (!$this->isDataLoaded())
    {
      $this->loadData();
    }
    
    // if this object has been initialized with a Doctrine_Collection, we need
    // to add the offset while retrieving objects
    $offset = $this->getOffset();
    
    if (!$this->valid())
    {
      throw new OutOfBoundsException(sprintf('The result with index %s does not exist', $this->key()));
    }
    
    return $this->data[$this->key()];
  }
  
  /**
   * Returns the number of records in the data source. If a limit is set with
   * setLimit(), the maximum return value is that limit. You can use the method
   * countAll() to count the total number of rows regardless of the limit.
   * 
   * <code>
   * $source = new sfDataSourcePropel('User');
   * echo $source->count();    // returns "100"
   * $source->setLimit(20);
   * echo $source->count();    // returns "20"
   * </code>
   * 
   * @return integer The number of rows
   */
  public function count()
  {
    if (!$this->isDataLoaded())
    {
      $this->loadData();
    }
    
    return count($this->data);
  }
  
  /**
   * @see sfDataSourceInterface::countAll()
   */
  public function countAll()
  {
    $criteria = clone $this->criteria;
    
    $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count
    $criteria->setLimit(-1);          // LIMIT affects the count negative
    $criteria->setOffset(0);          // OFFSET affects the count negative
    // BasePeer returns a PDOStatement
    $stmt = BasePeer::doCount($criteria, $con = null);

    if ($row = $stmt->fetch(PDO::FETCH_NUM)) 
    {
      $count = (int) $row[0];
    } 
    else 
    {
      $count = 0; // no rows returned; we infer that means 0 matches.
    }
    $stmt->closeCursor();    
    
    return $count;
  }
  
  /**
   * @see sfDataSourceInterface::hasColumn()
   */
  public function hasColumn($column)
  {
    // TODO: this has to be improved, the column-name now has to be preceded with the table-name 
    return array_search($column, $this->criteria->getSelectColumns());
  }
  
  /**
   * Sets the offset and reloads the data if necessary.
   * 
   * @see sfDataSource::setOffset()
   */
  public function setOffset($offset)
  {
    parent::setOffset($offset);
    
    $this->criteria->setOffset($offset);
    $this->refresh();
  }
  
  /**
   * Sets the limit and reloads the data if necessary.
   * 
   * @see sfDataSource::setLimit()
   */
  public function setLimit($limit)
  {
    parent::setLimit($limit);
    
    $this->criteria->setLimit($limit);
    $this->refresh();
  }
  
  /**
   * Reloads the data from the database, if the data had already been loaded.
   * Calling this method is essential when updating the internal query.
   */
  public function refresh()
  {
    if ($this->isDataLoaded())
    {
      $this->loadData();
    }
  }
  
  /**
   * @see sfDataSource::doSort()
   */
  protected function doSort($column, $order)
  {
    $this->criteria->clearOrderByColumns();
    
    switch ($order)
    {
      case sfDataSourceInterface::ASC:
        $this->criteria->addAscendingOrderByColumn($column);
        break;
      case sfDataSourceInterface::DESC:
        $this->criteria->addDescendingOrderByColumn($column);
        break;
      default:
        throw new Exception('sfDataSourcePropel::doSort() only accepts "'.sfDataSourceInterface::ASC.'" or "'.sfDataSourceInterface::DESC.'" as argument');
    }    
    $this->refresh();
  }
  
}