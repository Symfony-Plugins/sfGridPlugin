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
   * @var string
   */
  protected $date;
    
  /**
   * The message identifier
   *
   * @var string
   */
  protected $msgId;
  
  /**
   *
   * is a reference to this message id 
   * 
   * @var string
   */
  protected $reference;
  
  /**
   * is a reply to this message id 
   *
   * @var string
   */
  protected $replyTo;
  
  /**
   * size in bytes
   *
   * @var int
   */
  protected $size;
  
  /**
   * UID the message has in the mailbox 
   *
   * @var int
   */
  protected $uid;
  
  /**
   * message sequence number in the mailbox 
   *
   * @var int
   */
  protected $msgno;
  
  /**
   * this message is flagged as recent 
   *
   * @var bool
   */
  protected $recent;
  
  /**
   * this message is flagged 
   *
   * @var bool
   */
  protected $flagged;
  
  /**
   * this message is flagged as answered 
   *
   * @var bool
   */
  protected $answered;
  
  /**
   * this message is flagged for deletion 
   *
   * @var bool
   */
  protected $deleted;
  
  /**
   * this message is flagged as already read 
   *
   * @var bool
   */
  protected $seen;
  
  /**
   *  this message is flagged as being a draft 
   *
   * @var bool
   */
  protected $draft;
  



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
    
  
  //TODO:
  /**
   * CC Receiver(s) of the message
   *
   * @var string
   */
  protected $cc;
  
  //TODO:
  /**
   * BCC Receiver(s) of the message
   *
   * @var string
   */
  protected $bcc;  
  
  
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
   * @param int $size         size in bytes
   * @param int $uid          UID the message has in the mailbox
   * @param int $msgno        message sequence number in the mailbox 
   * @param bool $recent
   * @param bool $flagged
   * @param bool $answered
   * @param bool $deleted
   * @param bool $seen
   * @param bool $draft
   */
  public function __construct($stream,
                              $subject,
                              $from,
                              $to,
                              $date,
                              $messageId,
                              $reference,
                              $replyTo,
                              $size,
                              $uid,
                              $msgno,
                              $recent = false,
                              $flagged = false,
                              $answered = false,
                              $deleted = false,
                              $seen = false,
                              $draft = false)
  {
    $this->stream = $stream;
    
    $this->subject   = $subject;
    $this->from      = $from;
    $this->to        = $to;
    $this->date      = $date;
    $this->messageId = $messageId;
    $this->reference = $reference;
    $this->replyTo   = $replyTo;
    $this->size      = $size;
    $this->uid       = $uid;
    $this->msgno     = $msgno;
    $this->recent     = $recent;
    $this->flagged    = $flagged;
    $this->answered   = $answered;
    $this->deleted    = $deleted;
    $this->seen       = $seen;
    $this->draft      = $draft;
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
  
  /**
   * Gets an part of the body
   *
   * @param string $mimeType
   * @param object $structure
   * @param string $partNumber
   * @return string part of the body if match found, else false
   */
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

  /**
   * Returns the body in HTML, if only plain available this gets converted to HTML
   *
   * @return string
   */
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
  
  /**
   * returns an array of hydrated attachements for this message
   *
   * @return array[sfDSImapAttachement]
   */
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
  
  /**
   * the number of attachements for this message
   *
   * @return int the number of attachements for this message
   */
  public function getAttachmentCount()
  {
    return count($this->getAttachments());
  }

  /**
   * the messages subject 
   *
   * @return string
   */
  public function getSubject()
  {
    return $this->subject;
  }  
  
  /**
   * who sent it 
   *
   * @return string
   */
  public function getFrom()
  {
    return $this->from;
  }
  
  /**
   * recipient 
   *
   * @return string
   */
  public function getTo()
  {
    return $this->to;
  }
  
  /**
   * when was it sent 
   *
   * @return string
   */
  public function getDate()
  {
    return $this->date;
  }
 
  /**
   * Message-ID 
   *
   * @return string
   */
  public function getMsgId()
  {
    return $this->msgId;
  }
  
  /**
   * is a reference to this message id 
   *
   * @return string
   */
  public function getReference() 
  {
    return $this->reference;
  }
  
  /**
   * is a reply to this message id 
   *
   * @return string
   */
  public function getReplyTo() 
  {
    return $this->replyTo;
  }
  
  /**
   * size in bytes 
   *
   * @return int
   */
  public function getSize() 
  {
    return $this->size;
  }
  
  /**
   * UID the message has in the mailbox 
   *
   * @return int
   */
  public function getUid()
  {
    return $this->uid;
  }
  
  /**
   * message sequence number in the mailbox 
   *
   * @return int
   */
  public function getMsgno() 
  {
    return $this->msgno;
  }
  
  /**
   * this message is flagged as recent 
   *
   * @return bool
   */
  public function getRecent() 
  {
    return $this->recent;
  }
  
  /**
   * this message is flagged 
   *
   * @return bool
   */
  public function getFlagged() 
  {
    return $this->flagged;
  }
  
  /**
   * this message is flagged as answered 
   *
   * @return bool
   */
  public function getAnswered() 
  {
    return $this->answered;
  }
  
  /**
   * this message is flagged for deletion
   *
   * @return bool
   */
  public function getDeleted() 
  {
    return $this->deleted;
  }
  
  /**
   * this message is flagged as already read 
   *
   * @return bool
   */
  public function getSeen() 
  {
    return $this->seen;
  }
  
  /**
   * this message is flagged as being a draft 
   *
   * @return bool
   */
  public function getDraft() 
  {
    return $this->draft;
  }
  
}
?>