<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfWebGrid extends sfContextGrid
{
  /**
   * Bind the webRequest to the Grid, to make it automatically parse all parameters
   * 
   * @param sfWebRequest $request
   */
  public function bind(sfWebRequest $request)
  {
    $moduleName = $this->context->getModuleName();
    $actionName = $this->context->getActionName();
    
    $uri = $moduleName.'/'.$actionName;
    $this->setUri($uri);
    
    $page = $request->getParameter('page');
    if ($page != null)
    {
      $this->setPage($page);
    }
    
    $sort = $request->getParameter('sort');
    if ($sort != null)
    {
      
      $type = $request->getParameter('type');
      if ($type != null)
      {
        $this->setSort($sort, $type);
      }
      else
      {
        $this->setSort($sort);
      }
    }
  }
  
}