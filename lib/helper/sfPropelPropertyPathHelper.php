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
 * Tests if the object path is valid, if not throws an exception
 *
 * @param string $objectPath an objectPath, with syntax baseClassName.RelationName.RelationName...
 *                           the RelationNames should be defined in the peer::getRelations method
 *
 */
function checkObjectPath($objectPath)
{
  $classRelations = explode('.', $objectPath, 2);

  // if path was not provided
  if (count ($classRelations) == 0)
  {
    throw new InvalidArgumentException('empty path was provided');
  }

  $baseClass = $classRelations[0];
  // then it must be an existing class
  if (!class_exists($baseClass))
  {
    throw new InvalidArgumentException(sprintf('Class "%s" does not exist', $baseClass));
  }

  // class should be extension of Propel BaseObject
  $baseReflection = new ReflectionClass($baseClass);

  // that class must be a child of Propels BaseObject
  if (!$baseReflection->isSubclassOf('BaseObject'))
  {
    throw new LogicException(sprintf('Class "%s" is no Propel based class', $baseClass));
  }

  // check if there are related classes defined
  //  - else done checking object path
  //  - if so check relation and translate relationName to ClassName before recursion
  //    valid cases are:
  //     - directly related:                                                    RelatedTableName
  //     - related by multiple pk/fk pairs:                                     RelatedTableName (pk/fk-pairs are automatically resolved)
  //     - directly related, but multiple relations from base to parent exist:  RelatedTableNameRelatedByForeignKeyName
  //     - reversely related (one-to-many):                                     RelatedTableNames (with the s)
  //   - NOT tested are self referencing relations (the issue with this is there depth is unknown)
  //   - NOT implemented is joining i18n related tables automatically.
  if (isset($classRelations[1]))
  {
    $relatedClassRelations = explode('.', $classRelations[1], 2);
    $relationName = array_shift($relatedClassRelations);
    $relationPath = $baseClass.'.'.$relationName;

    $relatedClass = resolveClassNameFromObjectPath($relationPath);
    
    $relation     = getRelationForRelationPath($relationPath);
    
    // (replace/)insert real ClassName at beginning of objectPath 
    array_unshift($relatedClassRelations, $relation['relatedClass']);
    $newObjectPath = implode('.', $relatedClassRelations);

    //recursively check
    checkObjectPath($newObjectPath);
  }

  // done, sucessfully parsed the objectPath
}


/**
 * Checks the resolved objectPath and
 * if the getter for the property from the final object exist
 *
 * @param string $propertyPath, objectPath followed by a propertyName
 */
function checkPropertyPath($baseClass, $propertyPath)
{
  $objectPath = getObjectPathFromProperyPath($baseClass, $propertyPath);
  checkObjectPath($objectPath);

  //get property from propertyPath
  $parts = explode('.', $propertyPath);
  $property = array_pop($parts);

  $lastClass = resolveClassNameFromObjectPath($objectPath);
  $getterMethod = 'get'.$property;

  if (!method_exists($lastClass, $getterMethod))
  {
    // test if it possibly is a custom column
    $lastClassPeer = getPeerNameForClass($lastClass);

    $custom = array_key_exists($property, call_user_func(array($lastClassPeer, 'getCustomColumns')));
    if (!$custom)
    {
      throw new LogicException(sprintf('Class "%s" has no method called "%s".', $lastClass, $getterMethod));
    }
  }

}


/**
 * retreives the relation information to a table
 *
 * @param string $relationPath  partial objectPath consisting of two objects
 *
 * @throws InvalidArgumentException  throws an InvalidArgumentException if something else than two parts have been provided in the path
 * @throws Exception                throws an Exception if relation cannot be found in the base peer class 
 * @return array      array with relation information
 *
 */
function getRelationForRelationPath($relationPath)
{
  $parts = explode('.', $relationPath);
  if (count($parts) != 2)
  {
    throw new InvalidArgumentException($relationPath.' should only consist out of two parts!');
  }

  list($baseClass, $relationName) = $parts;
  $basePeer = getPeerNameForClass($baseClass);
  
  $relations = call_user_func(array($basePeer, 'getRelations'));

  if (isset($relations[$relationName]))
  {
    $relation = $relations[$relationName];
  }
  else 
  {
    throw new Exception('No relation "'.$relationName.'" has been defined in the (base)"'.$basePeer.'"-method "getRelations".');
  }

  return $relation;
}

/**
 * Simple resolver for Peer class
 * The Peer name is already stored as a constant in the class 
 *
 * @param string $class the class name
 * @return string       the peer class name
 */
function getPeerNameForClass($class)
{
  if (!class_exists($class))
  {
    throw new Exception('Unable to retreive Peer! Baseclass "'.$class.'" does not exist!');
  }
  
  $peerName = constant($class.'::PEER');
  
  return $peerName;
}



/**
 * Resolves the (last) ClassName from the objectPath
 *
 * @param string $objectPath an objectPath, with syntax baseClassName.RelationName.RelationName...
 *                           the RelationNames should be defined in the peer::getRelations method
 * @return string            the ClassName
 */
function resolveClassNameFromObjectPath($objectPath)
{
  // get the latest classReference (the part after the last '.')
  $parts = explode('.', $objectPath, 2);

  // if no parts, we have an error
  if (count($parts) == 0)
  {
    throw new InvalidArgumentException('empty objectPath provided'); 
  }  
  
  // if only one part, we have found the class Name
  if (count($parts) == 1)
  {
    return $parts[0];
  }
  
  list($baseClass, $relationPath) = $parts;
  $basePeer = getPeerNameForClass($baseClass);
  
  $relationNames = explode('.', $relationPath);
  $relationName = array_shift($relationNames);
  
  $relations = call_user_func(array($basePeer, 'getRelations'));

  if (isset($relations[$relationName]))
  {
    $relation = $relations[$relationName];
  }
  else 
  {
    throw new Exception('No relation "'.$relationName.'" has been defined in the (base)"'.$basePeer.'"-method "getRelations".');
  }
  
  array_unshift($relationNames, $relation['relatedClass']);
  $newObjectPath = implode('.', $relationNames);
  
  // recursively call till only one left;
  return resolveClassNameFromObjectPath($newObjectPath);
}

/**
 * Simple helper that converts a propertyPath to a ObjectPath, by
 * simply adding the baseClass in front, and removing the propertyname on the end 
 *
 * @param string $baseClass     the BaseClass
 * @param string $propertyPath  the complete propertyPath
 * @return string               the ObjectPath
 */
function getObjectPathFromProperyPath($baseClass, $propertyPath)
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
 * translates a propertyPath to an aliased database selectColumn
 *
 * @param string $baseClass     The base class for the hydration
 * @param string $propertyPath  The propertyPath of the field
 * @return string               An aliased columnName for the database query
 */
function translatePropertyPathToAliasedColumn($baseClass, $propertyPath)
{
  //remove property from the path
  $parts = explode('.', $propertyPath);
  $property = array_pop($parts);

  // add the baseClass in front of the objectPath
  // add baseClass to parts, before constructing objectPath
  array_unshift($parts, $baseClass);
  // insert _ between all objects in the objectPath
  $aliasedColumn = implode('_', $parts);

  return $aliasedColumn.'.'.$property;
}

/**
 * returns an array of Classes refered to by the objectPath
 *
 * @param string $objectPath an objectPath, with syntax baseClassName.RelationName.RelationName...
 *                           the RelationNames should be defined in the peer::getRelations method
 * @param array $classes     an instance of the classes to be returned, that will be complemented
 * @param string $parent     the parent of the current objectPath
 * @return array
 */
function flattenAllClasses($objectPath, $classes = array(), $parent = '')
{
  $classRelations = explode('.', $objectPath, 2);
  $relationName = $parent.$classRelations[0];

  // add alliased Class to array, if not already known
  if (!isset($classes[$relationName]))
  {
    $classes[$relationName] = array('className' =>  resolveClassNameFromObjectPath($classRelations[0]),
                                    'relatedTo' => array()
    );
  }

  if (isset($classRelations[1]))
  {
    $relatedClassRelations = explode('.', $classRelations[1], 2);

    $relatedTo = $relatedClassRelations[0];
    if (!in_array($relatedTo, $classes[$relationName]['relatedTo']))
    {
      $classes[$relationName]['relatedTo'][] = $relatedTo;
    }

    $classes = flattenAllClasses($classRelations[1], $classes, $relationName.'_');
  }

  return $classes;
}

/**
 * Simply returns the base class in this path
 *
 * @param string $objectPath an objectPath, with syntax baseClassName.RelationName.RelationName...
 *                           the RelationNames should be defined in the peer::getRelations method
 * @return string            the base class for this path
 */
function resolveBaseClass($objectPath)
{
  $classRelations = explode('.', $objectPath, 2);

  return $classRelations[0];
}

/**
 * addJoins.
 * TODO: add support for inner/strict/right joins as well!
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
 * @throws InvalidArgumentException  Throws an exception if the select source is
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
  $basePeer = getPeerNameForClass($baseClass);

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
    $classes =  flattenAllClasses($objectPath, $classes);
    
    var_dump($classes);
    die();
  }

  // construct full hydration-profile
  $criteria->setDbName(constant($basePeer.'::DATABASE_NAME'));

  // process all classes
  foreach ($classes as $alias => &$class)
  {
    // TODO: should I start with the base $class??? in that case do it before the foreach and skip base in foreach
    $peer = getPeerNameForClass($class['className']);

    //add alias for tables
    $criteria->addAlias($alias, constant($peer.'::TABLE_NAME'));

    //addSelectColumns
    if ($withColumns)
    {
      call_user_func_array(array($peer, 'addSelectColumnsAliased'), array($criteria, $alias));
    }
    // this always has to be done, since you might want to filter on these columns
    call_user_func_array(array($peer, 'addCustomSelectColumns'), array($criteria, $alias));

    // get TableMap for base
    $baseTM = call_user_func(array($peer, 'getTableMap'));

    //join related
    foreach ($class['relatedTo'] as $relatedTo)
    {
      $relatedAlias = $alias.'_'.$relatedTo;
      $relatedPeer = getPeerNameForClass($classes[$relatedAlias]['className']);
      // get TableMap for related
      $relatedTM = call_user_func_array(array($relatedPeer, 'getTableMap'), array());

      $relatedPKs = $relatedTM->getPrimaryKeyColumns();

      $baseFKNames = array();
      $i=0;
      foreach ($relatedPKs as $relatedPK)
      {
        //count the number of iterations
        $i++;

        $relatedPKName = $relatedPK->getColumnName();

        // search for relation (BaseTable-ForeignKey / ForeignTable-PrimaryKey)
        $RelatedByArr = explode('RelatedBy', $relatedTo, 2);
        foreach ($baseTM->getColumns() as $relatedColumn)
        {
          // if one foreign key refering from baseTable to RelatedTable
          if (count($RelatedByArr)==1)
          {
            $colRelTableName = $relatedColumn->getRelatedTableName();
            if ((!empty($colRelTableName)) && ($baseTM->getDatabaseMap()->containsTable($colRelTableName)) && ($baseTM->getDatabaseMap()->getTable($colRelTableName)->getPhpName() == $RelatedByArr[0]))
            {
              $baseFKNames[] = $relatedColumn->getName();
              break; // stop iterating
            }
          }
          // if multiple foreign keys refering from baseTable to RelatedTable
          else
          {
            if ($relatedColumn->getPhpName() == $RelatedByArr[1])
            {
              $baseFKNames[] = $relatedColumn->getName();
              break;
            }
          }
        }


        //TODO: reverse lookup (per table) to fix hack from above
        if (count($baseFKNames) < $i)
        {

          foreach ($baseTM->getColumns() as $relatedColumn);
          // TODO HIER BEN IK

        }

        $currentBaseFKName = $baseFKNames[count($baseFKNames)-1];
        $joinColumnLeft  = call_user_func_array(array($peer, 'alias'), array($alias, constant($peer.'::'.$currentBaseFKName)));
        $joinColumnRight = call_user_func_array(array($relatedPeer, 'alias'), array($relatedAlias, constant($relatedPeer.'::'.$relatedPKName)));
        $joinType = Criteria::LEFT_JOIN; //TODO: add lookup table that can define the joinType

        $criteria->addJoin($joinColumnLeft, $joinColumnRight, $joinType);
      }
    }
  }

  return $criteria;
}

/**
 * private method to hydrate the (related)objects from the objectPaths,
 *
 * the functionality depends on the constructor call (have custom Criteria been provided, or is hydration of objects possible)
 */

/**
 * hydrates the data for the objects in the objectPaths from the database
 * and places them in an array.
 *
 * @param Criteria $criteria
 * @param array[string] $objectPaths
 * @param PDO $connection
 *
 * @return array        the array of hydrated (base)objects, with there relations
 */
function hydrate($criteria = null, $objectPaths, $connection = null)
{
  // data holds all main results
  $data = array();

  $stmt = BasePeer::doSelect($criteria, $connection);
  $results = $stmt->fetchAll(PDO::FETCH_NUM);

  $baseClass = resolveBaseClass($objectPaths[0]);
  $basePeer = getPeerNameForClass($baseClass);
  $classes = array();
  // pre-process classnames and there mutual relations
  foreach ($objectPaths as $objectPath)
  {
    $classes = flattenAllClasses($objectPath, $classes);
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
      $relatedPeer = getPeerNameForClass($relatedClassName);

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
        $addMethod = 'add'.resolveFirstMethodForObjectPath($paths[$nrPaths-2].'.'.$paths[$nrPaths-1]);

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

    $instance->hydrateCustomColumns($row, $startcol, $criteria);

    $data[] = $instance;
  }

  $stmt->closeCursor();

  return $data;
}


/**
 * Counts the number of results for the generated query
 * TODO: add support for inner/strict/right joins as well!
 *
 * @param Criteria $criteria
 * @param array[string] $objectPaths
 * @param PDO $connection
 *
 * @return int    the number of results for the generated query
 */
function countAll($criteria, $objectPaths, $connection = null)
{
  $baseClass = resolveBaseClass($objectPaths[0]);
  $basePeer = getPeerNameForClass($baseClass);
  $alias = $baseClass;

  // we're going to modify criteria, so copy it first
  $criteria = clone $criteria;

  $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count
  $criteria->setLimit(-1);          // LIMIT affects the count negative
  $criteria->setOffset(0);          // OFFSET affects the count negative

  // We need to set the primary table name, since in the case that there are no WHERE columns
  // it will be impossible for the BasePeer::createSelectSql() method to determine which
  // tables go into the FROM clause.
  $criteria->setPrimaryTableName(constant($basePeer.'::TABLE_NAME'));
  $criteria->addAlias($alias, constant($basePeer.'::TABLE_NAME'));

  $criteria = addJoins($criteria, $objectPaths, false);


  if (!$criteria->hasSelectClause())
  {
    call_user_func_array(array($basePeer, 'addSelectColumnsAliased'), array($criteria, $alias));
  }
  // this always has to be done, since you might want to filter on these columns
  call_user_func_array(array($basePeer, 'addCustomSelectColumns'), array($criteria, $alias));

  if ($connection === null)
  {
    $connection = Propel::getConnection(constant($basePeer.'::DATABASE_NAME'), Propel::CONNECTION_READ);
  }

  // BasePeer returns a PDOStatement
  $stmt = BasePeer::doCount($criteria, $connection);

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


