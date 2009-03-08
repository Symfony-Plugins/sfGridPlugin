<?php

/**
 * Subclass for performing query and update operations on the 'foto' table.
 *
 *
 *
 * @package lib.model
 */
class FotoPeer extends BaseFotoPeer
{
  public static function getObjectPaths()
  {
    return array('Foto', 'Foto.Album');
  }

//  public function uploadFoto() {
//    // upload foto into originals folder, with random filename
//    // store filename and exif info into database
//    //   related the foto to the album
//  }
//
//  public function getUrlToFoto(int $id, array $size){
//
//  }

  public static function getFotosInAlbum($parent_album_id)
  {
    $criteria = new Criteria();

    $criteria->add(FotoPeer::ALBUM_ID, $parent_album_id);

    return FotoPeer::doSelect($criteria);
  }


  /**
   * Returns the number of rows matching criteria, joining all related tables
   *
   * @param      Criteria $c
   * @param      PropelPDO $con
   * @return     int Number of matching rows.
   */
  public static function doCountJoin(Criteria $criteria, PropelPDO $con = null)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('sfPropel'));

    // we're going to modify criteria, so copy it first
    $criteria = clone $criteria;

    // We need to set the primary table name, since in the case that there are no WHERE columns
    // it will be impossible for the BasePeer::createSelectSql() method to determine which
    // tables go into the FROM clause.
    $criteria->setPrimaryTableName(self::TABLE_NAME);

    if (!$criteria->hasSelectClause())
    {
      self::addSelectColumns($criteria);
    }

    $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

    if ($con === null)
    {
      $con = Propel::getConnection(self::DATABASE_NAME, Propel::CONNECTION_READ);
    }

    $criteria = addJoins($criteria, self::getObjectPaths(), false);

    $stmt = BasePeer::doCount($criteria, $con);
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
   * Selects a collection of Foto objects pre-filled with all related objects.
   *
   * @param      Criteria  $c
   * @param      PropelPDO $con
   * @return     array Array of Foto objects.
   * @throws     PropelException Any exceptions caught during processing will be
   *     rethrown wrapped into a PropelException.
   */
  public static function doSelectJoin(Criteria $criteria, PropelPDO $con = null)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('sfPropel'));

    // we're going to modify criteria, so copy it first
    $criteria = clone $criteria;

    $criteria = addJoins($criteria, self::getObjectPaths());

    $criteria->addAsColumn('test_column', 'CONCAT(Foto_Album.TITLE, \' - \', Foto_Album.DESCRIPTION)');

    $data = loadData($criteria, self::getObjectPaths(), array('test_column'), $con);

    return $data;
  }

  public static function doSelectJoinHasColum(){}
  public static function doSelectJoinGetValue(){}

  /**
   * Enter description here...
   *
   * @param Criteria $criteria
   * @param string $column
   * @param string $order
   * @return Criteria
   */
  public static function doSelectJoinSort(Criteria $criteria, $column, $order)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('sfPropel'));

    $baseClass = 'Foto';
    $propertyPath = $baseClass.'.'.$column;

    $column = translatePropertyPathToAliasedColumn($propertyPath);

    switch ($order)
    {
      case sfDataSourceInterface::ASC:
        $criteria->addAscendingOrderByColumn($column);
        break;
      case sfDataSourceInterface::DESC:
        $criteria->addDescendingOrderByColumn($column);
        break;
      default:
        throw new Exception('sfDataSourcePropel::doSort() only accepts "'.sfDataSourceInterface::ASC.'" or "'.sfDataSourceInterface::DESC.'" as argument');
    }

    return $criteria;
  }

}
