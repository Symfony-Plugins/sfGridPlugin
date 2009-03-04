<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This class represents the controller of the grid subframework. It
 * communicates with an instance of sfDataSourceInterface and an instance of
 * sfGridFormatterInterface. The data source contains the data that will
 * be rendered to a suitable string output by the formatter.
 *
 * You can directly create instances of sfGrid and configure them on the fly.
 * Usually you will want to create subclasses though which implement the method
 * configure(). This method is called automatically when the grid is
 * being constructed.
 *
 * The minimum information required to render a grid are the columns
 * displayed in the grid and the formatter used to render it. You can set
 * these properties using the methods setColumns() and setFormatter() or
 * setFormatterName().
 *
 * Example:
 *
 * <code>
 * $source = new sfDataSourceDoctrine('User');
 * $grid = new sfGrid($source);
 * $grid->setColumns(array('id', 'name'));
 * $grid->setFormatterName('html');
 *
 * echo $grid->render();
 * </code>
 *
 * @package    symfony
 * @subpackage grid
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 */
class sfGrid implements Countable
{
  const ASC  = 'asc';
  const DESC = 'desc';

  const ALL  = 0;

  private
    $columns      = array(),
    $columnTitles = array(),
    $sortable     = array(),
    $widgets      = array(),
    $formatter    = null,
    $pager        = null,
    $sortColumn   = null,
    $sortOrder    = null,
    $uri          = null;

  /**
   * Constructor.
   *
   * @param  mixed $source An array or an instance of sfDataSourceInterface with
   *                       data that should be displayed in the grid.
   *                       If an array is given, the array must conform to the
   *                       requirements of the constructor of sfDataSourceArray.
   */
  public function __construct($source)
  {
    // the given source must be either an array...
    if (is_array($source))
    {
      $source = new sfDataSourceArray($source);
    }
    // ...or an instance of sfDataSourceInterface
    if (!$source instanceof sfDataSourceInterface)
    {
      throw new InvalidArgumentException('The given data must either be an array or a class implementing sfDataSourceInterface');
    }

    $this->pager = new sfDataSourcePager($source);

    $this->configure();
  }

  /**
   * Returns the data source associated to this grid.
   *
   * @return sfDataSourceInterface A sfDataSourceInterface instance
   */
  public function getDataSource()
  {
    return $this->getPager()->getDataSource();
  }

  /**
   * Sets the columns that should be displayed in the grid.
   *
   * @param array $columns An array of column names that must match the column
   *                       names in the data source.
   */
  public function setColumns(array $columns)
  {
    foreach ($columns as $column)
    {
      if (!is_string($column))
      {
        throw new InvalidArgumentException('The column names must be strings');
      }
      $this->getDataSource()->requireColumn($column);
    }

    $this->columns = $columns;

    foreach ($columns as $column)
    {
      $this->setWidget($column, new sfWidgetText());
    }
  }

  /**
   * Returns the columns of this grid that have been set with setColumns().
   *
   * @return array An array of sfGridColumn instances.
   */
  public function getColumns()
  {
    return $this->columns;
  }

  /**
   * Returns whether the grid has a column with the given name.
   *
   * @param  string $column  The name of the column to check for
   * @return boolean         Whether the grid has a column with this name
   */
  public function hasColumn($column)
  {
    return in_array($column, $this->columns);
  }

  /**
   * Sets a list of titles that should be used for the given columns. The
   * titles should be given as associative array with the column
   * names as keys.
   *
   * <code>
   * $grid->setColumnTitles(array(
   *   'id'         => 'Key',
   *   'created_at' => 'Created At',
   * ));
   * </code>
   *
   * Columns for which you do not specify a widget will not be modified. Per
   * default all columns are rendered with sfWidgetText.
   *
   * @param array $columnTitles An associative array of column names and titles
   * @throws LogicException     Throws an exception if any of the given column
   *                            names has not been configured with setColumns()
   */
  public function setColumnTitles(array $columnTitles)
  {
    foreach ($columnTitles as $column => $title)
    {
      $this->setColumnTitle($column, $title);
    }
  }

  /**
   * Sets the title used to render above the given column.
   *
   * Per default all columns have a uppercase-first title of the column-name
   *
   * @param  string $column   The name of the column
   * @param  string $title    The title used to render above this column
   * @throws LogicException   Throws an exception if the given column
   *                          name has not been configured with setColumns()
   */
  public function setColumnTitle($column, $title)
  {
    if (!$this->hasColumn($column))
    {
      throw new LogicException(sprintf('The column "%s" has not been configured', $column));
    }

    $this->columnTitles[$column] = $title;
  }

  /**
   * Returns the title for a column
   *
   * @param string $column  the column name
   * @return string         the title for this column
   */
  public function getTitleForColumn($column)
  {
    if (!$this->hasColumn($column))
    {
      throw new LogicException(sprintf('The column "%s" is not defined for this grid', $column));
    }

    if (isset($this->columnTitles[$column]))
    {
      $title = $this->columnTitles[$column];
    }
    else
    {
      $title = ucFirst($column);
    }

    return $title;
  }

  /**
   * Sets the formatter that should be used to render the grid.
   *
   * @param  sfGridFormatterInterface $formatter An instance of sfGridFormatterInterface
   */
  public function setFormatter(sfGridFormatterInterface $formatter)
  {
    $this->formatter = $formatter;
  }

  /**
   * Sets the formatter by its name. For the name you pass to this method, a
   * corresponding class "sfGridFormatter%Name%" must exist, where %Name%
   * is the passed name with an upper cased first character. This class must
   * also implement sfGridFormatterInterface, otherwise an exception is thrown.
   *
   * @param  string $name              The name of the formatter
   * @throws UnexpectedValueException  Throws an exception when the class
   *                                   sfGridFormatter%Name% does not exist
   * @throws UnexpectedValueException  Throws an exception when the class
   *                                   sfGridFormatter%Name% does not implement
   *                                   sfDataSourceInterface.
   */
  public function setFormatterName($name)
  {
    // resolve the class name
    $class = 'sfGridFormatter'.ucfirst($name);
    if (!class_exists($class))
    {
      throw new UnexpectedValueException(sprintf('The formatter name "%s" (class %s) does not exist', $name, $class));
    }

    // the formatter must implement sfGridFormatterInterface
    $reflection = new ReflectionClass($class);
    if (!$reflection->implementsInterface('sfGridFormatterInterface'))
    {
      throw new UnexpectedValueException(sprintf('The formatter "%s" (class %s) must implement sfGridFormatterInterface', $name, $class));
    }

    $this->setFormatter(new $class($this));
  }

  /**
   * Returns the formatter given to the grid with setFormatter().
   *
   * @return sfGridFormatterInterface An instance of sfGridFormatterInterface
   */
  public function getFormatter()
  {
    return $this->formatter;
  }

  /**
   * Returns a pager for the data source of the grid.
   *
   * @return sfDataSourcePager An instance of sfDataSourcePager
   */
  public function getPager()
  {
    return $this->pager;
  }

  /**
   * Renders the grid with the formatter that has been set with setFormatter().
   * If no formatter has been set, an exception is thrown.
   *
   * @return string         The rendered output of the formatter
   * @throws LogicException Throws an exception if no formatter has been set
   */
  public function render()
  {
    if ($this->formatter === null)
    {
      throw new LogicException('A formatter must be set before calling render()');
    }

    return $this->formatter->render();
  }

  /**
   * Renders the grid
   *
   * @see render()
   */
  public function __toString()
  {
    return $this->render();
  }

  /**
   * Returns the number of rows in the grid.
   *
   * @return integer The number of rows
   */
  public function count()
  {
    return $this->getDataSource()->count();
  }

  /**
   * Sets the column and the order by which the grid should be sorted. The
   * column name must be one of the column names of the data source. It does
   * not necessarily have to be one of the names given to setColumns().
   *
   * @param string $column The name of the column to sort by
   * @param string $order  The order to sort in. Must be one of sfGrid::ASC,
   *                       sfGrid::DESC, "asc" or "desc".
   */
  public function setSort($column, $order = sfGrid::ASC)
  {
    if ($order !== sfGrid::ASC && $order !== sfGrid::DESC)
    {
      throw new DomainException(sprintf('The value "%s" is no valid sort order. Should be sfGrid::ASC or sfGrid::DESC', $order));
    }

    $this->getDataSource()->requireColumn($column);

    $this->sortColumn = $column;
    $this->sortOrder = $order;

    $this->getDataSource()->setSort($column, $order == sfGrid::ASC
    ? sfDataSourceInterface::ASC
    : sfDataSourceInterface::DESC);
  }

  /**
   * Returns the column by which the grid is sorted. If no sort column has been
   * configured, NULL is returned.
   *
   * @return string The name of the sorted column
   */
  public function getSortColumn()
  {
    return $this->sortColumn;
  }

  /**
   * Returns the order in which the grid is sorted. If no sort column has been
   * configured, NULL is returned.
   *
   * @return string Returns sfGrid::ASC or sfGrid::DESC.
   */
  public function getSortOrder()
  {
    return $this->sortOrder;
  }

  /**
   * Configures the grid. This method is called from the constructor. It can
   * be overridden in child classes to configure the grid.
   */
  public function configure()
  {
  }

  /**
   * Sets the URI used for all sorts of interaction such as sorting or paging.
   * This is usually set to the same URI as the grid is displayed on.
   *
   * @param string $uri A valid URI starting with http://
   */
  public function setUri($uri)
  {
//    if (!preg_match('/^http:\/\//', $uri))
//    {
//      throw new UnexpectedValueException(sprintf('The string "%s" is not a valid URI, an URL should start with http://', $uri));
//    }

    if (strpos($uri, '?') === false )
    {
      $this->uri = $uri;
    }
    else
    {
      $parts = explode('?', $uri);
      $this->uri = $parts[0];
    }

  }

  /**
   * Returns the URI set with setUri(). If no URI has been set, NULL is returned.
   *
   * @return string An URI starting with http:// or NULL
   */
  public function getUri()
  {
    return $this->uri;
  }

  /**
   * Sets which columns should appear as sortable when rendered. Whether
   * and how sortable columns look like is defined by the formatter.
   *
   * You can either pass an array of column names, a single column name or
   * the constant sfGrid::ALL if all columns should be sortable.
   *
   * @param  mixed $columns  An array, a string or sfGrid::ALL
   * @throws LogicException  Throws an exception if any of the given column
   *                         names has not been configured in the call to
   *                         setColumns()
   */
  public function setSortable($columns)
  {
    if ($columns === sfGrid::ALL)
    {
      $this->sortable = sfGrid::ALL;
    }
    else
    {
      // the developer can also pass a single column name
      $columns = (array)$columns;

      foreach ($columns as $column)
      {
        if (!$this->hasColumn($column))
        {
          throw new LogicException(sprintf('The column "%s" has not been configured', $column));
        }
      }

      $this->sortable = $columns;
    }
  }

  /**
   * Returns the names of the columns which are set to sortable with setSortable().
   *
   * @return array An array of column names
   */
  public function getSortable()
  {
    if ($this->sortable === sfGrid::ALL)
    {
      return $this->getColumns();
    }
    else
    {
      return $this->sortable;
    }
  }

  /**
   * Sets a list of widgets that should be used to render values in the given
   * columns. The widgets should be given as associative array with the column
   * names as keys and instances of sfWidget as values.
   *
   * <code>
   * $grid->setWidgets(array(
   *   'id'         => new sfWidgetText(),
   *   'created_at' => new sfWidgetDate(),
   * ));
   * </code>
   *
   * Columns for which you do not specify a widget will not be modified. Per
   * default all columns are rendered with sfWidgetText.
   *
   * @param array $widgets   An associative array of column names and widgets
   * @throws LogicException  Throws an exception if any of the given column
   *                         names has not been configured with setColumns()
   */
  public function setWidgets(array $widgets)
  {
    foreach ($widgets as $column => $widget)
    {
      $this->setWidget($column, $widget);
    }
  }

  /**
   * Sets the widget used to render values in the given column.
   *
   * Per default all columns are rendered with sfWidgetText.
   *
   * @param  string $column   The name of the column
   * @param  sfWidget $widget The widget used to render values in this column
   * @throws LogicException   Throws an exception if the given column
   *                          name has not been configured with setColumns()
   */
  public function setWidget($column, sfWidget $widget)
  {
    if (!$this->hasColumn($column))
    {
      throw new LogicException(sprintf('The column "%s" has not been configured', $column));
    }

    $this->widgets[$column] = $widget;
  }

  /**
   * Returns all widgets for all columns.
   *
   * Per default all columns are rendered with sfWidgetText. You can modify
   * the used widgets for each column by calling setWidgets() or setWidget().
   *
   * @return array An associative array with column names as keys and instances
   *               of sfWidget as values.
   */
  public function getWidgets()
  {
    return $this->widgets;
  }

  /**
   * Returns the widget used to render the given column.
   *
   * Per default all columns are rendered with sfWidgetText. You can modify
   * the used widgets for each column by calling setWidgets() or setWidget().
   *
   * @param  string $column  The name of the column
   * @return sfWidget        The widget used to render this column
   * @throws LogicException  Throws an exception if the given column
   *                         name has not been configured with setColumns()
   */
  public function getWidget($column)
  {
    if (!$this->hasColumn($column))
    {
      throw new LogicException(sprintf('The column "%s" has not been configured', $column));
    }

    return $this->widgets[$column];
  }

}