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
set_include_path($_SERVER['SYMFONY'].'/plugins/sfPropelPlugin/lib/vendor'.PATH_SEPARATOR.get_include_path());

// initialize Doctrine
$autoload = sfSimpleAutoload::getInstance(sfToolkit::getTmpDir().DIRECTORY_SEPARATOR.sprintf('sf_autoload_unit_doctrine_%s.data', md5(__FILE__)));
$autoload->addDirectory(realpath($_SERVER['SYMFONY'].'/plugins/sfDoctrinePlugin/lib'));
$autoload->register();

function iterator_to_field_array($iterator, $field)
{
  $values = array();
  foreach ($iterator as $key => $value)
  {
    $values[] = $iterator[$field];
  }
  return $values;
}


function iterator_ids_to_field_array($iterator)
{
  $values = array();
  foreach ($iterator as $key => $value)
  {
    $values[] = $value->getId();
  }
  return $values;
}

function iterator_names_to_field_array($iterator)
{
  $values = array();
  foreach ($iterator as $key => $value)
  {
    $values[] = $value->getName();
  }
  return $values;
}

$t = new lime_test(47, new lime_output_color());

// initialize Propel
$autoload = sfSimpleAutoload::getInstance(sfToolkit::getTmpDir().DIRECTORY_SEPARATOR.sprintf('sf_autoload_unit_propel_%s.data', md5(__FILE__)));
$autoload->addDirectory(realpath($_SERVER['SYMFONY'].'/plugins/sfPropelPlugin/lib'));
$autoload->addDirectory(realpath(dirname(__FILE__).'/model'));
$autoload->register();

if (!extension_loaded('SQLite'))
{
  $t->error('SQLite needed to run these tests');
  exit(0);
}

// initialize the storage
$database = new sfPropelDatabase(array('dsn' => 'sqlite::memory:', 'pooling' => true));
$connection = $database->getConnection();
$connection->exec("CREATE TABLE `album` (
  `id` INTEGER NOT NULL PRIMARY KEY,
  `album_id` INTEGER
    CONSTRAINT `album_FK_1` REFERENCES `album` (`id`),
  `map` VARCHAR( 255 ),
  `name` VARCHAR( 255 ),
  `description` TEXT
) ");

$connection->exec("CREATE TABLE `foto` (
  `id` INTEGER  NOT NULL PRIMARY KEY,
  `album_id` INTEGER  NOT NULL
    CONSTRAINT `foto_FK_1` REFERENCES `album` (`id`),

  `alternative_album_id` INTEGER  default NULL
    CONSTRAINT `foto_FK_2` REFERENCES `album` (`id`) ON UPDATE CASCADE,

  `filename` varchar(255) default NULL,
  `title` varchar(255) default NULL,
  `description` text,
  `owner_firstname` varchar(100) default NULL,
  `owner_lastname` varchar(100) default NULL
) ");



$albumNames = array();
$albumNames[] = 'Album 1';
$albumNames[] = 'Album 2';
$albumNames[] = 'Album 3';

$albums = array();
foreach ($albumNames as $name)
{
  $album = new Album();
  $album->setName($name);
  $album->save($connection);

  $albums[] = $album;
}

$fotoTitles = array();
$fotoTitles[] = 'title 1';
$fotoTitles[] = 'title 2';
$fotoTitles[] = 'title 3';
$fotoTitles[] = 'title 4';
$fotoTitles[] = 'title 5';
$fotoTitles[] = 'title 6';
$fotoTitles[] = 'title 7';
$fotoTitles[] = 'title 8';
$fotoTitles[] = 'title 9';

$i=0;
foreach ($fotoTitles as $title)
{
  $foto = new Foto();
  $foto->setTitle($title);
  $foto->setAlbumRelatedByAlbumId($albums[floor($i/5)+1]);
  $foto->save($connection);

  $i++;
}

//echo 'Test: ';
//$stmt = $connection->prepare("SELECT Foto.ID, Foto.ALBUM_ID, Foto.ALTERNATIVE_ALBUM_ID, Foto.FILENAME, Foto.TITLE, Foto.DESCRIPTION, Foto.OWNER_FIRSTNAME, Foto.OWNER_LASTNAME FROM foto Foto");
//$stmt->execute();
//$results = $stmt->fetchAll();
//print_r($results);

ini_set('session.use_cookies', 0);
$session_id = "1";


// ->__construct()
$t->diag('->__construct()');

$fotos = new sfDataSourcePropel('Foto') ;
$fotos->setConnection($connection);

try
{
  $fotos->requireColumn('Id');
  $t->pass('->requireColumn() doesn\'t throw an error since it has the column Id');
}
catch (LogicException $e)
{
  $t->fail('->requireColumn() throws an error while column Id should be there');
}

try
{
  $fotos->requireColumn('Faked');
  $t->fail('->requireColumn() should throw an error since it does not have the column Faked');
}
catch (LogicException $e)
{
  $t->pass('->requireColumn() throws an error since it does not have the column Faked');
}

$foto = $fotos->current();
$t->is($foto->getId(), 1, '->__construct() accepts a Propel class name as argument');

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
$s = new sfDataSourcePropel('Foto');
$s->setConnection($connection);
$current = $s->current();
$t->is($current->getId(), 1, '->current() returns the first result');
$s->next();
$current = $s->current();
$t->is($current->getId(), 2, '->current() returns the current result when iterating');

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
$s = new sfDataSourcePropel('Foto');
$s->setConnection($connection);
$t->is(array_keys(iterator_to_array($s)), range(0, 8), 'sfDataSourcePropel implements the SeekableIterator interface');
$t->is(count(iterator_to_array($s)), 9, 'sfDataSourcePropel implements the SeekableIterator interface');

$s = new sfDataSourcePropel('Foto');
$s->setConnection($connection);
$s->seek(1);
$t->is($s->current()->getId(), 2, 'sfDataSourcePropel implements the SeekableIterator interface');
$t->is($s->current()->getTitle(), 'title 2', 'sfDataSourcePropel implements the SeekableIterator interface');

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
$s = new sfDataSourcePropel('Foto');
$s->setConnection($connection);
$t->is(count($s), 9, 'sfDataSourcePropel implements the Countable interface');
$s->setLimit(4);
$t->is(count($s), 4, 'sfDataSourcePropel implements the Countable interface');
$s->setOffset(5);
$t->is(count($s), 4, 'sfDataSourcePropel implements the Countable interface');

// ->countAll()
$t->diag('->countAll()');
$s = new sfDataSourcePropel('Foto');
$s->setConnection($connection);
$t->is($s->countAll(), 9, '->countAll() returns the total amount of records');
$s->setLimit(4);
$t->is($s->countAll(), 9, '->countAll() returns the total amount of records');
$s->setOffset(5);
$t->is($s->countAll(), 9, '->countAll() returns the total amount of records');

// ->setLimit()
$t->diag('->setLimit()');
$s = new sfDataSourcePropel('Foto');
$s->setConnection($connection);
$s->setLimit(4);
$t->is(iterator_to_field_array($s, 'AlbumRelatedByAlbumId.Id'), array(2,2,2,2), '->setLimit() limits the records returned by the iterator');

// ->setOffset()
$t->diag('->setOffset()');
$s = new sfDataSourcePropel('Foto');
$s->setConnection($connection);
$s->setOffset(3);
$t->is(iterator_ids_to_field_array($s), range(4,9), '->setOffset() sets the offset of the iterator');

$s->setOffset(30);
$t->is(iterator_ids_to_field_array($s), array(), '->setOffset() sets the offset of the iterator');

$s->setOffset(2);
$s->seek(1);
$t->is($s['Id'], 4, '->setOffset() sets the offset of the iterator');
$t->is($s['Title'], 'title 4', '->setOffset() sets the offset of the iterator');

// ArrayAccess interface
$t->diag('ArrayAccess interface');
$s = new sfDataSourcePropel('Foto');
$s->setConnection($connection);
$t->is($s['Id'], 1, 'sfDataSourcePropel implements the ArrayAccess interface');
$t->is($s['Title'], 'title 1', 'sfDataSourcePropel implements the ArrayAccess interface');
$t->ok(isset($s['Id']), 'sfDataSourcePropel implements the ArrayAccess interface');
$t->ok(!isset($s['foobar']), 'sfDataSourcePropel implements the ArrayAccess interface');
$s->next();
$t->is($s['Id'] , 2, 'sfDataSourcePropel implements the ArrayAccess interface');
$t->is($s['Title'], 'title 2', 'sfDataSourcePropel implements the ArrayAccess interface');

try
{
  $s['Title'] = 'Foobar';
  $t->fail('sfDataSourcePropel throws a "LogicException" when fields are set using ArrayAccess');
}
catch (LogicException $e)
{
  $t->pass('sfDataSourcePropel throws a "LogicException" when fields are set using ArrayAccess');
}
try
{
  unset($s['Title']);
  $t->fail('sfDataSourcePropel throws a "LogicException" when fields are unset using ArrayAccess');
}
catch (LogicException $e)
{
  $t->pass('sfDataSourcePropel throws a "LogicException" when fields are unset using ArrayAccess');
}

foreach ($s as $k => $v);

try
{
  $s->current()->getName();
  $t->fail('sfDataSourcePropel throws an "OutOfBoundsException" when fields are accessed after iterating');
}
catch (OutOfBoundsException $e)
{
  $t->pass('sfDataSourcePropel throws an "OutOfBoundsException" when fields are accessed after iterating');
}
try
{
  isset($s['Title']);
  $t->fail('sfDataSourcePropel throws an "OutOfBoundsException" when fields are accessed after iterating');
}
catch (OutOfBoundsException $e)
{
  $t->pass('sfDataSourcePropel throws an "OutOfBoundsException" when fields are accessed after iterating');
}

// ->setSort()
$t->diag('->setSort()');
$s = new sfDataSourcePropel('Foto');
$s->setConnection($connection);
$originalValues = array(
  'title 1',
  'title 2',
  'title 3',
  'title 4',
  'title 5',
  'title 6',
  'title 7',
  'title 8',
  'title 9',
);

$s->setSort('Title', sfDataSourceInterface::DESC);
rsort($originalValues);
$t->is(iterator_to_field_array($s, 'Title'), $originalValues, '->setSort() sorts correctly');

//$s->setSort('Title', sfDataSourceInterface::ASC);
//sort($originalValues);
//$t->is(iterator_names_to_field_array($s), $originalValues, '->setSort() sorts correctly');

// static methods
$t->diag('testing helper methods');
sfContext::getInstance()->getConfiguration()->loadHelpers(array('sfPropelPropertyPath'));

$t->is(resolveBaseClass('Base.Child.ChildChild'), 'Base', 'resolveBaseClass resolves first class from objectPath');

$t->is(resolveClassNameFromObjectPath('Foto'), 'Foto', 'resolveClassNameFromObjectPath resolves the latest class from objectPath');
$t->is(resolveClassNameFromObjectPath('Foto.AlbumRelatedByAlbumId'), 'Album', 'resolveClassNameFromObjectPath resolves the latest class from objectPath');

$relation = getRelationForRelationPath('Foto.AlbumRelatedByAlbumId');
$t->is($relation['associateMethod'], 'addFotoRelatedByAlbumId', 'resolveFirstAddMethodForObjectPath resolves add Method for first relation objectPath');

try
{
  checkObjectPath('Foto.AlbumRelatedByAlbumId');
  $t->pass('checkObjectPath OK with valid Path');
}
catch (Exception $e)
{
  $t->fail('checkObjectPath OK with valid Path');
}

try
{
  checkObjectPath('Invalid.Child.ChildChild');
  $t->fail('checkObjectPath throws an InvalidArgumentException with invalid Path');
}
catch (InvalidArgumentException $e)
{
  $t->pass('checkObjectPath throws an InvalidArgumentException: with invalid Path');
}

try
{
  checkObjectPath('Invalid');
  $t->fail('checkObjectPath throws an InvalidArgumentException with invalid Class ');
}
catch (InvalidArgumentException $e)
{
  $t->pass('checkObjectPath throws an InvalidArgumentException with invalid Class');
}


$classes = array();
$classes = flattenAllClasses('Foto');
$t->is(count($classes), 1, 'resolveAllClasses returns one class for "Foto"');
$classAliasses = array_keys($classes);
$t->is($classAliasses[0], 'Foto', 'resolveAllClasses returns alias "Foto"');

$classes = flattenAllClasses('Foto.AlbumRelatedByAlbumId', $classes);
$t->is(count($classes), 2, 'resolveAllClasses correctly adds two classes');
$base = $classes['Foto'];
$t->is(count($base['relatedTo']), 1, '"Foto" correctly gets related to one child class');
