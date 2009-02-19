<?php

/*
 * This file is part of the symfony package.
 * (c) Leon van der Ree <leon@fun4me.demon.nl>
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

//add propel to include-path
set_include_path($_SERVER['SYMFONY'].'/plugins/sfPropelPlugin/lib/vendor'.PATH_SEPARATOR.SF_ROOT_DIR.PATH_SEPARATOR.get_include_path());

function iterator_to_field_array($iterator, $field)
{
  $values = array();
  foreach ($iterator as $key => $value)
  {
    $values[] = $value[$field];
  }
  return $values;
}

class ProjectConfiguration extends sfProjectConfiguration {}

// initialize Propel
$autoload = sfSimpleAutoload::getInstance(sfToolkit::getTmpDir().DIRECTORY_SEPARATOR.sprintf('sf_autoload_unit_propel_%s.data', md5(__FILE__)));
$autoload->addDirectory(realpath($_SERVER['SYMFONY'].'/plugins/sfPropelPlugin/lib'));
$autoload->addDirectory(realpath(dirname(__FILE__).'/model'));
$autoload->register();

if (!extension_loaded('SQLite'))
{
  $t->skip('SQLite needed to run these tests', $tests);
  exit(0);
}

// initialize the storage
$database = new sfPropelDatabase(array('dsn' => 'sqlite::memory:'));
$connection = $database->getConnection();
$connection->exec("CREATE TABLE `person_propel` (
  `id` INTEGER,
  `name` VARCHAR( 255 ) NOT NULL,
  PRIMARY KEY (`id`)
) ");

$coll = array();
$coll[] = 'Fabien';
$coll[] = 'Francois';
$coll[] = 'Jonathan';
$coll[] = 'Fabian';
$coll[] = 'Kris';
$coll[] = 'Nicolas';
$coll[] = 'Dustin';
$coll[] = 'Carl';

foreach ($coll as $name)
{
  $pp = new PersonPropel();
  $pp->setName($name);
  $pp->save($connection);
}
  
ini_set('session.use_cookies', 0);
$session_id = "1";


$t = new lime_test(36, new lime_output_color());

// ->__construct()
$t->diag('->__construct()');

$s = PersonPropelRegister::getPersonPropelDataSource($connection);
$t->is($s->hasColumn('person_propel.ID'), true, 'HasColumn person_propel.ID');

$current = $s->current();
$t->is($current['person_propel.ID'], 1, '->__construct() accepts a Propel class name as argument');

try
{
  $s = new sfDataSourcePropel('foobar');
  $t->fail('->__construct() throws an "UnexpectedValueException" if the given class name is no Propel class');
}
catch (UnexpectedValueException $e)
{
  $t->pass('->__construct() throws an "UnexpectedValueException" if the given class name is no Propel class');
}

try
{
  $s = new sfDataSourcePropel(new stdClass);
  $t->fail('->__construct() throws an "InvalidArgumentException" if the argument is not valid');
}
catch (InvalidArgumentException $e)
{
  $t->pass('->__construct() throws an "InvalidArgumentException" if the argument is not valid');
}

// ->current()
$t->diag('->current()');
$s = PersonPropelRegister::getPersonPropelDataSource($connection);
$current = $s->current();
$t->is($current['person_propel.ID'], 1, '->current() returns the first result');
$s->next();
$current = $s->current();
$t->is($current['person_propel.ID'], 2, '->current() returns the current result when iterating');

foreach ($s as $k => $v);

try
{
  $s->current();
  $t->fail('->current() throws an "OutOfBoundsException" when accessed after iterating');
}
catch (OutOfBoundsException $e)
{
  $t->pass('->current() throws an "OutOfBoundsException" when accessed after iterating');
}

// SeekableIterator interface
$t->diag('SeekableIterator interface');
$s = PersonPropelRegister::getPersonPropelDataSource($connection);
$t->is(array_keys(iterator_to_array($s)), range(0, 7), 'sfDataSourcePropel implements the SeekableIterator interface');
$t->is(count(iterator_to_array($s)), 8, 'sfDataSourcePropel implements the SeekableIterator interface');

$s = PersonPropelRegister::getPersonPropelDataSource($connection);
$s->seek(1);
$t->is($s['person_propel.ID'], 2, 'sfDataSourcePropel implements the SeekableIterator interface');
$t->is($s['person_propel.NAME'], 'Francois', 'sfDataSourcePropel implements the SeekableIterator interface');

try
{
  $s->seek(30);
  $t->fail('->seek() throws an "OutOfBoundsException" when the given index is too large');
}
catch (OutOfBoundsException $e)
{
  $t->pass('->seek() throws an "OutOfBoundsException" when the given index is too large');
}

try
{
  $s->seek(-1);
  $t->fail('->seek() throws an "OutOfBoundsException" when the given index is too small');
}
catch (OutOfBoundsException $e)
{
  $t->pass('->seek() throws an "OutOfBoundsException" when the given index is too small');
}

// Countable interface
$t->diag('Countable interface');
$s = PersonPropelRegister::getPersonPropelDataSource($connection);
$t->is(count($s), 8, 'sfDataSourcePropel implements the Countable interface');
$s->setLimit(4);
$t->is(count($s), 4, 'sfDataSourcePropel implements the Countable interface');
$s->setOffset(5);
$t->is(count($s), 3, 'sfDataSourcePropel implements the Countable interface');

// ->countAll()
$t->diag('->countAll()');
$s = PersonPropelRegister::getPersonPropelDataSource($connection);
$t->is($s->countAll(), 8, '->countAll() returns the total amount of records');
$s->setLimit(4);
$t->is($s->countAll(), 8, '->countAll() returns the total amount of records');
$s->setOffset(5);
$t->is($s->countAll(), 8, '->countAll() returns the total amount of records');

// ->setLimit()
$t->diag('->setLimit()');
$s = PersonPropelRegister::getPersonPropelDataSource($connection);
$s->setLimit(4);
$t->is(iterator_to_field_array($s, 'person_propel.ID'), range(1,4), '->setLimit() limits the records returned by the iterator');

// ->setOffset()
$t->diag('->setOffset()');
$s = PersonPropelRegister::getPersonPropelDataSource($connection);
$s->setOffset(3);
$t->is(iterator_to_field_array($s, 'person_propel.ID'), range(4,8), '->setOffset() sets the offset of the iterator');

$s->setOffset(30);
$t->is(iterator_to_field_array($s, 'person_propel.ID'), array(), '->setOffset() sets the offset of the iterator');

$s->setOffset(2);
$s->seek(1);
$t->is($s['person_propel.ID'], 4, '->setOffset() sets the offset of the iterator');
$t->is($s['person_propel.NAME'], 'Fabian', '->setOffset() sets the offset of the iterator');

// ArrayAccess interface
$t->diag('ArrayAccess interface');
$s = PersonPropelRegister::getPersonPropelDataSource($connection);

$t->is($s['person_propel.ID'], 1, 'sfDataSourcePropel implements the ArrayAccess interface');
$t->is($s['person_propel.NAME'], 'Fabien', 'sfDataSourcePropel implements the ArrayAccess interface');
$t->ok(isset($s['person_propel.ID']), 'sfDataSourcePropel implements the ArrayAccess interface');
$t->ok(!isset($s['foobar']), 'sfDataSourcePropel implements the ArrayAccess interface');
$s->next();
$t->is($s['person_propel.ID'], 2, 'sfDataSourcePropel implements the ArrayAccess interface');
$t->is($s['person_propel.NAME'], 'Francois', 'sfDataSourcePropel implements the ArrayAccess interface');

try
{
  $s['person_propel.NAME'] = 'Foobar';
  $t->fail('sfDataSourcePropel throws a "LogicException" when fields are set using ArrayAccess');
}
catch (LogicException $e)
{
  $t->pass('sfDataSourcePropel throws a "LogicException" when fields are set using ArrayAccess');
}
try
{
  unset($s['person_propel.NAME']);
  $t->fail('sfDataSourcePropel throws a "LogicException" when fields are unset using ArrayAccess');
}
catch (LogicException $e)
{
  $t->pass('sfDataSourcePropel throws a "LogicException" when fields are unset using ArrayAccess');
}

foreach ($s as $k => $v);

try
{
  $s['person_propel.NAME'];
  $t->fail('sfDataSourcePropel throws an "OutOfBoundsException" when fields are accessed after iterating');
}
catch (OutOfBoundsException $e)
{
  $t->pass('sfDataSourcePropel throws an "OutOfBoundsException" when fields are accessed after iterating');
}
try
{
  isset($s['person_propel.NAME']);
  $t->fail('sfDataSourcePropel throws an "OutOfBoundsException" when fields are accessed after iterating');
}
catch (OutOfBoundsException $e)
{
  $t->pass('sfDataSourcePropel throws an "OutOfBoundsException" when fields are accessed after iterating');
}

// ->setSort()
$t->diag('->setSort()');
$s = PersonPropelRegister::getPersonPropelDataSource($connection);
$originalValues = $coll;

$s->setSort('person_propel.NAME', sfDataSourceInterface::DESC);
rsort($originalValues);
$t->is(iterator_to_field_array($s, 'person_propel.NAME'), $originalValues, '->setSort() sorts correctly');

$s->setSort('person_propel.NAME', sfDataSourceInterface::ASC);
sort($originalValues);
$t->is(iterator_to_field_array($s, 'person_propel.NAME'), $originalValues, '->setSort() sorts correctly');

