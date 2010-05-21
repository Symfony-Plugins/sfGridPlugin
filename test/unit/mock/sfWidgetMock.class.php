<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfWidgetMock extends sfWidgetGrid
{
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    return '%'.$value.'%';
  }
}