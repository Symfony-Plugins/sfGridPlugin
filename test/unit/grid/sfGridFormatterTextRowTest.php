<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../mock/sfGridMock.class.php');
require_once(dirname(__FILE__).'/../mock/sfWidgetMock.class.php');

$t = new lime_test(0, new lime_output_color());

