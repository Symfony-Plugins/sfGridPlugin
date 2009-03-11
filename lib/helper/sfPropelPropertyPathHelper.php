<?php

/*
 * This file is part of the symfony package.
 * (c) Leon van der Ree <leon@fun4me.demon.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//   - Custom Columns are now only correctly hydrated for the base Object
//     (define them again in your peer, see peer::addCustomSelectColumns and object-hydrateCustomColumns)


/**
 * @package    symfony
 * @subpackage helper
 * @author     Leon van der Ree <leon@fun4me.demon.nl>
 * @version    SVN: $Id$
 *
 *
 */

/**

Example 1:

$criteria = new Criteria();
$objectPaths = array('Foto', 'Foto.Album');

$criteria = addJoinsAndSelectColumns($criteria, $objectPaths);
$fotos = hydrate($criteria, $objectPaths, $connection = null);

foreach ($fotos as $foto)
{
  echo $foto->getAlbum()->getTitle();
}




Example 2:

$criteria = new Criteria();
$objectPaths = array('Album.Foto'); // no need to provide Album first, this can be deducted (similar in the first example)

$criteria = addJoinsAndSelectColumns($criteria, $objectPaths);
$albums = hydrate($criteria, $objectPaths, $connection = null);

foreach ($albums as $album)
{
  foreach ($album->getFotos() as $foto)
  {
    echo $foto->getTitle();
  }
}


 *
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
  //
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
  $objectPath = implode('.', $parts);

  $lastClass = resolveClassNameFromObjectPath($objectPath);
  $lastPeer = getPeerNameForClass($lastClass);

  try
  {
    $fieldName = call_user_func_array(array($lastPeer, 'translateFieldName'), array($property, BasePeer::TYPE_PHPNAME, BasePeer::TYPE_FIELDNAME));
  }
  catch (PropelException $e)
  {
    // $property is no fieldName, possibly custom column;no need to translate
    $fieldName = $property;
  }

  return $aliasedColumn.'.'.$fieldName;
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
  $className = $classRelations[0];
  $peer = getPeerNameForClass($className);

  $relations = call_user_func(array($peer, 'getRelations'));

  if ($parent == '')
  {
    $parent = $className;
  }
  $relationName = $parent;

  // add alliased Class to array, if not already known
  if (!isset($classes[$relationName]))
  {
    $classes[$relationName] = array('className' =>  $className,
                                    'relatedTo' => array()
    );
  }

  // if relations left, add these
  if (isset($classRelations[1]))
  {
    $relationNames = explode('.', $classRelations[1]);

    $relatedTo = array_shift($relationNames);
    if (!array_key_exists($relatedTo, $classes[$relationName]['relatedTo']))
    {
      $classes[$relationName]['relatedTo'][$relatedTo] = $relations[$relatedTo];
    }

    $relatedClass = resolveClassNameFromObjectPath($className.'.'.$relatedTo);
    array_unshift($relationNames, $relatedClass);

    $newObjectPath = implode('.', $relationNames);

    $classes = flattenAllClasses($newObjectPath, $classes, $relationName.'.'.$relatedTo);
  }

  return $classes;
}

/**
 * FlatternsAllClasses from an array
 *
 * @param array[string] $objectPaths    an array of objectPaths
 * @return array                        an array of Classes derived from the objectPaths
 */
function flattenAllClassesArray($objectPaths)
{
  $classes = array();
  $baseClass = resolveBaseClass($objectPaths[0]);

  // construct complete class-overview of all combined objectPaths
  foreach ($objectPaths as $objectPath)
  {
    // test if there is only one base class
    if ($baseClass != ($currentBaseClass = resolveBaseClass($objectPath)))
    {
      throw new LogicException(sprintf('Not all base classes are the same.
                                        Resolved "%s", while expecting "%s"', $currentBaseClass, $baseClass));
    }

    // get flat array of classes that need to get hydrated
    $classes =  flattenAllClasses($objectPath, $classes);
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
 * @see addJoins()
 *
 */
function addJoinsAndSelectColumns(Criteria $criteria = null, $objectPaths)
{
  return addJoins($criteria, $objectPaths, true);
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
 * @param array $objectPaths         an array of object Paths
 * @param bool $withColumns          add Select Columns to the criteria
 *                                   When enabled also the select columns will be added to the criteria, see
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
function addJoins(Criteria $criteria = null, $objectPaths, $withColumns = false)
{
  // clone criteria, since we are going to modify it
  $criteria = clone $criteria;

  // if the source is provided as object paths, create hydratable criteria
  if (!is_array($objectPaths))
  {
    throw new InvalidArgumentException('No ObjectPaths provided (this should be an array)');
  }

  // generate an array of classes to be retrieved from DB
  $baseClass = resolveBaseClass($objectPaths[0]);
  $basePeer = getPeerNameForClass($baseClass);

  // construct full hydration-profile
  $criteria->setDbName(constant($basePeer.'::DATABASE_NAME'));

  // We need to set the primary table name, since in the case that there are no WHERE columns
  // it will be impossible for the BasePeer::createSelectSql() method to determine which
  // tables go into the FROM clause.
  $criteria->setPrimaryTableName(constant($basePeer.'::TABLE_NAME'));

  // construct complete class-overview of all combined objectPaths
  $classes = flattenAllClassesArray($objectPaths);

  // process all classes
  foreach ($classes as $baseObjectPath => $class)
  {
    $alias = str_replace('.', '_', $baseObjectPath);
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

    //join related
    foreach ($class['relatedTo'] as $relatedTo => $relation)
    {
      $objectPath = $baseObjectPath.'.'.$relatedTo;
      $relatedAlias = str_replace('.', '_', $objectPath);

      $relatedPeer = getPeerNameForClass($classes[$objectPath]['className']);

      $joinColumnsLeft = array();
      $joinColumnsRight = array();
      foreach ($relation['leftKeys'] as $field)
      {
        $joinColumnsLeft[] =  call_user_func_array(array($peer, 'alias'), array($alias, $field));
      }
      foreach ($relation['rightKeys'] as $field)
      {
        $joinColumnsRight[] =  call_user_func_array(array($relatedPeer, 'alias'), array($relatedAlias, $field));
      }
      $joinType = $relation['joinType'];

      $criteria->addJoin($joinColumnsLeft, $joinColumnsRight, $joinType);
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
 * @param Criteria $criteria          The criteria object, matching the provided objectPaths (see addJoins)
 * @param array[string] $objectPaths  the objectPaths, related to the criteria
 * @param PDO $connection             a PDO connection to perform the query with
 *
 * @return array        the array of hydrated (base)objects, with there relation (from the objectPaths)
 */
function hydrate(Criteria $criteria, $objectPaths, $connection = null)
{
  // data holds all main results
  $data = array();
  
  $processedResults = array();

  if (!Propel::isInstancePoolingEnabled())
  {
    throw new Exception('You need to enable instance pooling to make hydration work correctly');
    // this can also be solved by implementing a local instance pooling array in this method...
  }

  // if the source is provided as object paths, create hydratable criteria
  if (!is_array($objectPaths))
  {
    throw new InvalidArgumentException('No ObjectPaths provided (this should be an array)');
  }

  // execute query
  $stmt = BasePeer::doSelect($criteria, $connection);
  $results = $stmt->fetchAll(PDO::FETCH_NUM);

  // construct complete class-overview of all combined objectPaths
  $classes = flattenAllClassesArray($objectPaths);

  //remove the base class from the list, since this is hydrated first by default
  array_shift($classes);
  $baseClass = resolveBaseClass($objectPaths[0]);
  $basePeer = getPeerNameForClass($baseClass);

  // hydrate all (related)objects with the resultset
  foreach ($results as $row)
  {
    $startcol = 0;
    // keep track of current results of this row, to simplify adding/setting related objects
    $rowResult = array();

    // hydration of the base object
    $key = call_user_func_array(array($basePeer, 'getPrimaryKeyHashFromRow'), array($row, $startcol));
    if (!isset($processedResults[$baseClass]))
    {
      $processedResults[$baseClass] = array();
    }
    if (isset($processedResults[$baseClass][$key]))
    {
      $new = false;
      $instance = $processedResults[$baseClass][$key];
    }
    else
// TODO: add instance pooling as well
//    if (($instance = call_user_func_array(array($basePeer, 'getInstanceFromPool'), array($key))) === null)
    {
      $new = true;
      $omClass = call_user_func(array($basePeer, 'getOMClass'));

      $cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
      $instance = new $cls();
      $instance->hydrate($row);
      
// TODO: add instance pooling as well
//      call_user_func_array(array($basePeer, 'addInstanceToPool'), array($instance, $key));
      $processedResults[$baseClass][$key] = $instance;
    }

    // calculate startCol in row for first related class
    $startcol += constant($basePeer.'::NUM_COLUMNS') - constant($basePeer.'::NUM_LAZY_LOAD_COLUMNS');

    // add base object to current rowResult
    $rowResult[$baseClass] = $instance;

    // process all related-classes
    foreach ($classes as $objectPath => $relatedClass)
    {
      $relatedClassName = $relatedClass['className'];
      $relatedPeer = getPeerNameForClass($relatedClassName);

      $parts = explode('.', $objectPath);
      $relationName = array_pop($parts);
      $parentObjectPath = implode('.', $parts);
      $parentClass = resolveClassNameFromObjectPath($parentObjectPath);
      $parentPeer = getPeerNameForClass($parentClass);

      // get parent instance
      $parent = isset($rowResult[$parentObjectPath]) ? $rowResult[$parentObjectPath] : null;

      $parentRelations = call_user_func(array($parentPeer, 'getRelations'));
      $relation = $parentRelations[$relationName];

      $key = call_user_func_array(array($relatedPeer, 'getPrimaryKeyHashFromRow'), array($row, $startcol));
      if ($key !== null)
      {
        if (!isset($processedResults[$objectPath]))
        {
          $processedResults[$objectPath] = array();
        }
        if (isset($processedResults[$objectPath][$key]))
        {
          $relatedObj = $processedResults[$objectPath][$key];
        }
        else
//        $relatedObj = call_user_func_array(array($relatedPeer, 'getInstanceFromPool'), array($key));
//        if (!$relatedObj)
        {
          $omClass = call_user_func(array($relatedPeer, 'getOMClass'));

          $cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
          $relatedObj = new $cls();
          $relatedObj->hydrate($row, $startcol);
          
//          call_user_func_array(array($relatedPeer, 'addInstanceToPool'), array($relatedObj, $key));
          $processedResults[$objectPath][$key] = $relatedObj;
        }

        // add related object to current rowResult
        $rowResult[$objectPath] = $relatedObj;

        $associateMethod = $relation['associateMethod'];

        // associate related object to parent
        call_user_func_array(array($relatedObj, $associateMethod), array($parent));

      } else {
        if (($parent != null) && $relation['oneToMany'])
        {
          $touchMethod = 'touch'.$relationName;
          call_user_func(array($parent, $touchMethod));
        }
      }

      // add column-count to startcol for next object
      $startcol += constant($relatedPeer.'::NUM_COLUMNS') - constant($relatedPeer.'::NUM_LAZY_LOAD_COLUMNS');
    }

    // hydrate custom columns and add them to base TODO: hydrate customs per class
    $instance->hydrateCustomColumns($row, $startcol, $criteria);

    // add new instances (including all relations) to the data array (existing instances are updated, no need to re-add them)
    if ($new)
    {
      $data[] = $instance;
    }
  }

  $stmt->closeCursor();

  return $data;
}


/**
 * Counts the number of results for the generated query
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

  $criteria = addJoins($criteria, $objectPaths);

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


