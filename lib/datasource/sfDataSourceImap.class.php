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
 * $source = new sfDataSourceImap(TODO);
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
  protected
    $data = array();

  protected 
    $sortColumn = null,
    $sortOrder = null;
    
  protected $filters;
    
  protected $stream;
  
  protected
    $username,
    $password,
    $host,
    $port,
    $mailboxName,
    $options;

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
  
  public function getMails($uids = false)
  {
    if(!$this->stream)
    {
      $this->connect();
    }
    
    $num_mgs = imap_num_msg($this->stream);
    $counter = 1;
    while($counter <= $num_mgs)
    {
      if($message = $this->retrieve_message($this->stream, $counter, $uids))
      {
        $berichten [$counter - 1] = $message;

      }
      $counter++;
    }
    return $berichten;
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
    if(!$this->stream)
    {
      $this->connect();
    }

    if (!$this->valid())
    {
      throw new OutOfBoundsException(sprintf('The result with index %s does not exist', $this->key()));
    }

    // test if sorting asc or descending
    $reverse = ($this->sortOrder == self::DESC) ? 1 : 0; 

    $offset = $this->getOffset() + $this->key();
    switch ($this->sortColumn)
    {
      case 'date':
//        $msgNrs = imap_sort($this->stream, SORTDATE, $reverse);
        $msgNrs = imap_sort($this->stream, SORTARRIVAL, $reverse); // or simply on arrivalID
        $offset = $msgNrs[$offset];
        break;
      case 'fromaddress':
        $msgNrs = imap_sort($this->stream, SORTFROM, $reverse);
        $offset = $msgNrs[$offset];
        break;
      case 'toaddress':
        $msgNrs = imap_sort($this->stream, SORTTO, $reverse);
        $offset = $msgNrs[$offset];
        break;
      case 'ccaddress':
        $msgNrs = imap_sort($this->stream, SORTCC, $reverse);
        $offset = $msgNrs[$offset];
        break;        
      case 'subject':
        $msgNrs = imap_sort($this->stream, SORTSUBJECT, $reverse);
        $offset = $msgNrs[$offset];
        break;
      case 'size':
        $msgNrs = imap_sort($this->stream, SORTSIZE, $reverse);
        $offset = $msgNrs[$offset];
        break;
      default:
        $offset += 1;
        break;
    }
    
    // TODO: hydrate message (maybe including attachement, if required)
    return imap_header($this->stream, $offset);
  }
  
  /**
   * TODO: explain
   *
   * @param string $text
   * @return string
   */
  protected static function mine_header_to_text($text)
  {
    $elements = imap_mime_header_decode($text);
    $returnText = "";
    for($i = 0; $i < count($elements); $i++)
    {
      $returnText .= $elements [$i]->text . " ";
    }
    return trim($returnText);
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
    
    return self::mine_header_to_text($current->$column);
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
  
  protected function getFilterCriteria()
  {
    if (count($this->filters) == 0)
    {
      return null;
    }
    
    $criteria = '';
    foreach ($this->filters as $filter => $options)
    {
      $criteria .= $filter . ' "'.$options['value'].'" ';
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