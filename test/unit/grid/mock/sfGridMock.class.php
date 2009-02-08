<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/sfDataSourceMock.class.php');

class sfGridMock extends sfGrid
{
  public function __construct(array $columns = array('id', 'name'), $limit = null)
  {
    parent::__construct(new sfDataSourceMock($limit));
    
    $this->setColumns(array_intersect(array('id', 'name'), $columns));
  }
}