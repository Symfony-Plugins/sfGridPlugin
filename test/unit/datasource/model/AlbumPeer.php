<?php

/**
 * Subclass for performing query and update operations on the 'album' table.
 *
 *
 *
 * @package lib.model
 */
class AlbumPeer extends BaseAlbumPeer
{
  public static function getAlbumsInAlbum($parent_album_id)
  {
    $criteria = new Criteria();

    $criteria->add(AlbumPeer::ALBUM_ID, $parent_album_id);

    return AlbumPeer::doSelect($criteria);
  }

  /**
   * Method to do selects.
   *
   * @param      Criteria $criteria The Criteria object used to build the SELECT statement.
   * @param      PropelPDO $con
   * @return     array Array of selected Objects
   * @throws     PropelException Any exceptions caught during processing will be
   *     rethrown wrapped into a PropelException.
   */
  public static function doSelectOld(Criteria $criteria, PropelPDO $con = null)
  {
    $criteria = clone $criteria;

    // Set the correct dbName if it has not been overridden
    if ($criteria->getDbName() == Propel::getDefaultDB()) {
      $criteria->setDbName(self::DATABASE_NAME);
    }

    AlbumPeer::addSelectColumns($criteria);
    $criteria->addJoin(self::ID, self::alias('children', self::ALBUM_ID), Criteria::LEFT_JOIN);
    $criteria->addAlias('children', self::TABLE_NAME);
    $criteria->addAsColumn('leafs', 'count(children.id)');
    $criteria->addGroupByColumn(self::ID);


    $rs = BasePeer::doSelect($criteria, $con);
    $results = array();

    while($rs->next()) {
      $omClass = AlbumPeer::getOMClass();

      $cls = Propel::import($omClass);
      $obj1 = new $cls();
      $obj1->hydrate($rs);
      //add extra info to object
      $obj1->setIsLeaf($rs->getInt(AlbumPeer::NUM_COLUMNS+1)==0);

      $results[] = $obj1;
    }
    return $results;
  }
}
