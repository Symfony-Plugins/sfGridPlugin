<?php

/**
 * Subclass for representing a row from the 'album' table.
 *
 *
 *
 * @package lib.model
 */
class Album extends BaseAlbum
{
  /**
   * contains the result from the doSelectQuery if this is a leaf, or contains children
   *
   * @var boolean
   */
  protected $isLeaf;

  /**
   * returns if the Ablum has children or not
   * @var boolean
   */
  public function isLeaf(){
    return $this->isLeaf;
  }

  public function isParent(){
    return $this->getAlbumId() != null;
  }

  /**
   * Returns path of this album
   *
   * @return string
   */
  public function __toString()
  {
    $title = $this->getMap().DIRECTORY_SEPARATOR;

    if ($this->isParent()) $title = $this->getAlbumRelatedByAlbumId()->__toString().$title;

    return $title;
  }

  /**
   * Returns Fotos in this album
   *
   * @return array
   */
  public function getFotosInAlbum()
  {
    return FotoPeer::getFotosInAlbum($this->getId());
  }

  /**
   * Sets the isLeaf property
   *
   * @param      boolean $v new value
   * @return     void
   */
  public function setIsLeaf($v){
    $this->isLeaf = $v;
  }
}
