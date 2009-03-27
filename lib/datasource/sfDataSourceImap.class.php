<?php

/*
 * This file is part of the symfony package.
 * (c) Leon van der Ree <Leon@fun4me.demon.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This class implements the interface sfDataSourceInterface for accessing
 * messages stored on an imap-server.
 *
 * 
 * <code>
 * $username = 'user';
 * $password = 'pass';
 * $host = '127.0.0.1';
 * $port = 143;
 * $mailboxName = 'Inbox;
 * $options = array('notls');
 * $imapMessages = new sfDataSourceImap($username, $password, $host, $port, $mailboxName, $options);
 * 
 * foreach ($imapMessages as $message)
 * {
 *   echo $message->getSubject();
 * }
 * </code>
 *
 *
 * @package    symfony
 * @subpackage grid
 * @author     Leon van der Ree <Leon@fun4me.demon.nl>
 * @version    SVN: $Id$
 */
class sfDataSourceImap extends sfDataSource
{
  /**
   * an array that holds (a page of) the messages retreived 
   * from the imap connection
   *
   * @var array[sfDSImapMessage]
   */
  protected
    $messages;

  /**
   * The sort properties
   *
   * @var string
   */
  protected 
    $sortColumn = null,
    $sortOrder = null;
  
  /**
   * An array with filter properties
   *
   * @see sfDataSourceInterface::setFilter()
   * 
   * @var array
   */
  protected $filters;
  
  /**
   * The stream to the imap-server
   *
   * @var resource
   */
  protected $stream;
  
  /**
   * Connection properties
   *
   * @var string/int
   */
  protected
    $username,
    $password,
    $host,
    $port,
    $mailboxName,
    $options;

  /**
   * hydrates an sfDSImapMessage
   *
   * @param object $header
   * @param resource $stream
   * @return sfDSImapMessage
   */
  public static function hydrate($header, $stream)
  {
    $message = new sfDSImapMessage(
      $stream,
      
      isset($header->subject) ? self::mime_header_to_text($header->subject) : null,
      self::mime_header_to_text($header->from),
      isset($header->to) ? self::mime_header_to_text($header->to) : null, 
      self::mime_header_to_text($header->date),
      self::mime_header_to_text($header->message_id),
      isset($header->references) ? self::mime_header_to_text($header->references) : null,
      isset($header->in_reply_to) ? self::mime_header_to_text($header->in_reply_to) : null,
      $header->size,
      $header->uid,
      $header->msgno,
      $header->recent,
      $header->flagged,
      $header->answered,
      $header->deleted,
      $header->seen,
      $header->draft
    );
    
    return $message;
  }
  
  /**
   * Decodes MIME message header extensions that are non ASCII text (see » RFC2047). 
   * 
   * @param string $text
   * @return string
   */
  protected static function mime_header_to_text($text)
  {
    $elements = imap_mime_header_decode($text);
    
    $returnText = "";
    for($i = 0; $i < count($elements); $i++)
    {
      $returnText .= $elements[$i]->text . " ";
    }
    
    return trim($returnText);
  }  
    
  /**
   * Constructor.
   *
   * @param string  $username    the username
   * @param string  $password    the password
   * @param string  $host        the hostname
   * @param integer $port        the port
   * @param string  $mailboxName the mailbox-name 
   * @param array   $options     an array with options
   *   
   * @throws InvalidArgumentException  Throws an exception if the given array
   *                                   is not formatted correctly
   */
  public function __construct($username, $password, $host, $port, $mailboxName, array $options)
  {
    if(!extension_loaded('imap')) throw new Exception('IMAP extension needed for this class');
    
    $this->username    = $username;
    $this->password    = $password;
    $this->host        = $host;
    $this->port        = $port;
    $this->mailboxName = $mailboxName;    
    $this->options     = $options;
  }

  /**
   * Makes the connection to the imap-server for the specified user
   *
   * @throws Exception  Throws an exception when the connection was not made succesfully
   */
  protected function connect()
  {
    $options = "";
    if(!empty($this->options) && is_array($this->options))
    {
      foreach ($this->options as $option)
      {
        $options .= "/" . $option;
      }
    }

    $adress = '{'.$this->host.':'.$this->port.$options.'}'.$this->mailboxName;
    
    $time = time();
    // IF THIS IS SLOW, PLEASE MAKE SURE rDNS IS ENALBED ON YOUR SYSTEM 
    // (one solution is to place the ip of your mail-server in your /etc/hosts file) 
    $this->stream = imap_open($adress, $this->username, $this->password, OP_DEBUG, 0);
    $time = (time()-$time);
    if ($time >= 4)
    {
      sfContext::getInstance()->getLogger()->notice(
                    'Imap-login is slow! 
                     Please make user rDNS is enabled on your system. 
                     Tip you can add your mail-server ip to your hosts-file.');
    }
        
    if(!$this->stream)
    {
      throw new Exception('unable to connect user "'.$this->username.'" to imap server: '.$adress);
    }
  }
  
  /**
   * Closes the imap Connection
   *
   */
  protected function closeConnection()
  {
    imap_close($this->stream);
  }
  
  /**
   * changes the mailbox
   *
   * @param string $mailBoxName
   */
  public function changeMailbox($mailBoxName)
  {
    $this->mailboxName = $mailBoxName;

    // change to this mailbox immdediately, if there already was a connection
    if($this->stream)
    {
      $success = imap_reopen($this->stream, $this->mailboxName);
      
      if (!$success)
      {
        throw new Exception('Could not change to mailbox: '.$this->mailboxName);
      }
    }
  }
  
  /**
   * loads an array of hydrated (sfDSImapMessage) messages
   *
   */
  protected function loadMessages()
  {
    // if not yet loaded, get messages
    if (!isset($this->messages))
    {
      if(!$this->stream)
      {
        $this->connect();
      }
      
      $this->messages = array();
        
      // test if sorting asc or descending
      $reverse = ($this->sortOrder == self::DESC) ? 1 : 0; 
  
      // get translation for sorting
      switch (strtolower($this->sortColumn))
      {
        case 'date':
  //        $msgNrs = imap_sort($this->stream, SORTDATE, $reverse);
          $msgNrs = imap_sort($this->stream, SORTARRIVAL, $reverse, SE_UID, $this->getFilterCriteria()); // or simply on arrivalID
          break;
        case 'from':
          $msgNrs = imap_sort($this->stream, SORTFROM, $reverse, SE_UID, $this->getFilterCriteria());
          break;
        case 'to':
          $msgNrs = imap_sort($this->stream, SORTTO, $reverse, SE_UID, $this->getFilterCriteria());
          break;
        case 'cc':
          $msgNrs = imap_sort($this->stream, SORTCC, $reverse, SE_UID, $this->getFilterCriteria());
          break;        
        case 'subject':
          $msgNrs = imap_sort($this->stream, SORTSUBJECT, $reverse, SE_UID, $this->getFilterCriteria());
          break;
        case 'size':
          $msgNrs = imap_sort($this->stream, SORTSIZE, $reverse, SE_UID, $this->getFilterCriteria());
          break;
        default:
          $filterCriteria = ($this->getFilterCriteria() != null) ? $this->getFilterCriteria() : 'ALL';
          $msgNrs = imap_search($this->stream, $filterCriteria);
          if ($this->sortOrder == self::DESC)
          {
            $msgNrs = array_reverse($msgNrs);
          }
          break;
      }
      
// the alternatives:
//      $header = imap_headerinfo($this->stream, $offset);
//      return retrieve_message($this->stream, $offset))

      $msgNrs = array_slice($msgNrs, $this->getOffset(), $this->getLimit());
      $sequence = implode(',', $msgNrs);
      $headers = imap_fetch_overview($this->stream, $sequence, FT_UID);
      
      foreach ($headers as $header)
      {
        $location = array_search($header->uid, $msgNrs); 
        $this->messages[$location] = self::hydrate($header, $this->stream);
      }

      // sort to correct indexes.
      ksort($this->messages);
    }
  }  
    
  /**
   * Returns the current row while iterating. If the internal row pointer does
   * not point at a valid row, an exception is thrown.
   *
   * @return array                 The current row data
   * @throws OutOfBoundsException  Throws an exception if the internal row
   *                               pointer does not point at a valid row.
   */
  public function current()
  {
    // load mails
    $this->loadMessages();
    
    if (!$this->valid())
    {
      throw new OutOfBoundsException(sprintf('The result with index %s does not exist', $this->key()));
    }

    return $this->messages[$this->key()];
  }

  /**
   * Returns the value of the given column in the current row returned by current()
   *
   * @param  string $column The name of the column
   * @return mixed          The value in the given column of the current row
   */
  public function offsetGet($column)
  {
    $current = $this->current();
    
    return call_user_func(array($current, 'get'.$column));
  }

  /**
   * Returns the number of records in the data source. If a limit is set with
   * setLimit(), the maximum return value is that limit. You can use the method
   * countAll() to count the total number of rows regardless of the limit.
   *
   * <code>
   * $source = new sfDataSourceImap(...TODO: );
   * echo $source->count();    // returns "100"
   * $source->setLimit(20);
   * echo $source->count();    // returns "20"
   * </code>
   *
   * @return integer The number of messages for this connection
   */
  public function count()
  {
    $all   = $this->countAll();
    $count = $all - $this->getOffset();

    return $this->getLimit()==0 ? $count : min($this->getLimit(), $count);
  }

  /**
   * @see sfDataSourceInterface::countAll()
   */
  public function countAll()
  {
    if(!$this->stream)
    {
      $this->connect();
    }
    
    $filterCriteria = $this->getFilterCriteria();
    if ($filterCriteria != null)
    {
      $nrMessages = count(imap_search($this->stream, $filterCriteria));
    }
    else
    {
      $nrMessages = imap_num_msg($this->stream);  
    }
    
    return $nrMessages;
  }
  
  /**
   * Translates the array of filter properties to a imap-criteria
   *
   * @return string
   */
  protected function getFilterCriteria()
  {
    if (count($this->filters) == 0)
    {
      return null;
    }
    
    $criteria = '';
    foreach ($this->filters as $filter => $options)
    {
      $criteria .= strtoupper($filter).' ';
      if ($options['value'] != '')
      {
        $criteria .= '"'.$options['value'].'" ';
      }
    }
    
    return $criteria;
  }

  /**
   * @see sfDataSourceInterface::requireColumn()
   */
  public function requireColumn($column)
  {
    // TODO: allow getting complete message, including body and attachements,
    // currently only header is retreived
  }

  /**
   * @see sfDataSource::doSort()
   */
  protected function doSort($column, $order)
  {
    $this->sortColumn = $column;
    $this->sortOrder = $order;
  }
  
  /**
   * @see sfDataSourceInterface
   */
  public function setFilter($fields)
  {
    $this->filters = $fields;
  }
    
}