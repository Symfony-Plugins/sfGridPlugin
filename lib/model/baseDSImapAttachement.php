<?php
/**
 * Class to store an attachment
 *
 * @author    Iwan van Staveren (iwan@e-onesw.nl)
 * @author    Leon van der Ree (leon@fun4me.demon.nl)
 * @version   $Id:$
*/
class baseDSImapAttachement
{
  /**
   * The name of the attachment
   *
   * @var string
   */
  protected $filename = '';

  /**
   * Mime type of the attachment
   *
   * @var string
   */
  protected $mimeType = '';
    
  /**
   * The data of the attachment
   *
   * @var mixed
   */
  protected $data = null;
  
  
  public function __construct($filename, $mimeType, $data)
  {
    $this->filename = $filename;
    $this->mimeType = $mimeType;
    $this->data     = $data;
  }
  
  public function getFilename()
  {
    return $this->filename;
  }
  
  public function getMimeType()
  {
    return $this->getMimeType();
  }
  
  public function GetData()
  {
    return $this->data;
  }
  
}
?>