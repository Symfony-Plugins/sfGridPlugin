<?php

/*
 * This file is part of the symfony package.
 * (c) Leon van der Ree <leon@fun4me.demon.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This abstract class is there as a base for your JavaScript-Grid class.
 * You should define the JavaScript formatter in the specialised class
 * 
 * All JavaScript Grids "should" still render to HTML, while unobtrusive JavaScript should progressive enhance the intactivity of your grid,
 * To dynamically load new content a dataFormatter can be defined to output JSON or XML-data.  
 * 
 */
abstract class sfContextGridJavaScript extends sfContextGrid
{
  /**
   * formatter to output data (json/xml)
   * 
   * @var sfGridFormatterInterface
   */
  protected $dataFormatter;

  /**
   * formatter that returns (unobtrusive) JavaScript
   * 
   * @var sfGridFormatterInterface
   */
  protected $javaScriptFormatter;
  
  /**
   * This method should be implemented in your specialised GridClass, to define
   * the dataFormatter and javaScriptFormatter
   */
  public function configure()
  {
    //set html formatter
    parent::configure();
    
    // define the data formatter
    $this->setDataFormatter(new sfGridFormatterJson($this));
    
    // $this->setJavaScriptFormatter(new sfGridFormatterYOUR_JS_FORMATTER($this));
  }
  
  /**
   * returns the DataFormatter (to format data in json/xml) 
   * 
   * @return sfGridFormatterInterface
   */
  public function getDataFormatter()
  {
    return $this->dataFormatter;
  }

  /**
   * Sets the data formatter that should be used to render the data of the grid, E.G. in json or xml.
   *
   * @param  sfGridFormatterInterface $formatter A Data Formatter
   */
  public function setDataFormatter(sfGridFormatterInterface $formatter)
  {
    $this->dataFormatter = $formatter;
  }
  
    public function renderData()
  {
    // set default sort-column, if set
    if (!$this->getSortColumn() && $this->defaultSortColumn)
    {
      $this->setSort($this->defaultSortColumn, $this->defaultSortOrder);
    }

    // update offset lazy, now is a good time to request last page and check if we don't requested a higher pager
    $this->getDataSource()->setOffset($this->getPager()->getFirstIndex());

    if ($this->getDataFormatter() === null)
    {
      throw new LogicException('A Data formatter must be set before calling renderData()');
    }

    return $this->getDataFormatter()->render();
  }
  
  
  /**
   * returns the JavaScriptFormatter (to format (the structure) in (unobstrusive) JavaScript)
   * 
   * @return sfGridFormatterInterface
   */
  public function getJavaScriptFormatter()
  {
    return $this->javaScriptFormatter;
  }

  /**
   * Sets the JavaScript formatter that should be used to render the grid in JavaScript.
   *
   * @param  sfGridFormatterInterface $formatter A JavaScriptFormatter
   */
  public function setJavaScriptFormatter(sfGridFormatterInterface $formatter)
  {
    $this->javaScriptFormatter = $formatter;
  }
  
  
  /**
   * Renders static JavaScript (not based on rows)
   * 
   * return string
   */
  public function renderJavaScript()
  {
    if ($this->getJavaScriptFormatter() === null)
    {
      throw new LogicException('A JavaScript formatter must be set before calling renderJavaScript()');
    }

    return $this->getJavaScriptFormatter()->render();
  }
}