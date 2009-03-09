<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfWebGrid extends sfGrid
{
  /**
   * the namespace addition for this grid, 
   * to store attributes like: sort, type and page in
   *
   * @var string
   */
  protected $namespaceAddition = '';
  
  /**
   * Bind the webRequest to the Grid, to make it automatically parse all parameters
   * 
   * @param sfWebRequest $request
   */
  public function bind(sfWebRequest $request)
  {
    $context = sfContext::getInstance();
    $user = $context->getUser();
    
    $moduleName = $context->getModuleName();
    $actionName = $context->getActionName();
    
    $uri = $moduleName.'/'.$actionName;
    $this->setUri($uri);
    
//    $parameters = $request->getRequestParameters();
    
    $page = $request->getParameter('page', $user->getAttribute('page', null, $this->getNamespace()));
    if ($page != null)
    {
      $this->getPager()->setPage($page);
      $user->setAttribute('page', $page, $this->getNamespace());
    }
    
    $sort = $request->getParameter('sort', $user->getAttribute('sort', null, $this->getNamespace()));
    if ($sort != null)
    {
      $user->setAttribute('sort', $sort, $this->getNamespace());
      
      $type = $request->getParameter('type', $user->getAttribute('type', null, $this->getNamespace()));
      if ($type != null)
      {
        $this->setSort($sort, $type);
        $user->setAttribute('type', $type, $this->getNamespace());
      }
      else
      {
        $this->setSort($sort);
      }
    }
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
   *
   * @param string $namespaceAddition  the addition to the namespace
   */
  public function setNamespaceAddition($namespaceAddition)
  {
    $this->namespaceAddition = '/'.$namespaceAddition;
  }
  
}