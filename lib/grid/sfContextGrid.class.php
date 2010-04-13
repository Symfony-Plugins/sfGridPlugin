<?php

/*
 * This file is part of the symfony package.
 * (c) Leon van der Ree <leon@fun4me.demon.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfContextGrid extends sfGrid
{
  /**
   * the namespace addition for this grid, 
   * to store attributes like: sort, type and page in the context
   *
   * @var string
   */
  protected $namespaceAddition = '';
  
  /**
   * 
   * @var sfContext
   */
  protected $context;
  
  /**
   * Adds context awareness to the grid, to store attributes like: sort, type and page.
   *
   * @see sfGrid::__construct
   */
  public function __construct($source)
  {
    $this->context = sfContext::getInstance();
    
    parent::__construct($source);
  }

  /**
   * configures the context awareness
   * 
   *  @see sfGrid::configure
   */
  public function configure()
  { 
    parent::configure();
    
    $user = $this->context->getUser();
    
    $this->setUri('@'.$this->context->getRouting()->getCurrentRouteName());
    
    $page = $user->getAttribute('page', null, $this->getNamespace());
    if ($page != null)
    {
      $this->getPager()->setPage($page);
    }
    
    $sort = $user->getAttribute('sort', null, $this->getNamespace());
    if ($sort != null)
    {
      $type = $user->getAttribute('type', null, $this->getNamespace());
      $this->setSort($sort, $type);
    }
  }
  
  /**
   * adds context-awareness to the grid-page functionality
   * 
   * @see sfGrid::setPage
   */
  public function setPage($page)
  {
    parent::setPage($page);

    $user = $this->context->getUser();
    
    $user->setAttribute('page', $page, $this->getNamespace());
  }
  
  /**
   * adds context-awareness to the grid-sort functionality
   * 
   * @see sfGrid::setSort
   */
  public function setSort($column, $order = null)
  {
    parent::setSort($column, $order);
        
    $user = $this->context->getUser();
    
    $user->setAttribute('sort', $column, $this->getNamespace());
    $user->setAttribute('type', $order, $this->getNamespace());
  }
  
  /**
   * Get the complete namespace for this Grid, including the addition if set
   * by default the namespace equals the Uri, 
   * the uri is by default modulename/actionname 
   *
   * @return string
   */
  public function getNamespace()
  {
    return $this->getUri().$this->namespaceAddition;
  }
  
  /**
   * Add an addition at the end of the namespace, in case you have 
   * a couple of grids on one page and want to separate their interaction
   * or want to reuse the same grid on several pages for different usage.
   *
   * @param string $namespaceAddition  the addition to the namespace
   */
  public function setNamespaceAddition($namespaceAddition)
  {
    $this->namespaceAddition = '/'.$namespaceAddition;
  }
  
}