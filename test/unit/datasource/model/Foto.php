<?php

/**
 * Subclass for representing a row from the 'foto' table.
 *
 *
 *
 * @package lib.model
 */
class Foto extends BaseFoto
{
  public function getUrl()
  {
    //TODO: add album(s) recursively
    return $this->getFilename();
  }


  private function getBasePath()
  {
    $sf_root_dir = sfConfig::get('sf_root_dir');
    $basePath = $sf_root_dir.DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'fotos'.DIRECTORY_SEPARATOR;

    return $basePath;
  }

  private function getOrgPathAlbum()
  {
    $orgPath = 'originals'.DIRECTORY_SEPARATOR;

    return $this->getBasePath().$orgPath.$this->getAlbum();
  }

  private function getOrgPathFoto()
  {
    return $this->getOrgPathAlbum().$this->getFilename();
  }

  private function getThumbPathAlbum($max_height)
  {
    $thumb_path = 'thumb'.DIRECTORY_SEPARATOR.$max_height.DIRECTORY_SEPARATOR;

    return $this->getBasePath().$thumb_path.$this->getAlbum();
  }

  private function getThumbPathFoto($max_height)
  {
    return $this->getThumbPathAlbum($max_height).$this->getFilename();
  }

  public function getThumb($max_height = 60)
  {
    $max_width = (4/3) * $max_height;

    $org_path_foto = $this->getOrgPathFoto();
    $thumb_path_folder = $this->getThumbPathAlbum($max_height);
    $thumb_path_foto = $this->getThumbPathFoto($max_height);

    if (file_exists($org_path_foto))
    {
      if (!file_exists($thumb_path_foto))
      {
        if (!is_dir($thumb_path_folder))
        {
          mkdir($thumb_path_folder, 0755, true);
        }

        $thumbnail = new sfThumbnail($max_width, $max_height, true, true, 100);
        $thumbnail->loadFile($org_path_foto);
        $thumbnail->save($thumb_path_foto, 'image/jpeg');
      }
    }
    else
    {
       $thumb_path_foto = 'Location: '.$org_path_foto.' to original image does not exist';
    }

    $thumb = file_get_contents($thumb_path_foto);

    return $thumb;
  }

  public function getScaled()
  {
  	return $this->getThumb(480);
  }
  
  public function getFoto()
  {
    $org_path_foto = $this->getOrgPathFoto();
   
    $foto = file_get_contents($org_path_foto);

    return $foto;
  }

}
