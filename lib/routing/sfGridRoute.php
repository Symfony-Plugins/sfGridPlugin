<?php
/**
 * sfGridRoute represents a route that is bound to a Grid-class.
 *
 * A grid route can only represent a single Grid object.
 *
 * @package    symfony
 * @subpackage routing
 * @author     Leon van der Ree
 * @version    SVN: $Id:  $
 */
class sfGridRoute extends sfObjectRoute
{
  public function __construct($pattern, array $defaults = array(), array $requirements = array(), array $options = array())
  {
    $options['type'] = 'object';
    
    parent::__construct($pattern, $defaults, $requirements, $options);
  }
  
  protected function getObjectForParameters($parameters)
  {
    $className = $this->options['model'];
    $grid = new $className();
    
    if (!$grid instanceof sfContextGrid)
    {
      throw new InvalidArgumentException('The model should extend sfContextGrid'); 
    }
    
    if (isset($parameters['page']))
    {
      $grid->setPage($parameters['page']);
    }
    
    if (isset($parameters['sort']))
    {
      $sort = $parameters['sort'];
      $type = isset($parameters['type']) ? $parameters['type'] : null;
       
      $grid->setSort($sort, $type);
    }
        
    return $grid; 
  }
}
