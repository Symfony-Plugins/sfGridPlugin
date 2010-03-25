<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 * (c) Leon van der Ree <leon@fun4me.demon.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This interface is used to render Grids
 * 
 * This can be for dynamic renders, like html, text, json, xml, etc-renderers
 * or static renders, for example for unobtrusive JavaScript (that won't rely on the grid-data-content) 
 *
 */
interface sfGridFormatterInterface
{
  /**
   * Renders the grid
   * 
   * @return string
   */
  public function render();
  
}