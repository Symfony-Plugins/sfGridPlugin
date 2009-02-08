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
  public function bind(sfWebRequest $request)
  {
    $this->setUri($request->getUri());
    
    $parameters = $request->getGetParameters();
    
    if (array_key_exists('page', $parameters))
    {
      $this->getPager()->setPage($parameters['page']);
    }
    if (array_key_exists('sort', $parameters))
    {
      if (array_key_exists('sort_order', $parameters))
      {
        $this->setSort($parameters['sort'], $parameters['sort_order']);
      }
      else
      {
        $this->setSort($parameters['sort']);
      }
    }
  }
}