<?php

class PersonPropelPeer extends BasePersonPropelPeer
{

  /**
   * Enter description here...
   *
   * @return Criteria
   */
  public static function getCountCriteria()
  {
    $countCriteria = new Criteria();
    
    $countCriteria->setPrimaryTableName(self::TABLE_NAME);
    
    self::addSelectColumns($countCriteria);
    
//    $countCriteria->addJoin(array(PersonPeer::TYPE_ID,), array(TypePeer::alias('type', TypePeer::ID)), Criteria::LEFT_JOIN);
//    $countCriteria->addAlias('type', TypePeer::TABLE_NAME);
    
    return $countCriteria;
  }
  
  /**
   * Enter description here...
   *
   * @param Criteria $baseCriteria
   * @return Criteria
   */
  public static function extendedWithSelectColumns($baseCriteria)
  {
    $selectCriteria = clone $baseCriteria;
    
////    TypePeer::addSelectColumns($selectCriteria);
//    $selectCriteria->addSelectColumn('type.ID');
//    $selectCriteria->addSelectColumn('type.NAME');
    
    return $selectCriteria;
  }  
}
