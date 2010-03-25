<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 * Leon van der Ree <leon@fun4me.demon.nl> 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfGridFormatterHtml extends sfGridFormatterDynamic
{
  const FIRST = "|&laquo;";
  const PREV  = "&laquo;";
  const NEXT  = "&raquo;";
  const LAST  = "&raquo;|";

  const SORT_ASC  = 'sort_asc';
  const SORT_DESC = 'sort_desc';

  protected    
    $uri        = null,
    $sortable   = array(),
    $sortClass  = array(sfGrid::ASC  => self::SORT_ASC ,
                        sfGrid::DESC => self::SORT_DESC );

  static public function indent($code, $levels)
  {
    $lines = explode("\n", $code);
    foreach ($lines as &$line)
    {
      $line = str_repeat('  ', $levels) . $line;
    }
    return implode("\n", $lines);
  }
  
  /**
   * constructor of a Grid Formatter
   * 
   * @param sfGrid $grid
   */
  public function __construct(sfGrid $grid)
  {
    parent::__construct($grid, new sfGridFormatterHtmlRow($grid, 0));
  }

  /**
   * Sets the css classes used for the actively sorted column
   *
   * @param array $sortClass
   * @throws LogicException Throws an exception if no asc or desc have been defined
   */
  public function setSortClasses(array $sortClass)
  {
    if (!isset($sortClass['asc']) || !isset($sortClass['desc']))
    {
     throw new LogicException('When setting the sortClasses please specify both asc and desc');
    }

    $this->sortClass = $sortClass;
  }

  /**
   * Sets the description when the datasource contains no results
   *
   * @param string $noResultsMessage
   */
  public function setNoResultsMessage($noResultsMessage)
  {
    $this->row->setNoResultsMessage($noResultsMessage);
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
    $this->row->setRowHighlightCondition($column, $value, $class);
  }

  /**
   * Renders the table in HTML
   *
   * @return string
   */
  public function render()
  {
    return 
      $this->renderHead().
      $this->renderFoot().
      $this->renderBody();
  }

  /**
   * Renders the Head of the grid
   * 
   * @return string
   */  
  public function renderHead()
  {
    $html = "<thead>\n<tr>\n";

    foreach ($this->grid->getColumns() as $column)
    {
      $html .= "  " . $this->renderColumnHead($column) . "\n";
    }

    return $html . "</tr>\n</thead>\n";
  }

  /**
   * renders the pager for this grid
   * 
   * @return string
   */
  public function renderPager()
  {
    sfProjectConfiguration::getActive()->loadHelpers(array('Url'));

    $uri = $this->grid->getUri();
    if (empty($uri))
    {
      throw new LogicException('Please specify a URI with sfGrid::setUri() before rendering the pager');
    }
    $uriArgs = $this->grid->getUriArgs();

    $pager = $this->grid->getPager();
    $html = "<div class=\"paging\">\n";

    if ($pager->hasFirstPage())
    {
      $html .= "  <a href=\"" . url_for($uri. '?' . http_build_query(array_merge($uriArgs, array('page' => $pager->getFirstPage())) , '', '&')) . "\">".self::FIRST."</a>\n";
    }
    if ($pager->hasPreviousPage())
    {
      $html .= "  <a href=\"" . url_for($uri. '?' . http_build_query(array_merge($uriArgs, array('page' => $pager->getPreviousPage())), '', '&')) . "\">".self::PREV."</a>\n";
    }
    foreach ($pager as $page)
    {
      if ($page == $pager->getPage())
      {
        $html .= "  " . $page . "\n";
      }
      else
      {
        $html .= "  <a href=\"" . url_for($uri. '?' . http_build_query(array_merge($uriArgs, array('page' => $page)), '', '&')) . "\">" . $page . "</a>\n";
      }
    }
    if ($pager->hasNextPage())
    {
      $html .= "  <a href=\"" . url_for($uri. '?' . http_build_query(array_merge($uriArgs, array('page' => $pager->getNextPage())), '', '&')) . "\">".self::NEXT."</a>\n";
    }
    if ($pager->hasLastPage())
    {
      $html .= "  <a href=\"" . url_for($uri. '?' . http_build_query(array_merge($uriArgs, array('page' => $pager->getLastPage())), '', '&')) . "\">".self::LAST."</a>\n";
    }

    return $html . "</div>\n";
  }

  /**
   * Renders the Footer of the grid
   * 
   * @return string
   */
  public function renderFoot()
  {
    $pager = $this->grid->getPager();
    $html = $pager->hasToPaginate() ? "\n".self::indent($this->renderPager(), 2) : '';

    $html = "<tfoot>\n<tr>\n";
    $html .= "  <th colspan=\"".count($this->grid->getColumns())."\">";
    if ($pager->hasToPaginate())
    {
      $html .= "\n".self::indent($this->renderPager(), 2);
    }
    $html .= "\n    ".$pager->getRecordCount()." results";
    if ($pager->hasToPaginate())
    {
      $html .= " (page ".$pager->getPage()." of ".$pager->getPageCount().")";
    }


    return $html."\n  </th>\n</tr>\n</tfoot>\n";
  }
  
  /**
   * Renders the Body of the grid
   * 
   * @return string
   */
  public function renderBody()
  {
    $html = "<tbody>\n";

    if (count($this) != 0)
    {
      foreach ($this as $row)
      {
        $html .= $row->render();
      }
    } 
    else
    {
      $html .= $this->row->noRows();
    }

    return $html . "</tbody>\n";
  }

  /**
   * Renders the ColumnHead for the grid
   *
   * @param string $column
   * @return string html formatted string
   */
  public function renderColumnHead($column)
  {
    sfProjectConfiguration::getActive()->loadHelpers(array('Url', 'Tag'));

    $html = $this->grid->getTitleForColumn($column);
    $arrOptions = $this->grid->getOptionsTitleForColumn($column);

    if (in_array($column, $this->grid->getSortable()))
    {
      $uri = $this->grid->getUri();
      if (empty($uri))
      {
        throw new LogicException('Please specify a URI with sfGrid::setUri() before rendering the pager');
      }
      $uriArgs = $this->grid->getUriArgs();

      if ($this->grid->getSortColumn() == $column)
      {
        if (isset($arrOptions['class']))
        {
          $arrOptions['class'] .= ' '. $this->sortClass[$this->grid->getSortOrder()];
        }
        else
        {
          $arrOptions['class'] = $this->sortClass[$this->grid->getSortOrder()];
        }
      }

      $nextOrder = $this->grid->getSortColumn() == $column
         ? ($this->grid->getSortOrder() == sfGrid::ASC ? 'desc' : 'asc')
         : 'asc';

      // build the HTML with a class attribute sort_asc or sort_desc, if the
      // column is currently being sorted
      $html = sprintf("<a href=\"%s\">%s</a>",
                      url_for($uri. '?' . http_build_query(array_merge($uriArgs, 
                                                                       array('sort' => $column,
                                                                             'type' => $nextOrder)),
                                                                       '',
                                                                       '&')),
                              $html);
    }

    return tag('th', $arrOptions, true).$html.'</th>';
  }


}

