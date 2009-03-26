<?php
/**
 * class baseDSImapMessage
 * 
 * Storage for a imap message.
 *
 * @author    Iwan van Staveren (iwan@e-onesw.nl)
 * @author    Leon vander Ree (leon@fun4me.demon.nl)
 * @version   Release: @package_version@
 */
class baseDSImapMessage
{
  const TYPE_PLAIN = 'TEXT/PLAIN';
  const TYPE_HTML  = 'TEXT/HTML';

  public static function getMimeType($structure) 
  {
   $primary_mime_type = array("TEXT", "MULTIPART","MESSAGE", "APPLICATION", "AUDIO","IMAGE", "VIDEO", "OTHER");
   
   if($structure->subtype) 
   {
    return $primary_mime_type[(int) $structure->type] . '/' .$structure->subtype;
   }
   
   return self::TYPE_PLAIN;
 }
  
  
  /**
   * The stream to the imap-server
   *
   * @var resource
   */
  protected $stream;

  /**
   * The subject of the message
   *
   * @var string
   */
  protected $subject;
    
  /**
   * Sender of the message
   *
   * @var string
   */
  protected $from;
  
  /**
   * Receiver(s) of the message
   *
   * @var string
   */
  protected $to;
  
  /**
   * Date and time of the message
   *
   * @var string date
   */
  protected $date;
    
  /**
   * The message identifier
   *
   * @var int
   */
  protected $msgId;
  
  /**
   * TODO: PHP DOCUMENTATION for rest of the vars
   *
   * @var unknown_type
   */
  protected $reference;
  protected $replyTo;
  protected $intSize;
  protected $uid;
  protected $msgno;
  protected $blRecent;
  protected $blFlagged;
  protected $blAnswered;
  protected $blDeleted;
  protected $blSeen;
  protected $blDraft;  
  

  /**
   * CC Receiver(s) of the message
   *
   * @var string
   */
  protected $cc;
  
  /**
   * BCC Receiver(s) of the message
   *
   * @var string
   */
  protected $bcc;  
  

  /**
   * The structured part of the message 
   *
   * @var unknown_type
   */
  protected $structure;
  
  /**
   * An cached array of attachements
   *
   * @var array
   */  
  protected $attachements;
  
  /**
   * Constructor creating a new DSImapMessage object
   *
   * @param resource $stream  the stream with which the message is retreived (for lazy loading of body and attachements
   * @param string $subject   the messages subject
   * @param string $from      who sent the message
   * @param string $to        recipient
   * @param string $date      when the message was sent
   * @param string $messageId Message-ID
   * @param string $reference is a reference to this message id
   * @param string $replyTo   
   * @param int $intSize      size in bytes
   * @param string $uid       UID the message has in the mailbox
   * @param string $msgno     message sequence number in the mailbox 
   * @param bool $blRecent
   * @param bool $blFlagged
   * @param bool $blAnswered
   * @param bool $blDeleted
   * @param bool $blSeen
   * @param bool $blDraft
   */
  public function __construct($stream,
                              $subject,
                              $from,
                              $to,
                              $date,
                              $messageId,
                              $reference,
                              $replyTo,
                              $intSize,
                              $uid,
                              $msgno,
                              $blRecent = false,
                              $blFlagged = false,
                              $blAnswered = false,
                              $blDeleted = false,
                              $blSeen = false,
                              $blDraft = false)
  {
    $this->stream = $stream;
    
    $this->subject   = $subject;
    $this->from      = $from;
    $this->to        = $to;
    $this->date      = $date;
    $this->messageId = $messageId;
    $this->reference = $reference;
    $this->replyTo   = $replyTo;
    $this->intSize      = $intSize;
    $this->uid       = $uid;
    $this->msgno     = $msgno;
    $this->blRecent     = $blRecent;
    $this->blFlagged    = $blFlagged;
    $this->blAnswered   = $blAnswered;
    $this->blDeleted    = $blDeleted;
    $this->blSeen       = $blSeen;
    $this->blDraft      = $blDraft;
  }

  /**
   * returns the (lazy-loaded) structure of this message 
   *
   * @return object
   */
  protected function getStructure() 
  {
    if (!isset($this->structure))
    {
      $this->structure = imap_fetchstructure($this->stream, $this->uid, FT_UID);
    }
    
    return $this->structure;
  }
  
  protected function getPart($mimeType, $structure = false, $partNumber = false)
  {
    if (!$structure)
    {
      $structure = $this->getStructure();
    }

    if($structure) 
    {
      if($mimeType == self::getMimeType($structure)) 
      {
        if(!$partNumber) 
        {
          $partNumber = "1";
        }
        $text = imap_fetchbody($this->stream, $this->uid, $partNumber, FT_UID);
        
        if($structure->encoding == 3) 
        {
          return imap_base64($text);
        }
        else if($structure->encoding == 4) 
        {
          return imap_qprint($text);
        } 
        else 
        {
          return $text;
        }
      }
   
      // search recursively through multipart
      if($structure->type == 1) 
      {
        while(list($index, $subStructure) = each($structure->parts)) 
        {
          $prefix = '';
          if($partNumber) 
          {
            $prefix = $partNumber . '.';
          }
          $data = $this->getPart($mimeType, $subStructure, $prefix.($index + 1));
          if($data) 
          {
            return $data;
          }
        }
      }
    }
    
    return false;
  }

  public function getBodyHtmlElsePlain()
  {
    $body = $this->getBodyHtml();
    
    if (!$body)
    {
      $body = htmlentities($this->getBodyPlain());
    }
    
    return $body;
  }
  
  
  /**
   * Returns the body of this message (in plain)
   *
   * @return string
   */
  public function getBodyPlain()
  {
    return $body = $this->getPart(self::TYPE_PLAIN);
  }

  /**
   * Returns the body of this message (in html)
   *
   * @return string
   */
  public function getBodyHtml()
  {
    return $this->getPart(self::TYPE_HTML);
  }
  
  public function getAttachments()
  {
    if (!isset($this->attachements))
    {
      $this->attachements = array();
      
      $structure = $this->getStructure();
      $contentParts = 0;
      if (isset($structure->parts))
      {
        $contentParts = sizeof($structure->parts);
      }
   
      if ($contentParts > 1) 
      {
        for ($i=1; $i<$contentParts; $i++) 
        {
          $part = $structure->parts[$i];
          
          $filename = '';
          if (strtolower($part->parameters[0]->value) == "us-ascii") 
          {
            if ($part->parameters[1]->value != "") 
            {
              $filename = $part->parameters[1]->value;
            }
          }
          else if (strtolower($part->parameters[0]->value) != "iso-8859-1") 
          {
            $filename = $part->parameters[0]->value;
          }
          
          $mimeType = self::getMimeType($part);
          
          $binData = imap_fetchbody($this->stream, $this->uid, $i+1, FT_UID);
          if (strtolower(substr($mimeType, 0, 4)) == "text")
          {
            $binData = imap_qprint($binData);
          } 
          else 
          {
            $binData = imap_base64($binData);
          }
          
          $this->attachements[] = new sfDSImapAttachement($filename, $mimeType, $binData);
        }
      }
    }
    
    return $this->attachements;
  }
  
  public function getAttachementCount()
  {
    return count($this->getAttachments());
  }

  public function getSubject()
  {
    return $this->subject;
  }  
  
  public function getFrom()
  {
    return $this->from;
  }
  
  public function getTo()
  {
    return $this->to;
  }
  
  public function getDate()
  {
    return $this->date;
  }
 
  public function getUid()
  {
    return $this->uid;
  }
  
}
?>