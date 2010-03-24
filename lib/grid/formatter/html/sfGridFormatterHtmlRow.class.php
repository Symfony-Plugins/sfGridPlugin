<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 * Leon van der Ree <leon@fun4me.demon.nl>
 *
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A formatter that renders the HTML of a row
 *
 */
class sfGridFormatterHtmlRow extends sfGridFormatterDynamicRow
{
  const NO_RESULTS_MESSAGE = 'no results';
  
  protected $noResultsMessage;

  protected $highlightCondition = array();

  /**
   * Constructs a new sfGridFormatterHtmlRow to render html-rows (tr/td's)
   *
   * @param sfGrid $grid  the grid that this row-formatter should render
   * @param int $index    The index to which to set the internal row pointer
   * @param string $noResultsMessage  The message to show when there are no results in the datasource
   */
  public function __construct(sfGrid $grid, $index, $noResultsMessage = self::NO_RESULTS_MESSAGE)
  {
    $this->initialize($grid, $index, $noResultsMessage);
  }

  /**
   * Initialises the new sfGridFormatterHtmlRow 
   *
   * @param sfGrid $grid  the grid that this row-formatter should render
   * @param int $index    The index to which to set the internal row pointer
   * @param string $noResultsMessage  The message to show when there are no results in the datasource
   */  
  public function initialize(sfGrid $grid, $index, $noResultsMessage = self::NO_RESULTS_MESSAGE)
  {
    parent::initialize($grid, $index);
    
    $this->noResultsMessage = $noResultsMessage; 
  }

  /**
   * Returns the associated grid
   *
   * @return sfGrid
   */
  public function getGrid()
  {
    return $this->grid;
  }

  /**
   * Renders a row to html
   *
   * @return string
   */
  public function render()
  {
    $source = $this->grid->getDataSource();
    $source->seek($this->index);

    $css = '';
    if (isset($this->highlightCondition['column']))
    {
      if ($source[$this->highlightCondition['column']] === $this->highlightCondition['value'])
      {
        $css = ' class="'.$this->highlightCondition['class'].'"';
      }
    }

    $data = "<tr".$css.">\n";
    foreach ($this->grid->getWidgets() as $column => $widget)
    {
      // First render the body. Possible that the tdCss is changed by it.
      $tagBody = $widget->render($column, $source[$column]);
      
      // Check the css options.
      $arrOptions = array();      
      if ($widget->getOption('tdCss'))
      {
        $arrOptions['class'] = $widget->getOption('tdCss');
        $widget->setOption('tdCss', null); // reset for next row
      }
      if ($widget->getOption('tdTitle'))
      {
        $arrOptions['title'] = $widget->getOption('tdTitle');
        $widget->setOption('tdTitle', null); // reset for next row
      }      
      $data .= '  '.$widget->renderContentTag('td',
                                         $tagBody,
                                         $arrOptions)
              ."\n";
    }

    return $data . "</tr>\n";
  }

  /**
   * renders the html when no data is in the datasource
   *
   * @return string
   */
  public function noRows()
  {
    $colspan = count($this->grid->getWidgets());
    
    $data = "<tr>\n";
    $data .= "<td colspan=\"".$colspan."\">".$this->getNoResultsMessage()."</td>";
    
    return $data . "</tr>\n";
  }
  
  /**
   * Returns the description if no results are in the datasource
   *
   * @return string
   */
  public function getNoResultsMessage()
  {
    return $this->noResultsMessage;
  }
  
  /**
   * Sets the description when the datasource contains no results
   *
   * @param string $noResultsMessage
   */
  public function setNoResultsMessage($noResultsMessage)
  {
    $this->noResultsMessage = $noResultsMessage;
  }  
  
  /**
   * Sets the condition to add a css-class to a row
   *
   * @param string $column  the column name
   * @param mixed  $value   the value the column should be equal to
   * @param string $class   the css-class the row should get
   */
  public function setRowHighlightCondition($column, $value = true, $class='active')
  {
    $this->highlightCondition = array(
      'column' => $column,
      'value'  => $value,
      'class'  => $class,
    );
  }
}