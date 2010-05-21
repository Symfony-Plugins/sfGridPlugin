<?php

/*
 * This file is part of the sfGrid plugin.
 * (c) 2010 Sergio Fabian Vier <sergio.vier@alyssa-it.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * GridHelper.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Sergio Fabian Vier <sergio.vier@alyssa-it.com>
 * @version    SVN: $Id$
 */

/**
 * Returns <script> tags for all javascripts associated with the given grid.
 *
 * The scripts are set by implementing the getJavaScripts() method in the
 * corresponding widget.
 *
 * @return string <script> tags
 */
function get_javascripts_for_grid(sfGrid $grid)
{
  $html = '';
  foreach ($grid->getJavascripts() as $file)
  {
    $html .= javascript_include_tag($file);
  }

  return $html;
}

/**
 * Prints <script> tags for all javascripts associated with the given grid.
 *
 * @see get_javascripts_for_grid()
 */
function include_javascripts_for_grid(sfGrid $grid)
{
  echo get_javascripts_for_grid($grid);
}

/**
 * Returns <link> tags for all stylesheets associated with the given grid.
 *
 * The stylesheets are set by implementing the getStyleSheets() method in the
 * corresponding grid.
 *
 *
 * @return string <link> tags
 */
function get_stylesheets_for_grid(sfGrid $grid)
{
  $html = '';
  foreach ($grid->getStylesheets() as $file => $media)
  {
    $html .= stylesheet_tag($file, array('media' => $media));
  }

  return $html;
}

/**
 * Prints <link> tags for all stylesheets associated with the given grid.
 *
 * @see get_stylesheets_for_grid()
 */
function include_stylesheets_for_grid(sfGrid $grid)
{
  echo get_stylesheets_for_grid($grid);
}

/**
 * Adds stylesheets from the supplied grid to the response object.
 *
 * @param sfGrid $grid
 */
function use_stylesheets_for_grid(sfGrid $grid)
{
  $response = sfContext::getInstance()->getResponse();

  foreach ($grid->getStylesheets() as $file => $media)
  {
    $response->addStylesheet($file, '', array('media' => $media));
  }
}