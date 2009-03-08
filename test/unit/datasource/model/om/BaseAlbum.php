<?php

/**
 * Base class that represents a row from the 'album' table.
 *
 * 
 *
 * @package    lib.model.om
 */
abstract class BaseAlbum extends BaseObject  implements Persistent {


  const PEER = 'AlbumPeer';

	/**
	 * The Peer class.
	 * Instance provides a convenient way of calling static methods on a class
	 * that calling code may not be able to identify.
	 * @var        AlbumPeer
	 */
	protected static $peer;

	/**
	 * The value for the id field.
	 * @var        int
	 */
	protected $id;

	/**
	 * The value for the album_id field.
	 * @var        int
	 */
	protected $album_id;

	/**
	 * The value for the map field.
	 * @var        string
	 */
	protected $map;

	/**
	 * The value for the name field.
	 * @var        string
	 */
	protected $name;

	/**
	 * The value for the description field.
	 * @var        string
	 */
	protected $description;

	/**
	 * @var        Album
	 */
	protected $aAlbumRelatedByAlbumId;

	/**
	 * @var        array Album[] Collection to store aggregation of Album objects.
	 */
	protected $collAlbumsRelatedByAlbumId;

	/**
	 * @var        Criteria The criteria used to select the current contents of collAlbumsRelatedByAlbumId.
	 */
	private $lastAlbumRelatedByAlbumIdCriteria = null;

	/**
	 * @var        array Foto[] Collection to store aggregation of Foto objects.
	 */
	protected $collFotosRelatedByAlbumId;

	/**
	 * @var        Criteria The criteria used to select the current contents of collFotosRelatedByAlbumId.
	 */
	private $lastFotoRelatedByAlbumIdCriteria = null;

	/**
	 * @var        array Foto[] Collection to store aggregation of Foto objects.
	 */
	protected $collFotosRelatedByAlternativeAlbumId;

	/**
	 * @var        Criteria The criteria used to select the current contents of collFotosRelatedByAlternativeAlbumId.
	 */
	private $lastFotoRelatedByAlternativeAlbumIdCriteria = null;

	/**
	 * Flag to prevent endless save loop, if this object is referenced
	 * by another object which falls in this transaction.
	 * @var        boolean
	 */
	protected $alreadyInSave = false;

	/**
	 * Flag to prevent endless validation loop, if this object is referenced
	 * by another object which falls in this transaction.
	 * @var        boolean
	 */
	protected $alreadyInValidation = false;

  /**
   * The holder for all custom columns of an query
   * @var array
   */
  protected $customColumns = array();

	/**
	 * Initializes internal state of BaseAlbum object.
	 * @see        applyDefaults()
	 */
	public function __construct()
	{
		parent::__construct();
		$this->applyDefaultValues();
	}

	/**
	 * Applies default values to this object.
	 * This method should be called from the object's constructor (or
	 * equivalent initialization method).
	 * @see        __construct()
	 */
	public function applyDefaultValues()
	{
	}

	/**
	 * Get the [id] column value.
	 * 
	 * @return     int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get the [album_id] column value.
	 * 
	 * @return     int
	 */
	public function getAlbumId()
	{
		return $this->album_id;
	}

	/**
	 * Get the [map] column value.
	 * 
	 * @return     string
	 */
	public function getMap()
	{
		return $this->map;
	}

	/**
	 * Get the [name] column value.
	 * 
	 * @return     string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get the [description] column value.
	 * 
	 * @return     string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Set the value of [id] column.
	 * 
	 * @param      int $v new value
	 * @return     Album The current object (for fluent API support)
	 */
	public function setId($v)
	{
		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->id !== $v) {
			$this->id = $v;
			$this->modifiedColumns[] = AlbumPeer::ID;
		}

		return $this;
	} // setId()

	/**
	 * Set the value of [album_id] column.
	 * 
	 * @param      int $v new value
	 * @return     Album The current object (for fluent API support)
	 */
	public function setAlbumId($v)
	{
		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->album_id !== $v) {
			$this->album_id = $v;
			$this->modifiedColumns[] = AlbumPeer::ALBUM_ID;
		}

		if ($this->aAlbumRelatedByAlbumId !== null && $this->aAlbumRelatedByAlbumId->getId() !== $v) {
			$this->aAlbumRelatedByAlbumId = null;
		}

		return $this;
	} // setAlbumId()

	/**
	 * Set the value of [map] column.
	 * 
	 * @param      string $v new value
	 * @return     Album The current object (for fluent API support)
	 */
	public function setMap($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->map !== $v) {
			$this->map = $v;
			$this->modifiedColumns[] = AlbumPeer::MAP;
		}

		return $this;
	} // setMap()

	/**
	 * Set the value of [name] column.
	 * 
	 * @param      string $v new value
	 * @return     Album The current object (for fluent API support)
	 */
	public function setName($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->name !== $v) {
			$this->name = $v;
			$this->modifiedColumns[] = AlbumPeer::NAME;
		}

		return $this;
	} // setName()

	/**
	 * Set the value of [description] column.
	 * 
	 * @param      string $v new value
	 * @return     Album The current object (for fluent API support)
	 */
	public function setDescription($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->description !== $v) {
			$this->description = $v;
			$this->modifiedColumns[] = AlbumPeer::DESCRIPTION;
		}

		return $this;
	} // setDescription()

	/**
	 * Indicates whether the columns in this object are only set to default values.
	 *
	 * This method can be used in conjunction with isModified() to indicate whether an object is both
	 * modified _and_ has some values set which are non-default.
	 *
	 * @return     boolean Whether the columns in this object are only been set with default values.
	 */
	public function hasOnlyDefaultValues()
	{
			// First, ensure that we don't have any columns that have been modified which aren't default columns.
			if (array_diff($this->modifiedColumns, array())) {
				return false;
			}

		// otherwise, everything was equal, so return TRUE
		return true;
	} // hasOnlyDefaultValues()

	/**
	 * Hydrates (populates) the object variables with values from the database resultset.
	 *
	 * An offset (0-based "start column") is specified so that objects can be hydrated
	 * with a subset of the columns in the resultset rows.  This is needed, for example,
	 * for results of JOIN queries where the resultset row includes columns from two or
	 * more tables.
	 *
	 * @param      array $row The row returned by PDOStatement->fetch(PDO::FETCH_NUM)
	 * @param      int $startcol 0-based offset column which indicates which restultset column to start with.
	 * @param      boolean $rehydrate Whether this object is being re-hydrated from the database.
	 * @return     int next starting column
	 * @throws     PropelException  - Any caught Exception will be rewrapped as a PropelException.
	 */
	public function hydrate($row, $startcol = 0, $rehydrate = false)
	{
		try {

			$this->id = ($row[$startcol + 0] !== null) ? (int) $row[$startcol + 0] : null;
			$this->album_id = ($row[$startcol + 1] !== null) ? (int) $row[$startcol + 1] : null;
			$this->map = ($row[$startcol + 2] !== null) ? (string) $row[$startcol + 2] : null;
			$this->name = ($row[$startcol + 3] !== null) ? (string) $row[$startcol + 3] : null;
			$this->description = ($row[$startcol + 4] !== null) ? (string) $row[$startcol + 4] : null;
			$this->resetModified();

			$this->setNew(false);

			if ($rehydrate) {
				$this->ensureConsistency();
			}

			// FIXME - using NUM_COLUMNS may be clearer.
			return $startcol + 5; // 5 = AlbumPeer::NUM_COLUMNS - AlbumPeer::NUM_LAZY_LOAD_COLUMNS).

		} catch (Exception $e) {
			throw new PropelException("Error populating Album object", $e);
		}
	}

	/**
	 * Checks and repairs the internal consistency of the object.
	 *
	 * This method is executed after an already-instantiated object is re-hydrated
	 * from the database.  It exists to check any foreign keys to make sure that
	 * the objects related to the current object are correct based on foreign key.
	 *
	 * You can override this method in the stub class, but you should always invoke
	 * the base method from the overridden method (i.e. parent::ensureConsistency()),
	 * in case your model changes.
	 *
	 * @throws     PropelException
	 */
	public function ensureConsistency()
	{

		if ($this->aAlbumRelatedByAlbumId !== null && $this->album_id !== $this->aAlbumRelatedByAlbumId->getId()) {
			$this->aAlbumRelatedByAlbumId = null;
		}
	} // ensureConsistency

	/**
	 * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
	 *
	 * This will only work if the object has been saved and has a valid primary key set.
	 *
	 * @param      boolean $deep (optional) Whether to also de-associated any related objects.
	 * @param      PropelPDO $con (optional) The PropelPDO connection to use.
	 * @return     void
	 * @throws     PropelException - if this object is deleted, unsaved or doesn't have pk match in db
	 */
	public function reload($deep = false, PropelPDO $con = null)
	{
		if ($this->isDeleted()) {
			throw new PropelException("Cannot reload a deleted object.");
		}

		if ($this->isNew()) {
			throw new PropelException("Cannot reload an unsaved object.");
		}

		if ($con === null) {
			$con = Propel::getConnection(AlbumPeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		// We don't need to alter the object instance pool; we're just modifying this instance
		// already in the pool.

		$stmt = AlbumPeer::doSelectStmt($this->buildPkeyCriteria(), $con);
		$row = $stmt->fetch(PDO::FETCH_NUM);
		$stmt->closeCursor();
		if (!$row) {
			throw new PropelException('Cannot find matching row in the database to reload object values.');
		}
		$this->hydrate($row, 0, true); // rehydrate

		if ($deep) {  // also de-associate any related objects?

			$this->aAlbumRelatedByAlbumId = null;
			$this->collAlbumsRelatedByAlbumId = null;
			$this->lastAlbumRelatedByAlbumIdCriteria = null;

			$this->collFotosRelatedByAlbumId = null;
			$this->lastFotoRelatedByAlbumIdCriteria = null;

			$this->collFotosRelatedByAlternativeAlbumId = null;
			$this->lastFotoRelatedByAlternativeAlbumIdCriteria = null;

		} // if (deep)
	}

	/**
	 * Removes this object from datastore and sets delete attribute.
	 *
	 * @param      PropelPDO $con
	 * @return     void
	 * @throws     PropelException
	 * @see        BaseObject::setDeleted()
	 * @see        BaseObject::isDeleted()
	 */
	public function delete(PropelPDO $con = null)
	{
		if ($this->isDeleted()) {
			throw new PropelException("This object has already been deleted.");
		}

		if ($con === null) {
			$con = Propel::getConnection(AlbumPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}
		
		$con->beginTransaction();
		try {
			AlbumPeer::doDelete($this, $con);
			$this->setDeleted(true);
			$con->commit();
		} catch (PropelException $e) {
			$con->rollBack();
			throw $e;
		}
	}

	/**
	 * Persists this object to the database.
	 *
	 * If the object is new, it inserts it; otherwise an update is performed.
	 * All modified related objects will also be persisted in the doSave()
	 * method.  This method wraps all precipitate database operations in a
	 * single transaction.
	 *
	 * @param      PropelPDO $con
	 * @return     int The number of rows affected by this insert/update and any referring fk objects' save() operations.
	 * @throws     PropelException
	 * @see        doSave()
	 */
	public function save(PropelPDO $con = null)
	{
		if ($this->isDeleted()) {
			throw new PropelException("You cannot save an object that has been deleted.");
		}

		if ($con === null) {
			$con = Propel::getConnection(AlbumPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}
		
		$con->beginTransaction();
		try {
			$affectedRows = $this->doSave($con);
			$con->commit();
			AlbumPeer::addInstanceToPool($this);
			return $affectedRows;
		} catch (PropelException $e) {
			$con->rollBack();
			throw $e;
		}
	}

	/**
	 * Performs the work of inserting or updating the row in the database.
	 *
	 * If the object is new, it inserts it; otherwise an update is performed.
	 * All related objects are also updated in this method.
	 *
	 * @param      PropelPDO $con
	 * @return     int The number of rows affected by this insert/update and any referring fk objects' save() operations.
	 * @throws     PropelException
	 * @see        save()
	 */
	protected function doSave(PropelPDO $con)
	{
		$affectedRows = 0; // initialize var to track total num of affected rows
		if (!$this->alreadyInSave) {
			$this->alreadyInSave = true;

			// We call the save method on the following object(s) if they
			// were passed to this object by their coresponding set
			// method.  This object relates to these object(s) by a
			// foreign key reference.

			if ($this->aAlbumRelatedByAlbumId !== null) {
				if ($this->aAlbumRelatedByAlbumId->isModified() || $this->aAlbumRelatedByAlbumId->isNew()) {
					$affectedRows += $this->aAlbumRelatedByAlbumId->save($con);
				}
				$this->setAlbumRelatedByAlbumId($this->aAlbumRelatedByAlbumId);
			}

			if ($this->isNew() ) {
				$this->modifiedColumns[] = AlbumPeer::ID;
			}

			// If this object has been modified, then save it to the database.
			if ($this->isModified()) {
				if ($this->isNew()) {
					$pk = AlbumPeer::doInsert($this, $con);
					$affectedRows += 1; // we are assuming that there is only 1 row per doInsert() which
										 // should always be true here (even though technically
										 // BasePeer::doInsert() can insert multiple rows).

					$this->setId($pk);  //[IMV] update autoincrement primary key

					$this->setNew(false);
				} else {
					$affectedRows += AlbumPeer::doUpdate($this, $con);
				}

				$this->resetModified(); // [HL] After being saved an object is no longer 'modified'
			}

			if ($this->collAlbumsRelatedByAlbumId !== null) {
				foreach ($this->collAlbumsRelatedByAlbumId as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
						$affectedRows += $referrerFK->save($con);
					}
				}
			}

			if ($this->collFotosRelatedByAlbumId !== null) {
				foreach ($this->collFotosRelatedByAlbumId as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
						$affectedRows += $referrerFK->save($con);
					}
				}
			}

			if ($this->collFotosRelatedByAlternativeAlbumId !== null) {
				foreach ($this->collFotosRelatedByAlternativeAlbumId as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
						$affectedRows += $referrerFK->save($con);
					}
				}
			}

			$this->alreadyInSave = false;

		}
		return $affectedRows;
	} // doSave()

	/**
	 * Array of ValidationFailed objects.
	 * @var        array ValidationFailed[]
	 */
	protected $validationFailures = array();

	/**
	 * Gets any ValidationFailed objects that resulted from last call to validate().
	 *
	 *
	 * @return     array ValidationFailed[]
	 * @see        validate()
	 */
	public function getValidationFailures()
	{
		return $this->validationFailures;
	}

	/**
	 * Validates the objects modified field values and all objects related to this table.
	 *
	 * If $columns is either a column name or an array of column names
	 * only those columns are validated.
	 *
	 * @param      mixed $columns Column name or an array of column names.
	 * @return     boolean Whether all columns pass validation.
	 * @see        doValidate()
	 * @see        getValidationFailures()
	 */
	public function validate($columns = null)
	{
		$res = $this->doValidate($columns);
		if ($res === true) {
			$this->validationFailures = array();
			return true;
		} else {
			$this->validationFailures = $res;
			return false;
		}
	}

	/**
	 * This function performs the validation work for complex object models.
	 *
	 * In addition to checking the current object, all related objects will
	 * also be validated.  If all pass then <code>true</code> is returned; otherwise
	 * an aggreagated array of ValidationFailed objects will be returned.
	 *
	 * @param      array $columns Array of column names to validate.
	 * @return     mixed <code>true</code> if all validations pass; array of <code>ValidationFailed</code> objets otherwise.
	 */
	protected function doValidate($columns = null)
	{
		if (!$this->alreadyInValidation) {
			$this->alreadyInValidation = true;
			$retval = null;

			$failureMap = array();


			// We call the validate method on the following object(s) if they
			// were passed to this object by their coresponding set
			// method.  This object relates to these object(s) by a
			// foreign key reference.

			if ($this->aAlbumRelatedByAlbumId !== null) {
				if (!$this->aAlbumRelatedByAlbumId->validate($columns)) {
					$failureMap = array_merge($failureMap, $this->aAlbumRelatedByAlbumId->getValidationFailures());
				}
			}


			if (($retval = AlbumPeer::doValidate($this, $columns)) !== true) {
				$failureMap = array_merge($failureMap, $retval);
			}


				if ($this->collAlbumsRelatedByAlbumId !== null) {
					foreach ($this->collAlbumsRelatedByAlbumId as $referrerFK) {
						if (!$referrerFK->validate($columns)) {
							$failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
						}
					}
				}

				if ($this->collFotosRelatedByAlbumId !== null) {
					foreach ($this->collFotosRelatedByAlbumId as $referrerFK) {
						if (!$referrerFK->validate($columns)) {
							$failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
						}
					}
				}

				if ($this->collFotosRelatedByAlternativeAlbumId !== null) {
					foreach ($this->collFotosRelatedByAlternativeAlbumId as $referrerFK) {
						if (!$referrerFK->validate($columns)) {
							$failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
						}
					}
				}


			$this->alreadyInValidation = false;
		}

		return (!empty($failureMap) ? $failureMap : true);
	}

	/**
	 * Retrieves a field from the object by name passed in as a string.
	 *
	 * @param      string $name name
	 * @param      string $type The type of fieldname the $name is of:
	 *                     one of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
	 *                     BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM
	 * @return     mixed Value of field.
	 */
	public function getByName($name, $type = BasePeer::TYPE_PHPNAME)
	{
		$pos = AlbumPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
		$field = $this->getByPosition($pos);
		return $field;
	}

	/**
	 * Retrieves a field from the object by Position as specified in the xml schema.
	 * Zero-based.
	 *
	 * @param      int $pos position in xml schema
	 * @return     mixed Value of field at $pos
	 */
	public function getByPosition($pos)
	{
		switch($pos) {
			case 0:
				return $this->getId();
				break;
			case 1:
				return $this->getAlbumId();
				break;
			case 2:
				return $this->getMap();
				break;
			case 3:
				return $this->getName();
				break;
			case 4:
				return $this->getDescription();
				break;
			default:
				return null;
				break;
		} // switch()
	}

	/**
	 * Exports the object as an array.
	 *
	 * You can specify the key type of the array by passing one of the class
	 * type constants.
	 *
	 * @param      string $keyType (optional) One of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
	 *                        BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM. Defaults to BasePeer::TYPE_PHPNAME.
	 * @param      boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns.  Defaults to TRUE.
	 * @return     an associative array containing the field names (as keys) and field values
	 */
	public function toArray($keyType = BasePeer::TYPE_PHPNAME, $includeLazyLoadColumns = true)
	{
		$keys = AlbumPeer::getFieldNames($keyType);
		$result = array(
			$keys[0] => $this->getId(),
			$keys[1] => $this->getAlbumId(),
			$keys[2] => $this->getMap(),
			$keys[3] => $this->getName(),
			$keys[4] => $this->getDescription(),
		);
		return $result;
	}

	/**
	 * Sets a field from the object by name passed in as a string.
	 *
	 * @param      string $name peer name
	 * @param      mixed $value field value
	 * @param      string $type The type of fieldname the $name is of:
	 *                     one of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
	 *                     BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM
	 * @return     void
	 */
	public function setByName($name, $value, $type = BasePeer::TYPE_PHPNAME)
	{
		$pos = AlbumPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
		return $this->setByPosition($pos, $value);
	}

	/**
	 * Sets a field from the object by Position as specified in the xml schema.
	 * Zero-based.
	 *
	 * @param      int $pos position in xml schema
	 * @param      mixed $value field value
	 * @return     void
	 */
	public function setByPosition($pos, $value)
	{
		switch($pos) {
			case 0:
				$this->setId($value);
				break;
			case 1:
				$this->setAlbumId($value);
				break;
			case 2:
				$this->setMap($value);
				break;
			case 3:
				$this->setName($value);
				break;
			case 4:
				$this->setDescription($value);
				break;
		} // switch()
	}

	/**
	 * Populates the object using an array.
	 *
	 * This is particularly useful when populating an object from one of the
	 * request arrays (e.g. $_POST).  This method goes through the column
	 * names, checking to see whether a matching key exists in populated
	 * array. If so the setByName() method is called for that column.
	 *
	 * You can specify the key type of the array by additionally passing one
	 * of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME,
	 * BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM.
	 * The default key type is the column's phpname (e.g. 'AuthorId')
	 *
	 * @param      array  $arr     An array to populate the object from.
	 * @param      string $keyType The type of keys the array uses.
	 * @return     void
	 */
	public function fromArray($arr, $keyType = BasePeer::TYPE_PHPNAME)
	{
		$keys = AlbumPeer::getFieldNames($keyType);

		if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
		if (array_key_exists($keys[1], $arr)) $this->setAlbumId($arr[$keys[1]]);
		if (array_key_exists($keys[2], $arr)) $this->setMap($arr[$keys[2]]);
		if (array_key_exists($keys[3], $arr)) $this->setName($arr[$keys[3]]);
		if (array_key_exists($keys[4], $arr)) $this->setDescription($arr[$keys[4]]);
	}

	/**
	 * Build a Criteria object containing the values of all modified columns in this object.
	 *
	 * @return     Criteria The Criteria object containing all modified values.
	 */
	public function buildCriteria()
	{
		$criteria = new Criteria(AlbumPeer::DATABASE_NAME);

		if ($this->isColumnModified(AlbumPeer::ID)) $criteria->add(AlbumPeer::ID, $this->id);
		if ($this->isColumnModified(AlbumPeer::ALBUM_ID)) $criteria->add(AlbumPeer::ALBUM_ID, $this->album_id);
		if ($this->isColumnModified(AlbumPeer::MAP)) $criteria->add(AlbumPeer::MAP, $this->map);
		if ($this->isColumnModified(AlbumPeer::NAME)) $criteria->add(AlbumPeer::NAME, $this->name);
		if ($this->isColumnModified(AlbumPeer::DESCRIPTION)) $criteria->add(AlbumPeer::DESCRIPTION, $this->description);

		return $criteria;
	}

	/**
	 * Builds a Criteria object containing the primary key for this object.
	 *
	 * Unlike buildCriteria() this method includes the primary key values regardless
	 * of whether or not they have been modified.
	 *
	 * @return     Criteria The Criteria object containing value(s) for primary key(s).
	 */
	public function buildPkeyCriteria()
	{
		$criteria = new Criteria(AlbumPeer::DATABASE_NAME);

		$criteria->add(AlbumPeer::ID, $this->id);

		return $criteria;
	}

	/**
	 * Returns the primary key for this object (row).
	 * @return     int
	 */
	public function getPrimaryKey()
	{
		return $this->getId();
	}

	/**
	 * Generic method to set the primary key (id column).
	 *
	 * @param      int $key Primary key.
	 * @return     void
	 */
	public function setPrimaryKey($key)
	{
		$this->setId($key);
	}

	/**
	 * Sets contents of passed object to values from current object.
	 *
	 * If desired, this method can also make copies of all associated (fkey referrers)
	 * objects.
	 *
	 * @param      object $copyObj An object of Album (or compatible) type.
	 * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
	 * @throws     PropelException
	 */
	public function copyInto($copyObj, $deepCopy = false)
	{

		$copyObj->setAlbumId($this->album_id);

		$copyObj->setMap($this->map);

		$copyObj->setName($this->name);

		$copyObj->setDescription($this->description);


		if ($deepCopy) {
			// important: temporarily setNew(false) because this affects the behavior of
			// the getter/setter methods for fkey referrer objects.
			$copyObj->setNew(false);

			foreach ($this->getAlbumsRelatedByAlbumId() as $relObj) {
				if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
					$copyObj->addAlbumRelatedByAlbumId($relObj->copy($deepCopy));
				}
			}

			foreach ($this->getFotosRelatedByAlbumId() as $relObj) {
				if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
					$copyObj->addFotoRelatedByAlbumId($relObj->copy($deepCopy));
				}
			}

			foreach ($this->getFotosRelatedByAlternativeAlbumId() as $relObj) {
				if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
					$copyObj->addFotoRelatedByAlternativeAlbumId($relObj->copy($deepCopy));
				}
			}

		} // if ($deepCopy)


		$copyObj->setNew(true);

		$copyObj->setId(NULL); // this is a auto-increment column, so set to default value

	}

	/**
	 * Makes a copy of this object that will be inserted as a new row in table when saved.
	 * It creates a new object filling in the simple attributes, but skipping any primary
	 * keys that are defined for the table.
	 *
	 * If desired, this method can also make copies of all associated (fkey referrers)
	 * objects.
	 *
	 * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
	 * @return     Album Clone of current object.
	 * @throws     PropelException
	 */
	public function copy($deepCopy = false)
	{
		// we use get_class(), because this might be a subclass
		$clazz = get_class($this);
		$copyObj = new $clazz();
		$this->copyInto($copyObj, $deepCopy);
		return $copyObj;
	}

	/**
	 * Returns a peer instance associated with this om.
	 *
	 * Since Peer classes are not to have any instance attributes, this method returns the
	 * same instance for all member of this class. The method could therefore
	 * be static, but this would prevent one from overriding the behavior.
	 *
	 * @return     AlbumPeer
	 */
	public function getPeer()
	{
		if (self::$peer === null) {
			self::$peer = new AlbumPeer();
		}
		return self::$peer;
	}

	/**
	 * Declares an association between this object and a Album object.
	 *
	 * @param      Album $v
	 * @return     Album The current object (for fluent API support)
	 * @throws     PropelException
	 */
	public function setAlbumRelatedByAlbumId(Album $v = null)
	{
		if ($v === null) {
			$this->setAlbumId(NULL);
		} else {
			$this->setAlbumId($v->getId());
		}

		$this->aAlbumRelatedByAlbumId = $v;

		// Add binding for other direction of this n:n relationship.
		// If this object has already been added to the Album object, it will not be re-added.
		if ($v !== null) {
			$v->addAlbumRelatedByAlbumId($this);
		}

		return $this;
	}


	/**
	 * Get the associated Album object
	 *
	 * @param      PropelPDO Optional Connection object.
	 * @return     Album The associated Album object.
	 * @throws     PropelException
	 */
	public function getAlbumRelatedByAlbumId(PropelPDO $con = null)
	{
		if ($this->aAlbumRelatedByAlbumId === null && ($this->album_id !== null)) {
			$c = new Criteria(AlbumPeer::DATABASE_NAME);
			$c->add(AlbumPeer::ID, $this->album_id);
			$this->aAlbumRelatedByAlbumId = AlbumPeer::doSelectOne($c, $con);
			/* The following can be used additionally to
			   guarantee the related object contains a reference
			   to this object.  This level of coupling may, however, be
			   undesirable since it could result in an only partially populated collection
			   in the referenced object.
			   $this->aAlbumRelatedByAlbumId->addAlbumsRelatedByAlbumId($this);
			 */
		}
		return $this->aAlbumRelatedByAlbumId;
	}

	/**
	 * Clears out the collAlbumsRelatedByAlbumId collection (array).
	 *
	 * This does not modify the database; however, it will remove any associated objects, causing
	 * them to be refetched by subsequent calls to accessor method.
	 *
	 * @return     void
	 * @see        addAlbumsRelatedByAlbumId()
	 */
	public function clearAlbumsRelatedByAlbumId()
	{
		$this->collAlbumsRelatedByAlbumId = null; // important to set this to NULL since that means it is uninitialized
	}

	/**
	 * Initializes the collAlbumsRelatedByAlbumId collection (array).
	 *
	 * By default this just sets the collAlbumsRelatedByAlbumId collection to an empty array (like clearcollAlbumsRelatedByAlbumId());
	 * however, you may wish to override this method in your stub class to provide setting appropriate
	 * to your application -- for example, setting the initial array to the values stored in database.
	 *
	 * @return     void
	 */
	public function initAlbumsRelatedByAlbumId()
	{
		$this->collAlbumsRelatedByAlbumId = array();
	}

  /**
   * Touches the collAlbumsRelatedByAlbumId collection (array). (make it array() when null or keep it the way it is)
   *
   * This just sets the collAlbumsRelatedByAlbumId collection to an empty array if it was null;

   * @return     void
   */
  public function touchAlbumsRelatedByAlbumId()
  {
    if (!isset($this->collAlbumsRelatedByAlbumId) ||  ($this->collAlbumsRelatedByAlbumId == null))
    {
      $this->collAlbumsRelatedByAlbumId = array();
    }
  }

  /**
   * Gets an array of Album objects which contain a foreign key that references this object.
   *
   * If this collection has already been initialized, it returns the collection.
   * Otherwise if this Album has previously been saved, it will retrieve
   * related AlbumsRelatedByAlbumId from storage. If this Album is new, it will return
   * an empty collection or the current collection, the criteria is ignored on a new object.
   *
   * @param      PropelPDO $con
   * @param      Criteria $criteria
   * @return     array Album[]
   * @throws     PropelException
   */
  public function getAlbumsRelatedByAlbumId($criteria = null, PropelPDO $con = null)
  {
    if ($criteria === null) {
      $criteria = new Criteria(AlbumPeer::DATABASE_NAME);
    }
    elseif ($criteria instanceof Criteria)
    {
      $criteria = clone $criteria;
    }

    if ($this->collAlbumsRelatedByAlbumId === null) {
      if ($this->isNew()) {
         $this->collAlbumsRelatedByAlbumId = array();
      } else {

        $criteria->add(AlbumPeer::ALBUM_ID, $this->id);

        AlbumPeer::addSelectColumns($criteria);
        $this->collAlbumsRelatedByAlbumId = AlbumPeer::doSelect($criteria, $con);
      }
    } else {
      // criteria has no effect for a new object
      if (!$this->isNew() && !is_array($this->collAlbumsRelatedByAlbumId)) {
        // the following code is to determine if a new query is
        // called for.  If the criteria is the same as the last
        // one, just return the collection.


        $criteria->add(AlbumPeer::ALBUM_ID, $this->id);

        AlbumPeer::addSelectColumns($criteria);
        if (!isset($this->lastAlbumRelatedByAlbumIdCriteria) || !$this->lastAlbumRelatedByAlbumIdCriteria->equals($criteria)) {
          $this->collAlbumsRelatedByAlbumId = AlbumPeer::doSelect($criteria, $con);
        }
      }
    }
    $this->lastAlbumRelatedByAlbumIdCriteria = $criteria;
    return $this->collAlbumsRelatedByAlbumId;
  }

	/**
	 * Returns the number of related Album objects.
	 *
	 * @param      Criteria $criteria
	 * @param      boolean $distinct
	 * @param      PropelPDO $con
	 * @return     int Count of related Album objects.
	 * @throws     PropelException
	 */
	public function countAlbumsRelatedByAlbumId(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(AlbumPeer::DATABASE_NAME);
		} else {
			$criteria = clone $criteria;
		}

		if ($distinct) {
			$criteria->setDistinct();
		}

		$count = null;

		if ($this->collAlbumsRelatedByAlbumId === null) {
			if ($this->isNew()) {
				$count = 0;
			} else {

				$criteria->add(AlbumPeer::ALBUM_ID, $this->id);

				$count = AlbumPeer::doCount($criteria, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return count of the collection.


				$criteria->add(AlbumPeer::ALBUM_ID, $this->id);

				if (!isset($this->lastAlbumRelatedByAlbumIdCriteria) || !$this->lastAlbumRelatedByAlbumIdCriteria->equals($criteria)) {
					$count = AlbumPeer::doCount($criteria, $con);
				} else {
					$count = count($this->collAlbumsRelatedByAlbumId);
				}
			} else {
				$count = count($this->collAlbumsRelatedByAlbumId);
			}
		}
		$this->lastAlbumRelatedByAlbumIdCriteria = $criteria;
		return $count;
	}

	/**
	 * Method called to associate a Album object to this object
	 * through the Album foreign key attribute.
	 *
	 * @param      Album $l Album
	 * @return     void
	 * @throws     PropelException
	 */
	public function addAlbumRelatedByAlbumId(Album $l)
	{
		if ($this->collAlbumsRelatedByAlbumId === null) {
			$this->initAlbumsRelatedByAlbumId();
		}
		if (!in_array($l, $this->collAlbumsRelatedByAlbumId, true)) { // only add it if the **same** object is not already associated
			array_push($this->collAlbumsRelatedByAlbumId, $l);
			$l->setAlbumRelatedByAlbumId($this);
		}
	}

	/**
	 * Clears out the collFotosRelatedByAlbumId collection (array).
	 *
	 * This does not modify the database; however, it will remove any associated objects, causing
	 * them to be refetched by subsequent calls to accessor method.
	 *
	 * @return     void
	 * @see        addFotosRelatedByAlbumId()
	 */
	public function clearFotosRelatedByAlbumId()
	{
		$this->collFotosRelatedByAlbumId = null; // important to set this to NULL since that means it is uninitialized
	}

	/**
	 * Initializes the collFotosRelatedByAlbumId collection (array).
	 *
	 * By default this just sets the collFotosRelatedByAlbumId collection to an empty array (like clearcollFotosRelatedByAlbumId());
	 * however, you may wish to override this method in your stub class to provide setting appropriate
	 * to your application -- for example, setting the initial array to the values stored in database.
	 *
	 * @return     void
	 */
	public function initFotosRelatedByAlbumId()
	{
		$this->collFotosRelatedByAlbumId = array();
	}

  /**
   * Touches the collFotosRelatedByAlbumId collection (array). (make it array() when null or keep it the way it is)
   *
   * This just sets the collFotosRelatedByAlbumId collection to an empty array if it was null;

   * @return     void
   */
  public function touchFotosRelatedByAlbumId()
  {
    if (!isset($this->collFotosRelatedByAlbumId) ||  ($this->collFotosRelatedByAlbumId == null))
    {
      $this->collFotosRelatedByAlbumId = array();
    }
  }

  /**
   * Gets an array of Foto objects which contain a foreign key that references this object.
   *
   * If this collection has already been initialized, it returns the collection.
   * Otherwise if this Album has previously been saved, it will retrieve
   * related FotosRelatedByAlbumId from storage. If this Album is new, it will return
   * an empty collection or the current collection, the criteria is ignored on a new object.
   *
   * @param      PropelPDO $con
   * @param      Criteria $criteria
   * @return     array Foto[]
   * @throws     PropelException
   */
  public function getFotosRelatedByAlbumId($criteria = null, PropelPDO $con = null)
  {
    if ($criteria === null) {
      $criteria = new Criteria(AlbumPeer::DATABASE_NAME);
    }
    elseif ($criteria instanceof Criteria)
    {
      $criteria = clone $criteria;
    }

    if ($this->collFotosRelatedByAlbumId === null) {
      if ($this->isNew()) {
         $this->collFotosRelatedByAlbumId = array();
      } else {

        $criteria->add(FotoPeer::ALBUM_ID, $this->id);

        FotoPeer::addSelectColumns($criteria);
        $this->collFotosRelatedByAlbumId = FotoPeer::doSelect($criteria, $con);
      }
    } else {
      // criteria has no effect for a new object
      if (!$this->isNew() && !is_array($this->collFotosRelatedByAlbumId)) {
        // the following code is to determine if a new query is
        // called for.  If the criteria is the same as the last
        // one, just return the collection.


        $criteria->add(FotoPeer::ALBUM_ID, $this->id);

        FotoPeer::addSelectColumns($criteria);
        if (!isset($this->lastFotoRelatedByAlbumIdCriteria) || !$this->lastFotoRelatedByAlbumIdCriteria->equals($criteria)) {
          $this->collFotosRelatedByAlbumId = FotoPeer::doSelect($criteria, $con);
        }
      }
    }
    $this->lastFotoRelatedByAlbumIdCriteria = $criteria;
    return $this->collFotosRelatedByAlbumId;
  }

	/**
	 * Returns the number of related Foto objects.
	 *
	 * @param      Criteria $criteria
	 * @param      boolean $distinct
	 * @param      PropelPDO $con
	 * @return     int Count of related Foto objects.
	 * @throws     PropelException
	 */
	public function countFotosRelatedByAlbumId(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(AlbumPeer::DATABASE_NAME);
		} else {
			$criteria = clone $criteria;
		}

		if ($distinct) {
			$criteria->setDistinct();
		}

		$count = null;

		if ($this->collFotosRelatedByAlbumId === null) {
			if ($this->isNew()) {
				$count = 0;
			} else {

				$criteria->add(FotoPeer::ALBUM_ID, $this->id);

				$count = FotoPeer::doCount($criteria, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return count of the collection.


				$criteria->add(FotoPeer::ALBUM_ID, $this->id);

				if (!isset($this->lastFotoRelatedByAlbumIdCriteria) || !$this->lastFotoRelatedByAlbumIdCriteria->equals($criteria)) {
					$count = FotoPeer::doCount($criteria, $con);
				} else {
					$count = count($this->collFotosRelatedByAlbumId);
				}
			} else {
				$count = count($this->collFotosRelatedByAlbumId);
			}
		}
		$this->lastFotoRelatedByAlbumIdCriteria = $criteria;
		return $count;
	}

	/**
	 * Method called to associate a Foto object to this object
	 * through the Foto foreign key attribute.
	 *
	 * @param      Foto $l Foto
	 * @return     void
	 * @throws     PropelException
	 */
	public function addFotoRelatedByAlbumId(Foto $l)
	{
		if ($this->collFotosRelatedByAlbumId === null) {
			$this->initFotosRelatedByAlbumId();
		}
		if (!in_array($l, $this->collFotosRelatedByAlbumId, true)) { // only add it if the **same** object is not already associated
			array_push($this->collFotosRelatedByAlbumId, $l);
			$l->setAlbumRelatedByAlbumId($this);
		}
	}


	/**
	 * If this collection has already been initialized with
	 * an identical criteria, it returns the collection.
	 * Otherwise if this Album is new, it will return
	 * an empty collection; or if this Album has previously
	 * been saved, it will retrieve related FotosRelatedByAlbumId from storage.
	 *
	 * This method is protected by default in order to keep the public
	 * api reasonable.  You can provide public methods for those you
	 * actually need in Album.
	 */
	public function getFotosRelatedByAlbumIdJoinUserRelatedByOwnerFirstname($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
		if ($criteria === null) {
			$criteria = new Criteria(AlbumPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collFotosRelatedByAlbumId === null) {
			if ($this->isNew()) {
				$this->collFotosRelatedByAlbumId = array();
			} else {

				$criteria->add(FotoPeer::ALBUM_ID, $this->id);

				$this->collFotosRelatedByAlbumId = FotoPeer::doSelectJoinUserRelatedByOwnerFirstname($criteria, $con, $join_behavior);
			}
		} else {
			// the following code is to determine if a new query is
			// called for.  If the criteria is the same as the last
			// one, just return the collection.

			$criteria->add(FotoPeer::ALBUM_ID, $this->id);

			if (!isset($this->lastFotoRelatedByAlbumIdCriteria) || !$this->lastFotoRelatedByAlbumIdCriteria->equals($criteria)) {
				$this->collFotosRelatedByAlbumId = FotoPeer::doSelectJoinUserRelatedByOwnerFirstname($criteria, $con, $join_behavior);
			}
		}
		$this->lastFotoRelatedByAlbumIdCriteria = $criteria;

		return $this->collFotosRelatedByAlbumId;
	}


	/**
	 * If this collection has already been initialized with
	 * an identical criteria, it returns the collection.
	 * Otherwise if this Album is new, it will return
	 * an empty collection; or if this Album has previously
	 * been saved, it will retrieve related FotosRelatedByAlbumId from storage.
	 *
	 * This method is protected by default in order to keep the public
	 * api reasonable.  You can provide public methods for those you
	 * actually need in Album.
	 */
	public function getFotosRelatedByAlbumIdJoinUserRelatedByOwnerLastname($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
		if ($criteria === null) {
			$criteria = new Criteria(AlbumPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collFotosRelatedByAlbumId === null) {
			if ($this->isNew()) {
				$this->collFotosRelatedByAlbumId = array();
			} else {

				$criteria->add(FotoPeer::ALBUM_ID, $this->id);

				$this->collFotosRelatedByAlbumId = FotoPeer::doSelectJoinUserRelatedByOwnerLastname($criteria, $con, $join_behavior);
			}
		} else {
			// the following code is to determine if a new query is
			// called for.  If the criteria is the same as the last
			// one, just return the collection.

			$criteria->add(FotoPeer::ALBUM_ID, $this->id);

			if (!isset($this->lastFotoRelatedByAlbumIdCriteria) || !$this->lastFotoRelatedByAlbumIdCriteria->equals($criteria)) {
				$this->collFotosRelatedByAlbumId = FotoPeer::doSelectJoinUserRelatedByOwnerLastname($criteria, $con, $join_behavior);
			}
		}
		$this->lastFotoRelatedByAlbumIdCriteria = $criteria;

		return $this->collFotosRelatedByAlbumId;
	}

	/**
	 * Clears out the collFotosRelatedByAlternativeAlbumId collection (array).
	 *
	 * This does not modify the database; however, it will remove any associated objects, causing
	 * them to be refetched by subsequent calls to accessor method.
	 *
	 * @return     void
	 * @see        addFotosRelatedByAlternativeAlbumId()
	 */
	public function clearFotosRelatedByAlternativeAlbumId()
	{
		$this->collFotosRelatedByAlternativeAlbumId = null; // important to set this to NULL since that means it is uninitialized
	}

	/**
	 * Initializes the collFotosRelatedByAlternativeAlbumId collection (array).
	 *
	 * By default this just sets the collFotosRelatedByAlternativeAlbumId collection to an empty array (like clearcollFotosRelatedByAlternativeAlbumId());
	 * however, you may wish to override this method in your stub class to provide setting appropriate
	 * to your application -- for example, setting the initial array to the values stored in database.
	 *
	 * @return     void
	 */
	public function initFotosRelatedByAlternativeAlbumId()
	{
		$this->collFotosRelatedByAlternativeAlbumId = array();
	}

  /**
   * Touches the collFotosRelatedByAlternativeAlbumId collection (array). (make it array() when null or keep it the way it is)
   *
   * This just sets the collFotosRelatedByAlternativeAlbumId collection to an empty array if it was null;

   * @return     void
   */
  public function touchFotosRelatedByAlternativeAlbumId()
  {
    if (!isset($this->collFotosRelatedByAlternativeAlbumId) ||  ($this->collFotosRelatedByAlternativeAlbumId == null))
    {
      $this->collFotosRelatedByAlternativeAlbumId = array();
    }
  }

  /**
   * Gets an array of Foto objects which contain a foreign key that references this object.
   *
   * If this collection has already been initialized, it returns the collection.
   * Otherwise if this Album has previously been saved, it will retrieve
   * related FotosRelatedByAlternativeAlbumId from storage. If this Album is new, it will return
   * an empty collection or the current collection, the criteria is ignored on a new object.
   *
   * @param      PropelPDO $con
   * @param      Criteria $criteria
   * @return     array Foto[]
   * @throws     PropelException
   */
  public function getFotosRelatedByAlternativeAlbumId($criteria = null, PropelPDO $con = null)
  {
    if ($criteria === null) {
      $criteria = new Criteria(AlbumPeer::DATABASE_NAME);
    }
    elseif ($criteria instanceof Criteria)
    {
      $criteria = clone $criteria;
    }

    if ($this->collFotosRelatedByAlternativeAlbumId === null) {
      if ($this->isNew()) {
         $this->collFotosRelatedByAlternativeAlbumId = array();
      } else {

        $criteria->add(FotoPeer::ALTERNATIVE_ALBUM_ID, $this->id);

        FotoPeer::addSelectColumns($criteria);
        $this->collFotosRelatedByAlternativeAlbumId = FotoPeer::doSelect($criteria, $con);
      }
    } else {
      // criteria has no effect for a new object
      if (!$this->isNew() && !is_array($this->collFotosRelatedByAlternativeAlbumId)) {
        // the following code is to determine if a new query is
        // called for.  If the criteria is the same as the last
        // one, just return the collection.


        $criteria->add(FotoPeer::ALTERNATIVE_ALBUM_ID, $this->id);

        FotoPeer::addSelectColumns($criteria);
        if (!isset($this->lastFotoRelatedByAlternativeAlbumIdCriteria) || !$this->lastFotoRelatedByAlternativeAlbumIdCriteria->equals($criteria)) {
          $this->collFotosRelatedByAlternativeAlbumId = FotoPeer::doSelect($criteria, $con);
        }
      }
    }
    $this->lastFotoRelatedByAlternativeAlbumIdCriteria = $criteria;
    return $this->collFotosRelatedByAlternativeAlbumId;
  }

	/**
	 * Returns the number of related Foto objects.
	 *
	 * @param      Criteria $criteria
	 * @param      boolean $distinct
	 * @param      PropelPDO $con
	 * @return     int Count of related Foto objects.
	 * @throws     PropelException
	 */
	public function countFotosRelatedByAlternativeAlbumId(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(AlbumPeer::DATABASE_NAME);
		} else {
			$criteria = clone $criteria;
		}

		if ($distinct) {
			$criteria->setDistinct();
		}

		$count = null;

		if ($this->collFotosRelatedByAlternativeAlbumId === null) {
			if ($this->isNew()) {
				$count = 0;
			} else {

				$criteria->add(FotoPeer::ALTERNATIVE_ALBUM_ID, $this->id);

				$count = FotoPeer::doCount($criteria, $con);
			}
		} else {
			// criteria has no effect for a new object
			if (!$this->isNew()) {
				// the following code is to determine if a new query is
				// called for.  If the criteria is the same as the last
				// one, just return count of the collection.


				$criteria->add(FotoPeer::ALTERNATIVE_ALBUM_ID, $this->id);

				if (!isset($this->lastFotoRelatedByAlternativeAlbumIdCriteria) || !$this->lastFotoRelatedByAlternativeAlbumIdCriteria->equals($criteria)) {
					$count = FotoPeer::doCount($criteria, $con);
				} else {
					$count = count($this->collFotosRelatedByAlternativeAlbumId);
				}
			} else {
				$count = count($this->collFotosRelatedByAlternativeAlbumId);
			}
		}
		$this->lastFotoRelatedByAlternativeAlbumIdCriteria = $criteria;
		return $count;
	}

	/**
	 * Method called to associate a Foto object to this object
	 * through the Foto foreign key attribute.
	 *
	 * @param      Foto $l Foto
	 * @return     void
	 * @throws     PropelException
	 */
	public function addFotoRelatedByAlternativeAlbumId(Foto $l)
	{
		if ($this->collFotosRelatedByAlternativeAlbumId === null) {
			$this->initFotosRelatedByAlternativeAlbumId();
		}
		if (!in_array($l, $this->collFotosRelatedByAlternativeAlbumId, true)) { // only add it if the **same** object is not already associated
			array_push($this->collFotosRelatedByAlternativeAlbumId, $l);
			$l->setAlbumRelatedByAlternativeAlbumId($this);
		}
	}


	/**
	 * If this collection has already been initialized with
	 * an identical criteria, it returns the collection.
	 * Otherwise if this Album is new, it will return
	 * an empty collection; or if this Album has previously
	 * been saved, it will retrieve related FotosRelatedByAlternativeAlbumId from storage.
	 *
	 * This method is protected by default in order to keep the public
	 * api reasonable.  You can provide public methods for those you
	 * actually need in Album.
	 */
	public function getFotosRelatedByAlternativeAlbumIdJoinUserRelatedByOwnerFirstname($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
		if ($criteria === null) {
			$criteria = new Criteria(AlbumPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collFotosRelatedByAlternativeAlbumId === null) {
			if ($this->isNew()) {
				$this->collFotosRelatedByAlternativeAlbumId = array();
			} else {

				$criteria->add(FotoPeer::ALTERNATIVE_ALBUM_ID, $this->id);

				$this->collFotosRelatedByAlternativeAlbumId = FotoPeer::doSelectJoinUserRelatedByOwnerFirstname($criteria, $con, $join_behavior);
			}
		} else {
			// the following code is to determine if a new query is
			// called for.  If the criteria is the same as the last
			// one, just return the collection.

			$criteria->add(FotoPeer::ALTERNATIVE_ALBUM_ID, $this->id);

			if (!isset($this->lastFotoRelatedByAlternativeAlbumIdCriteria) || !$this->lastFotoRelatedByAlternativeAlbumIdCriteria->equals($criteria)) {
				$this->collFotosRelatedByAlternativeAlbumId = FotoPeer::doSelectJoinUserRelatedByOwnerFirstname($criteria, $con, $join_behavior);
			}
		}
		$this->lastFotoRelatedByAlternativeAlbumIdCriteria = $criteria;

		return $this->collFotosRelatedByAlternativeAlbumId;
	}


	/**
	 * If this collection has already been initialized with
	 * an identical criteria, it returns the collection.
	 * Otherwise if this Album is new, it will return
	 * an empty collection; or if this Album has previously
	 * been saved, it will retrieve related FotosRelatedByAlternativeAlbumId from storage.
	 *
	 * This method is protected by default in order to keep the public
	 * api reasonable.  You can provide public methods for those you
	 * actually need in Album.
	 */
	public function getFotosRelatedByAlternativeAlbumIdJoinUserRelatedByOwnerLastname($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
		if ($criteria === null) {
			$criteria = new Criteria(AlbumPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collFotosRelatedByAlternativeAlbumId === null) {
			if ($this->isNew()) {
				$this->collFotosRelatedByAlternativeAlbumId = array();
			} else {

				$criteria->add(FotoPeer::ALTERNATIVE_ALBUM_ID, $this->id);

				$this->collFotosRelatedByAlternativeAlbumId = FotoPeer::doSelectJoinUserRelatedByOwnerLastname($criteria, $con, $join_behavior);
			}
		} else {
			// the following code is to determine if a new query is
			// called for.  If the criteria is the same as the last
			// one, just return the collection.

			$criteria->add(FotoPeer::ALTERNATIVE_ALBUM_ID, $this->id);

			if (!isset($this->lastFotoRelatedByAlternativeAlbumIdCriteria) || !$this->lastFotoRelatedByAlternativeAlbumIdCriteria->equals($criteria)) {
				$this->collFotosRelatedByAlternativeAlbumId = FotoPeer::doSelectJoinUserRelatedByOwnerLastname($criteria, $con, $join_behavior);
			}
		}
		$this->lastFotoRelatedByAlternativeAlbumIdCriteria = $criteria;

		return $this->collFotosRelatedByAlternativeAlbumId;
	}

	/**
	 * Resets all collections of referencing foreign keys.
	 *
	 * This method is a user-space workaround for PHP's inability to garbage collect objects
	 * with circular references.  This is currently necessary when using Propel in certain
	 * daemon or large-volumne/high-memory operations.
	 *
	 * @param      boolean $deep Whether to also clear the references on all associated objects.
	 */
	public function clearAllReferences($deep = false)
	{
		if ($deep) {
			if ($this->collAlbumsRelatedByAlbumId) {
				foreach ((array) $this->collAlbumsRelatedByAlbumId as $o) {
					$o->clearAllReferences($deep);
				}
			}
			if ($this->collFotosRelatedByAlbumId) {
				foreach ((array) $this->collFotosRelatedByAlbumId as $o) {
					$o->clearAllReferences($deep);
				}
			}
			if ($this->collFotosRelatedByAlternativeAlbumId) {
				foreach ((array) $this->collFotosRelatedByAlternativeAlbumId as $o) {
					$o->clearAllReferences($deep);
				}
			}
		} // if ($deep)

		$this->collAlbumsRelatedByAlbumId = null;
		$this->collFotosRelatedByAlbumId = null;
		$this->collFotosRelatedByAlternativeAlbumId = null;
			$this->aAlbumRelatedByAlbumId = null;
	}


  /**
   * Returns if the Custom Column has been set.
   *
   * @param string $key The name of the custom column
   *
   * @return bool       True is the custom column has been set
   */
  public function hasCustomColumn($key)
  {
    return isset($this->customColumns[$key]);
  }


  /**
   * Returns the Custom Column of a hydrated result.
   *
   * @param string $key The name of the custom column
   *
   * @return mixed      The value of the custom column
   */
  public function getCustomColumnValue($key)
  {
    return $this->customColumns[$key];
  }

  /**
   * Sets the culture.
   *
   * @param string $key  The name of the custom column
   * @param mixed $value The value from the custom column
   *
   * @return void
   */
  public function setCustomColumnValue($key, $value)
  {
    $this->customColumns[$key] = $value;
  }

  /**
   * Hydrates (populates) the custom columns with (lef over) values from the database resultset.
   *
   * An offset (0-based "start column") is specified so that objects can be hydrated
   * with a subset of the columns in the resultset rows.  This is needed, since the previous
   * rows are from the already hydrated objects.
   *
   * @param      array $row The row returned by PDOStatement->fetch(PDO::FETCH_NUM)
   * @param      int $startcol 0-based offset column which indicates which restultset column to start with.
   * @param      Criteria $criteria
   * @throws     PropelException  - Any caught Exception will be rewrapped as a PropelException.
   */
  public function hydrateCustomColumns($row, $startcol, Criteria $criteria)
  {
    $attributeNames = array_merge($criteria->getSelectColumns(), array_keys($criteria->getAsColumns()));

    for ($i=$startcol; $i<count($attributeNames); $i++)
    {
      //replace dots with underscores
      $attributeName = str_replace('.', '_', $attributeNames[$i]);

      // dynamically add attributes
      $this->setCustomColumnValue($attributeName, $row[$i]);
    }
  }

} // BaseAlbum
