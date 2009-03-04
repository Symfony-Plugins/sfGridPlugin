<?php

/*
 * This file is part of the symfony package.
 * (c) Leon van der Ree <leon@fun4me.demon.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony
 * @subpackage helper
 * @author     Leon van der Ree <leon@fun4me.demon.nl>
 * @version    SVN: $Id$
 */

/**
 * Resolves the (last) ClassName from the objectPath
 *
 * @param string $objectPath an objectPath, with syntax baseClassName.ChildClassName.ChildClassName
 *                           the childClassNames should have a getter in the baseClass
 * @return string            the ClassName
 */
function resolveClassNameFromObjectPath($objectPath)
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
 * Resolves the Add-method for the first relation in an objectPath
 *
 * @param string $objectPath an objectPath, with syntax baseClassName.ChildClassName.ChildClassName
 *                           the childClassNames should have a getter in the baseClass
 * @return string            the add-Method of the child to register itself to its parent
 */
function resolveFirstAddMethodForObjectPath($objectPath)
{
  // get the latest classReference (the part after the last '.')
  $classReferences = explode('.', $objectPath);
  $className = $classReferences[0];
//  $classReference = $classReferences[count($classReferences)-1];

  $related = '';
  // if there are multiple references to the same table, remove the RelatedBy.... part
  $classParts = explode('RelatedBy', $classReferences[1]);
  if (count($classParts)>1)
  {
    $related = 'RelatedBy'.$classParts[1];
  }

  return 'add'.$className.$related;
}

/**
 * TODO: Enter description here...
 *
 * @param string $propertyPath, objectPath followed by a propertyName
 */
function checkPropertyPath($baseClass, $propertyPath)
{
  $objectPath = getObjectPathForProperyPath($baseClass, $propertyPath);
  checkObjectPath($objectPath);

  //get property from propertyPath
  $parts = explode('.', $propertyPath);
  $property = array_pop($parts);

  $lastObject = resolveClassNameFromObjectPath($objectPath);
  $getterMethod = 'get'.$property;

  if (!method_exists($lastObject, $getterMethod))
  {
    throw new LogicException(sprintf('Class "%s" has no method called "%s".', $lastObject, $getterMethod));
  }

}

function getObjectPathForProperyPath($baseClass, $propertyPath)
{
  //remove property from propertyPath
  $parts = explode('.', $propertyPath);
  $property = array_pop($parts);

  // add baseClass to parts, before constructing objectPath
  array_unshift($parts, $baseClass);
  $objectPath = implode('.', $parts);

  return $objectPath;
}

/**
 * Tests if the object path is valid, if not throws an exception
 *
 * @param string $objectPath an objectPath, with syntax baseClassName.ChildClassName.ChildClassName
 *                           the childClassNames should have a getter in the baseClass
 *
 */
function checkObjectPath($objectPath)
{
  $classReferences = explode('.', $objectPath, 2);

  // if path was not provided
  if (count ($classReferences) == 0)
  {
    throw new UnexpectedValueException('empty path was provided');
  }

  $className = $classReferences[0];
  // then it must be an existing class
  if (!class_exists($className))
  {
    throw new UnexpectedValueException(sprintf('Class "%s" does not exist', $className));
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
    $relatedClassName = resolveClassNameFromObjectPath($partialObjectPath);
    $addMethod = resolveFirstAddMethodForObjectPath($partialObjectPath);

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
    checkObjectPath($relatedClassPath);
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
function resolveAllClasses($objectPath, $classes = array(), $parent = '')
{
  $classReferences = explode('.', $objectPath, 2);
  $relationName = $parent.$classReferences[0];

  // add Class to array, if not already known
  if (!isset($classes[$relationName]))
  {
    $classes[$relationName] = array('className' =>  resolveClassNameFromObjectPath($classReferences[0]),
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

    $classes = resolveAllClasses($classReferences[1], $classes, $relationName.'_');
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
function resolveBaseClass($objectPath)
{
  $classReferences = explode('.', $objectPath, 2);

  return $classReferences[0];
}

function addTableAliasses(&$criteria = null, $objectPaths)
{
  // if the source is provided as object paths, create hydratable criteria
  if (!is_array($objectPaths))
  {
    throw new InvalidArgumentException('The source must be an instance of Criteria or a propel class name');
  }

  // generate an array of classes to be retrieved from DB
  $classes = array();
  $baseClass = resolveBaseClass($objectPaths[0]);
  $basePeer = constant($baseClass.'::PEER');

  foreach ($objectPaths as $objectPath)
  {
    // validation checks @todo: this can be skipped in production
    // test if there is only one base class
    if ($baseClass != ($currentBaseClass = resolveBaseClass($objectPath)))
    {
      throw new LogicException(sprintf('Not all base classes are the same.
                                        Resolved "%s", while expecting "%s"', $currentBaseClass, $baseClass));
    }
    // test if relations are valid
    checkObjectPath($objectPath);

    // get flat array of classes that need to get hydrated
    $classes =  resolveAllClasses($objectPath, $classes);
  }

  // construct full hydration-profile
  $criteria->setDbName(constant($basePeer.'::DATABASE_NAME'));

  // process all classes
  foreach ($classes as $alias => &$class)
  {
    // TODO: should I start with the base $class??? in that case do it before the foreach and skip base in foreach
    $peer = constant($class['className'].'::PEER');

    //add alias for tables
    $criteria->addAlias($alias, constant($peer.'::TABLE_NAME'));

    //addSelectColumns
    if ($withColumns)
    {
      call_user_func_array(array($peer, 'addSelectColumnsAliased'), array($criteria, $alias));
    }

    // get TableMap for base
    $baseTM = call_user_func_array(array($peer, 'getTableMap'), array());

    //join related
    foreach ($class['relatedTo'] as $relatedTo)
    {
      $relatedAlias = $alias.'_'.$relatedTo;
      $relatedPeer = constant($classes[$relatedAlias]['className'].'::PEER');
      // get TableMap for related
      $relatedTM = call_user_func_array(array($relatedPeer, 'getTableMap'), array());

      $relatedPKs = $relatedTM->getPrimaryKeyColumns();
      // only support for one Primary Key
      $relatedPKName = $relatedPKs[0]->getColumnName();

      // search for relation (BaseTable-ForeignKey / ForeignTable-PrimaryKey)
      $RelatedByArr = explode('RelatedBy', $relatedTo, 2);
      foreach ($baseTM->getColumns() as $relatedColumn)
      {
        $baseFKName = 'ID'; // @todo: hack to make sfGuardUserProfile to work, for now (since this one cannot be resolved)
        if (count($RelatedByArr)==1)
        {
          $colRelTableName = $relatedColumn->getRelatedTableName();
          if ((!empty($colRelTableName)) && ($baseTM->getDatabaseMap()->containsTable($colRelTableName)) && ($baseTM->getDatabaseMap()->getTable($colRelTableName)->getPhpName() == $RelatedByArr[0]))
          {
            $baseFKName = $relatedColumn->getName();
            break;
          }
          //TODO: reverse lookup (per table) to fix hack from above...
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

      $criteria->addJoin($joinColumnLeft, $joinColumnRight, $joinType);
    }
  }

  return $criteria;
}

/**
 * addJoins.
 *
 * The data source can be given as an (array of) objectPaths, or a custom
 * Criteria object. Custom criteria objects will not get hydrated, objects
 * names are!
 * the Criteria object will be cloned, since it will be modified internally.
 *
 * In the future the objectPaths can become optional, since these can be resolved
 * lazy from the property paths of a grid->setColumns(...)
 *
 * <code>
 * // fetches all user objects, and their related userProfiles from objectPath
 * $source = new sfDataSourcePropel('User', 'User.UserProfile');
 * // exactly the same:
 * $source = new sfDataSourcePropel(array('User.UserProfile'));
 * // since array is optional, and 'User' is resolved from 'User.UserProfile'
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
 * @param Criteria $criteria         The Criteria Object to add the selected-columns and joins to
 * @param array $objectPaths         The data source (a select Criteria, or an
 *                                   (array of) object Path(s)
 * @param bool $withColumns          add Select Columns to the criteria (usefull to disable for counts, where you only want joins)
 *
 * @return Criteria                  The criteria object, with the added selected-columns and joins
 *
 * @throws LogicException            Throws an exception if the source is a
 *                                   string, but not an existing Propel class name
 * @throws UnexpectedValueException  Throws an exception if the select source is
 *                                   a Criteria, but is missing a count Criteria
 * @throws InvalidArgumentException  Throws an exception if the source is
 *                                   neither a valid propel model class name
 *                                   nor a Criteria.
 */
function addJoins($criteria = null, $objectPaths, $withColumns = true)
{
  $criteria = clone $criteria;

  // if the source is provided as object paths, create hydratable criteria
  if (!is_array($objectPaths))
  {
    throw new InvalidArgumentException('The source must be an instance of Criteria or a propel class name');
  }

  // generate an array of classes to be retrieved from DB
  $classes = array();
  $baseClass = resolveBaseClass($objectPaths[0]);
  $basePeer = constant($baseClass.'::PEER');

  foreach ($objectPaths as $objectPath)
  {
    // validation checks @todo: this can be skipped in production
    // test if there is only one base class
    if ($baseClass != ($currentBaseClass = resolveBaseClass($objectPath)))
    {
      throw new LogicException(sprintf('Not all base classes are the same.
                                        Resolved "%s", while expecting "%s"', $currentBaseClass, $baseClass));
    }
    // test if relations are valid
    checkObjectPath($objectPath);

    // get flat array of classes that need to get hydrated
    $classes =  resolveAllClasses($objectPath, $classes);
  }

  // construct full hydration-profile
  $criteria->setDbName(constant($basePeer.'::DATABASE_NAME'));

  // process all classes
  foreach ($classes as $alias => &$class)
  {
    // TODO: should I start with the base $class??? in that case do it before the foreach and skip base in foreach
    $peer = constant($class['className'].'::PEER');

    //add alias for tables
    $criteria->addAlias($alias, constant($peer.'::TABLE_NAME'));

    //addSelectColumns
    if ($withColumns)
    {
      call_user_func_array(array($peer, 'addSelectColumnsAliased'), array($criteria, $alias));
//      call_user_func_array(array($peer, 'addCustomColumnsAliased'), array($criteria, $alias));
    }

    // get TableMap for base
    $baseTM = call_user_func_array(array($peer, 'getTableMap'), array());

    //join related
    foreach ($class['relatedTo'] as $relatedTo)
    {
      $relatedAlias = $alias.'_'.$relatedTo;
      $relatedPeer = constant($classes[$relatedAlias]['className'].'::PEER');
      // get TableMap for related
      $relatedTM = call_user_func_array(array($relatedPeer, 'getTableMap'), array());

      $relatedPKs = $relatedTM->getPrimaryKeyColumns();
      // only support for one Primary Key
      $relatedPKName = $relatedPKs[0]->getColumnName();

      // search for relation (BaseTable-ForeignKey / ForeignTable-PrimaryKey)
      $RelatedByArr = explode('RelatedBy', $relatedTo, 2);
      foreach ($baseTM->getColumns() as $relatedColumn)
      {
        $baseFKName = 'ID'; // @todo: hack to make sfGuardUserProfile to work, for now (since this one cannot be resolved)
        if (count($RelatedByArr)==1)
        {
          $colRelTableName = $relatedColumn->getRelatedTableName();
          if ((!empty($colRelTableName)) && ($baseTM->getDatabaseMap()->containsTable($colRelTableName)) && ($baseTM->getDatabaseMap()->getTable($colRelTableName)->getPhpName() == $RelatedByArr[0]))
          {
            $baseFKName = $relatedColumn->getName();
            break;
          }
          //TODO: reverse lookup (per table) to fix hack from above...
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

      $criteria->addJoin($joinColumnLeft, $joinColumnRight, $joinType);
    }
  }

  return $criteria;
}

/**
 * private method to load the Data,
 *
 * the functionality depends on the constructor call (have custom Criteria been provided, or is hydration of objects possible)
 */

/**
 * Loads the data from the database and places it in an array.
 *
 * @param unknown_type $criteria
 * @param unknown_type $objectPaths
 * @param array $extraColumns
 * @param unknown_type $connection
 */
function loadData($criteria = null, $objectPaths, $connection = null)
{
  // data holds all main results
  $data = array();

  $stmt = BasePeer::doSelect($criteria, $connection);
  $results = $stmt->fetchAll(PDO::FETCH_NUM);

  $baseClass = resolveBaseClass($objectPaths[0]);
  $basePeer = constant($baseClass.'::PEER');
  $classes = array();
  // pre-process classnames and there mutual relations
  foreach ($objectPaths as $objectPath)
  {
    $classes = resolveAllClasses($objectPath, $classes);
  }
  //remove the base class from the list, since this is hydrated by default
  array_shift($classes);

  // hydrate all (related)objects with the resultset
  foreach ($results as $row)
  {
    $startcol = 0;

    // hydration of the base object
    $key = call_user_func_array(array($basePeer, 'getPrimaryKeyHashFromRow'), array($row, $startcol));
    if (($instance = call_user_func_array(array($basePeer, 'getInstanceFromPool'), array($key))) === null)
    {
      $omClass = call_user_func(array($basePeer, 'getOMClass'));

      $cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
      $instance = new $cls();
      $instance->hydrate($row);
      call_user_func_array(array($basePeer, 'addInstanceToPool'), array($instance, $key));
    }
    // calculate startCol in row for first related class
    $startcol += constant($basePeer.'::NUM_COLUMNS') - constant($basePeer.'::NUM_LAZY_LOAD_COLUMNS');

    //hydrate related objects
    foreach ($classes as $path => $relatedClass)
    {
      $relatedClassName = $relatedClass['className'];
      $relatedPeer = constant($relatedClassName.'::PEER');

      $key = call_user_func_array(array($relatedPeer, 'getPrimaryKeyHashFromRow'), array($row, $startcol));
      if ($key !== null)
      {
        $relatedObj = call_user_func_array(array($relatedPeer, 'getInstanceFromPool'), array($key));
        if (!$relatedObj)
        {
          $omClass = call_user_func(array($relatedPeer, 'getOMClass'));

          $cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
          $relatedObj = new $cls();
          $relatedObj->hydrate($row, $startcol);
          call_user_func_array(array($relatedPeer, 'addInstanceToPool'), array($relatedObj, $key));
        }

        $paths = explode('_', $path);// @TODO: check if exploding if _ is always OK...
        $nrPaths = count($paths);
        $addMethod = resolveFirstAddMethodForObjectPath($paths[$nrPaths-2].'.'.$paths[$nrPaths-1]);

        $parent = $instance;
        // remove base object and getter from path
        array_shift($paths); array_pop($paths);
        foreach ($paths as $getMethod)
        {
          $parent = call_user_func(array($parent, 'get'.$getMethod));
        }
        call_user_func_array(array($relatedObj, $addMethod), array($parent));
      }

      // add column-count to startcol for next object
      $startcol += constant($relatedPeer.'::NUM_COLUMNS') - constant($relatedPeer.'::NUM_LAZY_LOAD_COLUMNS');
    }

//    $instance->hydrateCustomColumns($row, $startcol, $criteria);

    $data[] = $instance;
  }

  $stmt->closeCursor();

  return $data;
}

/**
 * @see sfDataSourceInterface::countAll()
 */
function countAll()
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

function translatePropertyPathToAliasedColumn($propertyPath)
{
  $parts = explode('.', $propertyPath);
  $property = array_pop($parts);
  $aliasedColumn = implode('_', $parts);

  return $aliasedColumn.'.'.$property;
}
