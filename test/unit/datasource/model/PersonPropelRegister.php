<?php

class PersonPropelRegister 
{
  /**
   * Enter description here...
   *
   * @param PropelPDO $connection
   * @return sfDataSourceInterface
   */
  public static function getPersonPropelDataSource($connection = null)
  {
//    $countCriteria = PersonPropelPeer::getCountCriteria();
//    $selectCriteria = PersonPropelPeer::extendedWithSelectColumns($countCriteria);
    
//    $source = new sfDataSourcePropel($selectCriteria, $countCriteria);
    $source = new sfDataSourcePropel('PersonPropel');
    
    $source->setConnection($connection);

    return $source;
  }
  
}
