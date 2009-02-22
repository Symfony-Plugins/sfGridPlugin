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
	/*
	 * @var Criteria countCriteria
	 */
	protected $countCriteria = null;

	/**
	 * @var array    data
	 */
	protected $data     = null;

	/**
	 * Database Connection
	 *
	 * @var PropelPDO
	 */
	protected $connection = null;

	/**
	 * Resolves the (last) ClassName from the objectPath
	 *
	 * @param string $objectPath an objectPath, with syntax baseClassName.ChildClassName.ChildClassName
	 *                           the childClassNames should have a getter in the baseClass
	 * @return string            the ClassName
	 */
	static public function resolveClassNameFromObjectPath($objectPath)
	{
		// get the latest classReference (the part after the last '.')
		$classReferences = explode('.', $objectPath);
		$classReference = $classReferences[count($classReferences)-1];

		// if there are multiple references to the same table, remove the RelatedBy.... part
		$classParts = explode('RelatedBy', $classReference);
		$className = $classParts[0];

		return $className;
	}

	/**
	 * Resolves the Add-method for the last relation in an objectPath
	 *
	 * @param string $objectPath an objectPath, with syntax baseClassName.ChildClassName.ChildClassName
	 *                           the childClassNames should have a getter in the baseClass
	 * @return string            the add-Method of the child to register itself to its parent
	 */
	static public function resolveAddMethodFromObjectPath($objectPath)
	{
		// get the latest classReference (the part after the last '.')
		$classReferences = explode('.', $objectPath);
		$className = $classReferences[0];
		$classReference = $classReferences[count($classReferences)-1];

		$related = '';
		// if there are multiple references to the same table, remove the RelatedBy.... part
		$classParts = explode('RelatedBy', $classReference);
		if (count($classParts)>1)
		{
			$related = 'RelatedBy'.$classParts[1];
		}

		return 'add'.$className.$related;
	}

	/**
	 * Tests if the object path is valid, if not throws an exception
	 *
	 * @param string $objectPath an objectPath, with syntax baseClassName.ChildClassName.ChildClassName
	 *                           the childClassNames should have a getter in the baseClass
	 *
	 */
	static public function checkObjectPath($objectPath)
	{
		$classReferences = explode('.', $objectPath, 2);

		// if not path was provided
		if (count ($classReferences) == 0)
		{
			throw new UnexpectedValueException('empty path was provided');
		}

		$className = $classReferences[0];
		// then it must be an existing class
		if (!class_exists($className))
		{
			throw new LogicException(sprintf('Class "%s" does not exist', $className));
		}

		// class should be extension of Propel BaseObject
		$reflection = new ReflectionClass($className);
		// that class must be a child of Propels BaseObject
		if (!$reflection->isSubclassOf('BaseObject'))
		{
			throw new LogicException(sprintf('Class "%s" is no Propel based class', $className));
		}

		if (isset($classReferences[1]))
		{
			$relatedClassReferences = explode('.', $classReferences[1], 2);
			$relatedClassGetter = $relatedClassReferences[0];
			// test with reflection if getter exists for ClassName
			$getterMethod = 'get'.$relatedClassGetter;
			if (!$reflection->hasMethod($getterMethod))
			{
				throw new LogicException(sprintf('Class "%s" has no method called "%s". Tip: you can add your own get-method that returns the related object.', $className, $getterMethod));
			}

			// get directly related ClassName
			$partialObjectPath = $className . '.' .$relatedClassGetter;
			$relatedClassName = self::resolveClassNameFromObjectPath($partialObjectPath);
			$addMethod = self::resolveAddMethodFromObjectPath($partialObjectPath);

			// then it must be an existing class
			if (!class_exists($relatedClassName))
			{
				throw new LogicException(sprintf('Class "%s" does not exist.
	                                        Please note: don\'t use the getter for the foreign-key value, but the getter for the related class instance', $relatedClassName));
			}
			 
			// test here for add-er
			$relatedReflection = new ReflectionClass($relatedClassName);
			if (!$relatedReflection->hasMethod($addMethod))
			{
				throw new LogicException(sprintf('Class "%s" has no method called "%s. Tip: you can add your own add-method, required to perform the hydration.', $relatedClassName, $addMethod));
			}
			//recursively check
			$relatedClassPath = $relatedClassName;
			if (isset($relatedClassReferences[1]))
			{
				$relatedClassPath .= '.'.$relatedClassReferences[1];
			}
			self::checkObjectPath($relatedClassPath);
		}

		// done, sucessfully parsed the objectPath
	}

	/**
	 * returns an array of Classes refered to by an objectPath
	 *
	 * @param string $objectPath an objectPath, with syntax baseClassName.ChildClassName.ChildClassName
	 *                           the childClassNames should have a getter in the baseClass
	 * @param array $classes     an instance of the classes to be returned, that will be complemented
	 * @param string $parent     the parent of the current objectPath
	 * @return array
	 */
	static public function resolveAllClasses($objectPath, $classes = array(), $parent = '')
	{
		$classReferences = explode('.', $objectPath, 2);
		$relationName = $parent.$classReferences[0];

		// add Class to array, if not already known
		if (!isset($classes[$relationName]))
		{
			$classes[$relationName] = array('className' =>  self::resolveClassNameFromObjectPath($classReferences[0]),
                                      'relatedTo' => array()
			);
		}

		if (isset($classReferences[1]))
		{
			$relatedClassReferences = explode('.', $classReferences[1], 2);

			$relatedTo = $relatedClassReferences[0];
			if (!in_array($relatedTo, $classes[$relationName]['relatedTo']))
			{
				$classes[$relationName]['relatedTo'][] = $relatedTo;
			}

			$classes = self::resolveAllClasses($classReferences[1], $classes, $relationName.'_');
		}

		return $classes;
	}

	/**
	 * Enter description here...
	 *
	 * @param string $objectPath an objectPath, with syntax baseClassName.ChildClassName.ChildClassName
	 *                           the childClassNames should have a getter in the baseClass
	 * @return string            the base class for this path
	 */
	public static function resolveBaseClass($objectPath)
	{
		$classReferences = explode('.', $objectPath, 2);

		return $classReferences[0];
	}

	/**
	 * Constructor.
	 *
	 * The data source can be given as an (array of) objectPaths, or a custom
	 * Criteria object. Custom criteria objects will not get hydrated, objects
	 * names are!
	 * the Criteria object will be cloned, since it will be modified internally.
	 *
	 * <code>
	 * // fetches all user objects, and their related userProfiles from objectPath
	 * $source = new sfDataSourcePropel('User', 'User.UserProfile');
	 * // exactly the same:
	 * $source = new sfDataSourcePropel(array('User.UserProfile'));
	 * // this source->current() will return a hydrated object of the base object (User)
	 *
	 * // fetches user objects from Criteria
	 * $criteria = new Criteria();
	 * UserPeer::addSelectColumns($criteria);
	 * // you can add more related / custom columns here
	 *
	 * $countCriteria = new Criteria();
	 * $countCriteria->setPrimaryTableName(UserPeer::TABLE_NAME);
	 *
	 * $source = new sfDataSourcePropel($criteria, $countCriteria);
	 * // this source will contain non-hydrated resultsets,
	 * // hasColumn will only accept the tablename.COLUMNNAME syntax (from propel)
	 * </code>
	 *
	 * @param  mixed $source             The data source
	 * @param  Criteria $countCriteria   The count Criteria, required when providing
	 *                                   a Criteria object as source.
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
	public function __construct($source, $countCriteria = null)
	{
		// if provided object path(s), make it an array
		if (is_string($source))
		{
			$source = func_get_args();
		}

		// if the source is provided as object paths, create hydratable criteria
		if (is_array($source))
		{
			// generate an array of classes to be retrieved from DB
			$classes = array();
			$baseClass = self::resolveBaseClass($source[0]);
			foreach ($source as $objectPath)
			{
				// test if there is only one base class
				if ($baseClass != ($currentBaseClass = self::resolveBaseClass($objectPath)))
				{
					throw new LogicException(sprintf('Not all base classes are the same.
      		                                  Resolved "%s", while expecting "%s"', $currentBaseClass, $baseClass));
				}
				 
				// test if relations are valid
				self::checkObjectPath($objectPath);

				$classes =  self::resolveAllClasses($objectPath, $classes);
			}
			$basePeer = $baseClass.'Peer';
			
			// construct full hydration-profile
			$this->selectCriteria = new Criteria();
			
			$this->selectCriteria->setDbName(constant($basePeer.'::DATABASE_NAME'));

			// process all classes
			foreach ($classes as $alias => &$class)
			{
				// TODO: should I start with the base $class??? in that case do it before the foreach and skip base in foreach
				$peer = $class['className'].'Peer';
				 
				//addalias
				$this->selectCriteria->addAlias($alias, constant($peer.'::TABLE_NAME'));
				 
				//addSelectColumns
				call_user_func_array(array($peer, 'addSelectColumnsAliased'), array($this->selectCriteria, $alias));
				$class['retreived_columns'] =  constant($peer.'::NUM_COLUMNS') - constant($peer.'::NUM_LAZY_LOAD_COLUMNS') ;

				// get TableMaps
				$baseTM = call_user_func_array(array($peer, 'getTableMap'), array());

				//join related
				foreach ($class['relatedTo'] as $relatedTo)
				{
					$relatedAlias = $alias.'_'.$relatedTo;
					$relatedPeer = $classes[$relatedAlias]['className'].'Peer';
					$relatedTM = call_user_func_array(array($relatedPeer, 'getTableMap'), array());

					$relatedPKs = $relatedTM->getPrimaryKeyColumns();
					// only support for one Primary Key
					$relatedPKName = $relatedPKs[0]->getColumnName();

					$RelatedByArr = explode('RelatedBy', $relatedTo, 2);
					foreach ($baseTM->getColumns() as $relatedColumn)
					{
						$baseFKName = 'ID'; // @todo: hack to make sfGuardUserProfile to work, for now (since this one cannot be resolved)
						if (count($RelatedByArr)==1)
						{
							$colRelTableName = $relatedColumn->getRelatedTableName();
							if ((!empty($colRelTableName)) && ($baseTM->getDatabaseMap()->getTable($colRelTableName)->getPhpName() == $RelatedByArr[0]))
							{
								$baseFKName = $relatedColumn->getName();
								break;
							}
							//TODO: reverse lookup (per table) to fix hack...
						}
						else
						{
							if ($relatedColumn->getPhpName() == $RelatedByArr[1])
							{
								$baseFKName = $relatedColumn->getName();
								break;
							}
						}
					}
					 
					$joinColumnLeft  = call_user_func_array(array($peer, 'alias'), array($alias, constant($peer.'::'.$baseFKName)));
					$joinColumnRight = call_user_func_array(array($relatedPeer, 'alias'), array($relatedAlias, constant($relatedPeer.'::'.$relatedPKName)));
					$joinType = Criteria::LEFT_JOIN; //TODO: add lookup table that can define the joinType
					 
					$this->selectCriteria->addJoin($joinColumnLeft, $joinColumnRight, $joinType);
				}
			}
			
      // doCount
      $this->countCriteria = clone $this->selectCriteria;
      $this->countCriteria->clearSelectColumns();
      $this->countCriteria->clearOrderByColumns(); // ORDER BY won't ever affect the count
            
      $this->countCriteria->setPrimaryTableName(constant($basePeer.'::TABLE_NAME')); // or $baseClass
      call_user_func_array(array($basePeer, 'addSelectColumnsAliased'), array($this->countCriteria, $baseClass));
      

//			print_r($classes);
//			echo ($this->selectCriteria->toString() );
//			die ('<br>tot nu toe goed... genoeg.');

		}
		// ...the source can also be passed as custom criteria, these will not be hydrated!
		elseif ($source instanceof Criteria)
		{
			if (!$countCriteria instanceof Criteria)
			{
				throw new UnexpectedValueException(sprintf('The countCriteria argument is required when providing a Criteria object as source. The countCriteria is not a Criteria based class'));
			}

			$this->selectCriteria = clone $source;
			$this->countCriteria = clone $countCriteria;
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
		$stmt = BasePeer::doSelect($this->selectCriteria, $this->connection);

		$results = $stmt->fetchAll(PDO::FETCH_NUM);

		$this->data = array();

		$selectColumns = $this->selectCriteria->getSelectColumns();

		foreach ($results as $result)
		{
			$row = array();
			foreach ($result as $key => $field)
			{
				// translate columnnames
				$row[$selectColumns[$key]] = $field;
			}
			$this->data[] = $row;
		}
	}

	/**
	 * sets the connection to the database
	 *
	 * @param PropelPDO $connection
	 */
	public function setConnection($connection)
	{
		$this->connection = $connection;
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
		$criteria = clone $this->countCriteria;

		$criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count
		$criteria->setLimit(-1);          // LIMIT affects the count negative
		$criteria->setOffset(0);          // OFFSET affects the count negative

		if (!$criteria->hasSelectClause()) {
			throw new Exception('Please provide some select Criteria in the countCriteria (this can be a subset)');
		}

		// BasePeer returns a PDOStatement
		$stmt = BasePeer::doCount($criteria, $this->connection);

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
		return in_array($column, $this->selectCriteria->getSelectColumns());
	}

	/**
	 * Sets the offset and reloads the data if necessary.
	 *
	 * @see sfDataSource::setOffset()
	 */
	public function setOffset($offset)
	{
		parent::setOffset($offset);

		$this->selectCriteria->setOffset($offset);
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

		$this->selectCriteria->setLimit($limit);
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
		$this->selectCriteria->clearOrderByColumns();

		switch ($order)
		{
			case sfDataSourceInterface::ASC:
				$this->selectCriteria->addAscendingOrderByColumn($column);
				break;
			case sfDataSourceInterface::DESC:
				$this->selectCriteria->addDescendingOrderByColumn($column);
				break;
			default:
				throw new Exception('sfDataSourcePropel::doSort() only accepts "'.sfDataSourceInterface::ASC.'" or "'.sfDataSourceInterface::DESC.'" as argument');
		}
		$this->refresh();
	}

}