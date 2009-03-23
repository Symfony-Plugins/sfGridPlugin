<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfGridFormatterHtml implements sfGridFormatterInterface
{
  const FIRST = "|&laquo;";
  const PREV  = "&laquo;";
  const NEXT  = "&raquo;";
  const LAST  = "&raquo;|";

  const SORT_ASC  = 'sort_asc';
  const SORT_DESC = 'sort_desc';

  /**
   * @var sfGrid
   */
  protected $grid = null;

  protected
    $row        = null,
    $cursor     = 0,
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

  public function __construct(sfGrid $grid)
  {
    $this->grid = $grid;

    $this->row = new sfGridFormatterHtmlRow($grid, 0);
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

  public function render()
  {
    return $this->renderHead().$this->renderFoot().$this->renderBody();
  }

  public function renderHead()
  {
    $html = "<thead>\n<tr>\n";

    foreach ($this->grid->getColumns() as $column)
    {
      $html .= "  " . $this->renderColumnHead($column) . "\n";
    }

    return $html . "</tr>\n</thead>\n";
  }

  public function renderPager()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));

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

  public function renderBody()
  {
    $html = "<tbody>\n";

    foreach ($this as $row)
    {
      $html .= $row->render();
    }

    return $html . "</tbody>\n";
  }

  /**
   * Enter description here...
   *
   * @param string $column
   * @return string html formatted string
   */
  public function renderColumnHead($column)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url', 'Tag'));

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

  public function current()
  {
    $this->row->initialize($this->grid, $this->cursor);

    return $this->row;
  }

  public function next()
  {
    ++$this->cursor;
  }

  public function key()
  {
    return $this->cursor;
  }

  public function rewind()
  {
    $this->cursor = 0;
  }

  public function valid()
  {
    return $this->cursor < count($this);
  }

  public function count()
  {
    return count($this->grid);
  }

//  TODO: this can be removed?! using the url_for method from symfony...
//  static public function makeUri($uri, array $params)
//  {
//    // split the uri
//    $uri = explode('?', $uri);
//
//    // extract the query string
//    $values = array();
//    if (count($uri) > 1)
//    {
//      $query = explode('#', $uri[1]);
//      parse_str($query[0], $values);
//    }
//    $params = array_merge($values, $params);
//
//    // build the new uri
////    return $uri[0] . '?' . http_build_query($params, '', '&');
//    return url_for($module_action . '?' . http_build_query($params, '', '&'));
//  }
}

