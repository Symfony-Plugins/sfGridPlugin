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
 * @param string $objectPath an objectPath, with syntax baseClassName.ChildClassName.ChildClassName
 *                           the childClassNames should have a getter in the baseClass
 *
 */
function checkObjectPath($objectPath)
{
  $classRelations = explode('.', $objectPath, 2);

  // if path was not provided
  if (count ($classRelations) == 0)
  {
    throw new UnexpectedValueException('empty path was provided');
  }

  $baseClass = $classRelations[0];
  // then it must be an existing class
  if (!class_exists($baseClass))
  {
    throw new UnexpectedValueException(sprintf('Class "%s" does not exist', $baseClass));
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
  //   - BUG when having multple FKs to same related Table and you do a one-to-many lookup it will find all FKs, you cannot define one FK right now (TODO: with something like relatedBY)...
  //   - NOT tested are self referencing relations (the issue with this is there depth is unknown)
  //   - NOT implemented is joining i18n related tables automatically.
  if (isset($classRelations[1]))
  {
    $relatedClassRelations = explode('.', $classRelations[1], 2);
    $relationName = array_shift($relatedClassRelations);
    $relationPath = $baseClass.'.'.$relationName;

    $relatedClass = resolveClassNameFromObjectPath($relationPath);
    $isOneToMany  = ($relatedClass.'s' == $relationName);
    $foreignKeys  = resolveForeignKeysForRelationPath($relationPath);

//    echo $relationPath.':<br>';
//    var_dump($foreignKeys);

    array_unshift($relatedClassRelations, $relatedClass);
    $newObjectPath = implode('.', $relatedClassRelations);

    //recursively check
    checkObjectPath($newObjectPath);

//    die($relatedClass);
//    // first check if relationName contains the 'RelatedBy' Keyword
//
//    // test with reflection if getter exists for RelationName
//    $getterMethod = 'get'.$relationName;
//    if (!$baseReflection->hasMethod($getterMethod))
//    {
//      // if getter does not exist, but class does, check if there are multiple PKs for the related class, with multiple FKs from base
//      if (class_exists($relationName))
//      {
//        $relatedPeer = getPeerNameForClass($relationName);
//        $relatedTM = call_user_func(array($relatedPeer, 'getTableMap'));
//        $relatedPks = $relatedTM ->getPrimaryKeyColumns();
//
//        // if not multiple PKs the user provided an illegal
//        if (count($relatedPks) <= 1)
//        {
//          $relatedClass = '';
//        }
//      }
//      else
//      {
//        throw new LogicException(sprintf('Class "%s" has no method called "%s". Tip: you can add your own get-method that returns the related object.', $baseClass, $getterMethod));
//      }
//    }
//
//    // get directly related ClassName
//    $partialObjectPath = $baseClass . '.' .$relationName;
//    $relatedClassName = resolveClassNameFromObjectPath($partialObjectPath);
//    $addMethod = 'add'.resolveFirstMethodForObjectPath($partialObjectPath);
//    $setMethod = 'set'.resolveFirstMethodForObjectPath($partialObjectPath);
//
//    // then it must be an existing class
//    if (!class_exists($relatedClassName))
//    {
//      if (substr($relatedClassName, -1) == 's' // Get the last character
//          &&
//          class_exists(substr($relatedClassName, 0, -1))) // Strip last character
//      {
//        // switch classnames, to make the relatedClass name the base and visa versa
//        $orgRelatedClassName = $relatedClassName;
//        $relatedClassName = $baseClass;
//        $baseClass = substr($orgRelatedClassName, 0, -1);
//        $partialObjectPath = $className.'.'.$relatedClassName;
//        $addMethod = 'add'.resolveFirstMethodForObjectPath($partialObjectPath);
//        $setMethod = 'set'.resolveFirstMethodForObjectPath($partialObjectPath);
//      }
//      else
//      {
//        throw new LogicException(sprintf('Class "%s" does not exist.
//                                         Please note: don\'t use the getter for the foreign-key value, but the getter for the related class instance', $relatedClassName));
//      }
//    }
//
//    // test here for add-er
//    $relatedReflection = new ReflectionClass($relatedClassName);
//    if (!$relatedReflection->hasMethod($addMethod))
//    {
//      if (!$relatedReflection->hasMethod($setMethod))
//      {
//        throw new LogicException(sprintf('Class "%s" has no method called "%s" or "%s".', $relatedClassName, $addMethod, $setMethod));
//      }
//    }
  }

  // done, sucessfully parsed the objectPath
}


/**
 * Resolves the ForeingKeys between two tables,
 *
 * @param string $relationPath  partial objectPath consisting of two objects
 *
 * @throws UnexpectedValueException throws an UnexpectedValueException if something else than two parts have been provided in the path
 * @throws Exception  throws an exception if no foreignKeys can be found
 * @return array      array of foreignKeys
 *
 */
function resolveForeignKeysForRelationPath($relationPath)
{
  $parts = explode('.', $relationPath);
  if (count($parts) != 2)
  {
    throw new UnexpectedValueException($relationPath.' should only consist out of two parts!');
  }

  list($baseClass, $relationName) = $parts;
  $basePeer     = getPeerNameForClass($baseClass);
  $relatedClass = resolveClassNameFromObjectPath($relationPath);
  $relatedPeer  = getPeerNameForClass($relatedClass);

  $isOneToMany  = ($relatedClass.'s' == $relationName);
  $foreignKeys  = array();

  // if RelatedBy in $relationName, there is only one foreignKey
  $relationParts = explode('RelatedBy', $relationName);
  if (count($relationParts)>1)
  {
    $foreignKeyPhp = $relationParts[1];

    $baseReflection = new ReflectionClass($baseClass);
    $getRelated = 'get'.$relationName;
    if ($baseReflection->hasMethod($getRelated))
    {
      $baseTM = call_user_func(array($basePeer, 'getTableMap'));

      // convert fieldPhpName to tableName
      foreach ($baseTM->getColumns() as $column)
      {
        if ($column->getPhpName() == $foreignKeyPhp)
        {
          $foreignKeys[] = $column->getName();
          break;
        }
      }
    }
  }
  // directly (with one or multiple PKs) (many-to-one, or one-to-one;  base_class contains the FK(s) to the related table)
  else if (!$isOneToMany)
  {
    $baseTM = call_user_func(array($basePeer, 'getTableMap'));
    $relatedTableName = constant($relatedPeer.'::TABLE_NAME');

    // find all foreign keys refering from baseTable to RelatedTable
    foreach ($baseTM->getColumns() as $column)
    {
      $colRelTableName = $column->getRelatedTableName();

      if ((!empty($colRelTableName)) // found a FK column
          && ($colRelTableName == $relatedTableName)) // and FK refering to relatedTable
      {
        $foreignKeys[] = $column->getName();
      }
    }
  }
  // one to many (related_class contains the FK(s) to the base table)
  else
  {
    $relatedTM = call_user_func(array($relatedPeer, 'getTableMap'));
    $baseTableName = constant($basePeer.'::TABLE_NAME');

    // find all foreign keys refering from RelatedTable to baseTable
    foreach ($relatedTM->getColumns() as $column)
    {
      $colRelTableName = $column->getRelatedTableName();

      if ((!empty($colRelTableName)) // found a FK column
          && ($colRelTableName == $baseTableName)) // and FK refering to base Table
      {
        $foreignKeys[] = $column->getName();
      }
    }

  }

  if (count($foreignKeys)==0)
  {
    throw new Exception('No ForeignKeys can be found for relationPath "'.$relationPath.'"');
  }

  return $foreignKeys;
}

function getPeerNameForClass($class)
{
  return constant($class.'::PEER');
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

  $lastObject = resolveClassNameFromObjectPath($objectPath);
  $getterMethod = 'get'.$property;

  if (!class_exists($lastObject))
  {
    if (substr($lastObject, -1) == 's' // Get the last character
        &&
        class_exists(substr($lastObject, 0, -1))) // Strip last character
    {
      $lastObject = substr($lastObject, 0, -1);
    }
  }


  if (!method_exists($lastObject, $getterMethod))
  {
    // test if it possibly is a custom column
    $lastObjectPeer = getPeerNameForClass($lastObject);

    $custom = array_key_exists($property, call_user_func(array($lastObjectPeer, 'getCustomColumns')));
    if (!$custom)
    {
      throw new LogicException(sprintf('Class "%s" has no method called "%s".', $lastObject, $getterMethod));
    }
  }

}


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
  $classRelations = explode('.', $objectPath);
  $lastClassReference = $classRelations[count($classRelations)-1];

  // if there are multiple references to the same table, remove the RelatedBy.... part
  $classParts = explode('RelatedBy', $lastClassReference);
  $className = $classParts[0];

  // if ClassnameS (with s) provided, remove s (but check if this is valid)
  if (!class_exists($className)
      &&
      substr($className, -1) == 's') // check last character = s
  {
    $className = substr($className, 0, -1); //remove the s from the string
  }

  if (!class_exists($className))
  {
    throw new UnexpectedValueException(sprintf('Classname cannot be resolved! ObjectPath "%s" results in invalid classname "%s".', $objectPath, $className));
  }

  return $className;
}

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
 * Resolves the Add-method for the first relation in an objectPath
 *
 * @param string $objectPath an objectPath, with syntax baseClassName.ChildClassName.ChildClassName
 *                           the childClassNames should have a getter in the baseClass
 * @return string            the add-Method of the child to register itself to its parent
 */
//TODO: this should automatically find add / set
function resolveFirstMethodForObjectPath($objectPath)
{
  // get the latest classReference (the part after the last '.')
  $classRelations = explode('.', $objectPath);
  $className = $classRelations[0];
//  $classReference = $classRelations[count($classRelations)-1];

  $related = '';
  // if there are multiple references to the same table, remove the RelatedBy.... part
  $classParts = explode('RelatedBy', $classRelations[1]);
  if (count($classParts)>1)
  {
    $related = 'RelatedBy'.$classParts[1];
  }

  return $className.$related;
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
  $classRelations = explode('.', $objectPath, 2);
  $relationName = $parent.$classRelations[0];

  // add Class to array, if not already known
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

    $classes = resolveAllClasses($classRelations[1], $classes, $relationName.'_');
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
    $classes =  resolveAllClasses($objectPath, $classes);
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


