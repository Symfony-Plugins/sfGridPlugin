<?php

/**
 * sfGridJavaScript Actions.
 *
 * @package    symfony
 * @subpackage sfGridPlugin
 * @author     Leon van der Ree
 * @version    SVN: $Id:  $
 */
class sfGridJavaScriptActions extends sfActions
{
  
  /**
   * @see sfActions
   */
  public function execute($request)
  {
    $this->grid = $this->getRoute()->getObject(); // TODO: getGrid()

    $response = $this->getResponse();
    if ($request->getRequestFormat() == 'json')
    {
      sfConfig::set('sf_web_debug', false);
      $response->setContentType('application/json');
      
      return $this->renderText($this->grid->renderData());
    }
    elseif ($request->getRequestFormat() == 'js')
    {
      sfConfig::set('sf_web_debug', false);
      $response->setContentType('text/javascript');
      return $this->renderText($this->grid->renderJavaScript());
    }
    
    // when html version requested: 
    
    // and include javascript as well of course
    $response->addJavascript($this->getController()->genUrl($this->grid->getUri()."?sf_format=js"), 'last');
    
    return parent::execute($request);
  }
}
