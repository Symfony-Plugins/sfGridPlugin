<?php

/**
 * Base class that represents a row from the 'foto' table.
 *
 * 
 *
 * @package    lib.model.om
 */
abstract class BaseFoto extends BaseObject  implements Persistent {


  const PEER = 'FotoPeer';

	/**
	 * The Peer class.
	 * Instance provides a convenient way of calling static methods on a class
	 * that calling code may not be able to identify.
	 * @var        FotoPeer
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
	 * The value for the alternative_album_id field.
	 * @var        int
	 */
	protected $alternative_album_id;

	/**
	 * The value for the filename field.
	 * @var        string
	 */
	protected $filename;

	/**
	 * The value for the title field.
	 * @var        string
	 */
	protected $title;

	/**
	 * The value for the description field.
	 * @var        string
	 */
	protected $description;

	/**
	 * The value for the owner_firstname field.
	 * @var        string
	 */
	protected $owner_firstname;

	/**
	 * The value for the owner_lastname field.
	 * @var        string
	 */
	protected $owner_lastname;

	/**
	 * @var        Album
	 */
	protected $aAlbumRelatedByAlbumId;

	/**
	 * @var        Album
	 */
	protected $aAlbumRelatedByAlternativeAlbumId;

	/**
	 * @var        User
	 */
	protected $aUserRelatedByOwnerFirstname;

	/**
	 * @var        User
	 */
	protected $aUserRelatedByOwnerLastname;

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
	 * Initializes internal state of BaseFoto object.
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
	 * Get the [alternative_album_id] column value.
	 * 
	 * @return     int
	 */
	public function getAlternativeAlbumId()
	{
		return $this->alternative_album_id;
	}

	/**
	 * Get the [filename] column value.
	 * 
	 * @return     string
	 */
	public function getFilename()
	{
		return $this->filename;
	}

	/**
	 * Get the [title] column value.
	 * 
	 * @return     string
	 */
	public function getTitle()
	{
		return $this->title;
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
	 * Get the [owner_firstname] column value.
	 * 
	 * @return     string
	 */
	public function getOwnerFirstname()
	{
		return $this->owner_firstname;
	}

	/**
	 * Get the [owner_lastname] column value.
	 * 
	 * @return     string
	 */
	public function getOwnerLastname()
	{
		return $this->owner_lastname;
	}

	/**
	 * Set the value of [id] column.
	 * 
	 * @param      int $v new value
	 * @return     Foto The current object (for fluent API support)
	 */
	public function setId($v)
	{
		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->id !== $v) {
			$this->id = $v;
			$this->modifiedColumns[] = FotoPeer::ID;
		}

		return $this;
	} // setId()

	/**
	 * Set the value of [album_id] column.
	 * 
	 * @param      int $v new value
	 * @return     Foto The current object (for fluent API support)
	 */
	public function setAlbumId($v)
	{
		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->album_id !== $v) {
			$this->album_id = $v;
			$this->modifiedColumns[] = FotoPeer::ALBUM_ID;
		}

		if ($this->aAlbumRelatedByAlbumId !== null && $this->aAlbumRelatedByAlbumId->getId() !== $v) {
			$this->aAlbumRelatedByAlbumId = null;
		}

		return $this;
	} // setAlbumId()

	/**
	 * Set the value of [alternative_album_id] column.
	 * 
	 * @param      int $v new value
	 * @return     Foto The current object (for fluent API support)
	 */
	public function setAlternativeAlbumId($v)
	{
		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->alternative_album_id !== $v) {
			$this->alternative_album_id = $v;
			$this->modifiedColumns[] = FotoPeer::ALTERNATIVE_ALBUM_ID;
		}

		if ($this->aAlbumRelatedByAlternativeAlbumId !== null && $this->aAlbumRelatedByAlternativeAlbumId->getId() !== $v) {
			$this->aAlbumRelatedByAlternativeAlbumId = null;
		}

		return $this;
	} // setAlternativeAlbumId()

	/**
	 * Set the value of [filename] column.
	 * 
	 * @param      string $v new value
	 * @return     Foto The current object (for fluent API support)
	 */
	public function setFilename($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->filename !== $v) {
			$this->filename = $v;
			$this->modifiedColumns[] = FotoPeer::FILENAME;
		}

		return $this;
	} // setFilename()

	/**
	 * Set the value of [title] column.
	 * 
	 * @param      string $v new value
	 * @return     Foto The current object (for fluent API support)
	 */
	public function setTitle($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->title !== $v) {
			$this->title = $v;
			$this->modifiedColumns[] = FotoPeer::TITLE;
		}

		return $this;
	} // setTitle()

	/**
	 * Set the value of [description] column.
	 * 
	 * @param      string $v new value
	 * @return     Foto The current object (for fluent API support)
	 */
	public function setDescription($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->description !== $v) {
			$this->description = $v;
			$this->modifiedColumns[] = FotoPeer::DESCRIPTION;
		}

		return $this;
	} // setDescription()

	/**
	 * Set the value of [owner_firstname] column.
	 * 
	 * @param      string $v new value
	 * @return     Foto The current object (for fluent API support)
	 */
	public function setOwnerFirstname($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->owner_firstname !== $v) {
			$this->owner_firstname = $v;
			$this->modifiedColumns[] = FotoPeer::OWNER_FIRSTNAME;
		}

		if ($this->aUserRelatedByOwnerFirstname !== null && $this->aUserRelatedByOwnerFirstname->getFirstname() !== $v) {
			$this->aUserRelatedByOwnerFirstname = null;
		}

		return $this;
	} // setOwnerFirstname()

	/**
	 * Set the value of [owner_lastname] column.
	 * 
	 * @param      string $v new value
	 * @return     Foto The current object (for fluent API support)
	 */
	public function setOwnerLastname($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->owner_lastname !== $v) {
			$this->owner_lastname = $v;
			$this->modifiedColumns[] = FotoPeer::OWNER_LASTNAME;
		}

		if ($this->aUserRelatedByOwnerLastname !== null && $this->aUserRelatedByOwnerLastname->getLastname() !== $v) {
			$this->aUserRelatedByOwnerLastname = null;
		}

		return $this;
	} // setOwnerLastname()

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
			$this->alternative_album_id = ($row[$startcol + 2] !== null) ? (int) $row[$startcol + 2] : null;
			$this->filename = ($row[$startcol + 3] !== null) ? (string) $row[$startcol + 3] : null;
			$this->title = ($row[$startcol + 4] !== null) ? (string) $row[$startcol + 4] : null;
			$this->description = ($row[$startcol + 5] !== null) ? (string) $row[$startcol + 5] : null;
			$this->owner_firstname = ($row[$startcol + 6] !== null) ? (string) $row[$startcol + 6] : null;
			$this->owner_lastname = ($row[$startcol + 7] !== null) ? (string) $row[$startcol + 7] : null;
			$this->resetModified();

			$this->setNew(false);

			if ($rehydrate) {
				$this->ensureConsistency();
			}

			// FIXME - using NUM_COLUMNS may be clearer.
			return $startcol + 8; // 8 = FotoPeer::NUM_COLUMNS - FotoPeer::NUM_LAZY_LOAD_COLUMNS).

		} catch (Exception $e) {
			throw new PropelException("Error populating Foto object", $e);
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
		if ($this->aAlbumRelatedByAlternativeAlbumId !== null && $this->alternative_album_id !== $this->aAlbumRelatedByAlternativeAlbumId->getId()) {
			$this->aAlbumRelatedByAlternativeAlbumId = null;
		}
		if ($this->aUserRelatedByOwnerFirstname !== null && $this->owner_firstname !== $this->aUserRelatedByOwnerFirstname->getFirstname()) {
			$this->aUserRelatedByOwnerFirstname = null;
		}
		if ($this->aUserRelatedByOwnerLastname !== null && $this->owner_lastname !== $this->aUserRelatedByOwnerLastname->getLastname()) {
			$this->aUserRelatedByOwnerLastname = null;
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
			$con = Propel::getConnection(FotoPeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		// We don't need to alter the object instance pool; we're just modifying this instance
		// already in the pool.

		$stmt = FotoPeer::doSelectStmt($this->buildPkeyCriteria(), $con);
		$row = $stmt->fetch(PDO::FETCH_NUM);
		$stmt->closeCursor();
		if (!$row) {
			throw new PropelException('Cannot find matching row in the database to reload object values.');
		}
		$this->hydrate($row, 0, true); // rehydrate

		if ($deep) {  // also de-associate any related objects?

			$this->aAlbumRelatedByAlbumId = null;
			$this->aAlbumRelatedByAlternativeAlbumId = null;
			$this->aUserRelatedByOwnerFirstname = null;
			$this->aUserRelatedByOwnerLastname = null;
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
			$con = Propel::getConnection(FotoPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}
		
		$con->beginTransaction();
		try {
			FotoPeer::doDelete($this, $con);
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
			$con = Propel::getConnection(FotoPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}
		
		$con->beginTransaction();
		try {
			$affectedRows = $this->doSave($con);
			$con->commit();
			FotoPeer::addInstanceToPool($this);
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

			if ($this->aAlbumRelatedByAlternativeAlbumId !== null) {
				if ($this->aAlbumRelatedByAlternativeAlbumId->isModified() || $this->aAlbumRelatedByAlternativeAlbumId->isNew()) {
					$affectedRows += $this->aAlbumRelatedByAlternativeAlbumId->save($con);
				}
				$this->setAlbumRelatedByAlternativeAlbumId($this->aAlbumRelatedByAlternativeAlbumId);
			}

			if ($this->aUserRelatedByOwnerFirstname !== null) {
				if ($this->aUserRelatedByOwnerFirstname->isModified() || $this->aUserRelatedByOwnerFirstname->isNew()) {
					$affectedRows += $this->aUserRelatedByOwnerFirstname->save($con);
				}
				$this->setUserRelatedByOwnerFirstname($this->aUserRelatedByOwnerFirstname);
			}

			if ($this->aUserRelatedByOwnerLastname !== null) {
				if ($this->aUserRelatedByOwnerLastname->isModified() || $this->aUserRelatedByOwnerLastname->isNew()) {
					$affectedRows += $this->aUserRelatedByOwnerLastname->save($con);
				}
				$this->setUserRelatedByOwnerLastname($this->aUserRelatedByOwnerLastname);
			}

			if ($this->isNew() ) {
				$this->modifiedColumns[] = FotoPeer::ID;
			}

			// If this object has been modified, then save it to the database.
			if ($this->isModified()) {
				if ($this->isNew()) {
					$pk = FotoPeer::doInsert($this, $con);
					$affectedRows += 1; // we are assuming that there is only 1 row per doInsert() which
										 // should always be true here (even though technically
										 // BasePeer::doInsert() can insert multiple rows).

					$this->setId($pk);  //[IMV] update autoincrement primary key

					$this->setNew(false);
				} else {
					$affectedRows += FotoPeer::doUpdate($this, $con);
				}

				$this->resetModified(); // [HL] After being saved an object is no longer 'modified'
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

			if ($this->aAlbumRelatedByAlternativeAlbumId !== null) {
				if (!$this->aAlbumRelatedByAlternativeAlbumId->validate($columns)) {
					$failureMap = array_merge($failureMap, $this->aAlbumRelatedByAlternativeAlbumId->getValidationFailures());
				}
			}

			if ($this->aUserRelatedByOwnerFirstname !== null) {
				if (!$this->aUserRelatedByOwnerFirstname->validate($columns)) {
					$failureMap = array_merge($failureMap, $this->aUserRelatedByOwnerFirstname->getValidationFailures());
				}
			}

			if ($this->aUserRelatedByOwnerLastname !== null) {
				if (!$this->aUserRelatedByOwnerLastname->validate($columns)) {
					$failureMap = array_merge($failureMap, $this->aUserRelatedByOwnerLastname->getValidationFailures());
				}
			}


			if (($retval = FotoPeer::doValidate($this, $columns)) !== true) {
				$failureMap = array_merge($failureMap, $retval);
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
		$pos = FotoPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
				return $this->getAlternativeAlbumId();
				break;
			case 3:
				return $this->getFilename();
				break;
			case 4:
				return $this->getTitle();
				break;
			case 5:
				return $this->getDescription();
				break;
			case 6:
				return $this->getOwnerFirstname();
				break;
			case 7:
				return $this->getOwnerLastname();
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
		$keys = FotoPeer::getFieldNames($keyType);
		$result = array(
			$keys[0] => $this->getId(),
			$keys[1] => $this->getAlbumId(),
			$keys[2] => $this->getAlternativeAlbumId(),
			$keys[3] => $this->getFilename(),
			$keys[4] => $this->getTitle(),
			$keys[5] => $this->getDescription(),
			$keys[6] => $this->getOwnerFirstname(),
			$keys[7] => $this->getOwnerLastname(),
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
		$pos = FotoPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
				$this->setAlternativeAlbumId($value);
				break;
			case 3:
				$this->setFilename($value);
				break;
			case 4:
				$this->setTitle($value);
				break;
			case 5:
				$this->setDescription($value);
				break;
			case 6:
				$this->setOwnerFirstname($value);
				break;
			case 7:
				$this->setOwnerLastname($value);
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
		$keys = FotoPeer::getFieldNames($keyType);

		if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
		if (array_key_exists($keys[1], $arr)) $this->setAlbumId($arr[$keys[1]]);
		if (array_key_exists($keys[2], $arr)) $this->setAlternativeAlbumId($arr[$keys[2]]);
		if (array_key_exists($keys[3], $arr)) $this->setFilename($arr[$keys[3]]);
		if (array_key_exists($keys[4], $arr)) $this->setTitle($arr[$keys[4]]);
		if (array_key_exists($keys[5], $arr)) $this->setDescription($arr[$keys[5]]);
		if (array_key_exists($keys[6], $arr)) $this->setOwnerFirstname($arr[$keys[6]]);
		if (array_key_exists($keys[7], $arr)) $this->setOwnerLastname($arr[$keys[7]]);
	}

	/**
	 * Build a Criteria object containing the values of all modified columns in this object.
	 *
	 * @return     Criteria The Criteria object containing all modified values.
	 */
	public function buildCriteria()
	{
		$criteria = new Criteria(FotoPeer::DATABASE_NAME);

		if ($this->isColumnModified(FotoPeer::ID)) $criteria->add(FotoPeer::ID, $this->id);
		if ($this->isColumnModified(FotoPeer::ALBUM_ID)) $criteria->add(FotoPeer::ALBUM_ID, $this->album_id);
		if ($this->isColumnModified(FotoPeer::ALTERNATIVE_ALBUM_ID)) $criteria->add(FotoPeer::ALTERNATIVE_ALBUM_ID, $this->alternative_album_id);
		if ($this->isColumnModified(FotoPeer::FILENAME)) $criteria->add(FotoPeer::FILENAME, $this->filename);
		if ($this->isColumnModified(FotoPeer::TITLE)) $criteria->add(FotoPeer::TITLE, $this->title);
		if ($this->isColumnModified(FotoPeer::DESCRIPTION)) $criteria->add(FotoPeer::DESCRIPTION, $this->description);
		if ($this->isColumnModified(FotoPeer::OWNER_FIRSTNAME)) $criteria->add(FotoPeer::OWNER_FIRSTNAME, $this->owner_firstname);
		if ($this->isColumnModified(FotoPeer::OWNER_LASTNAME)) $criteria->add(FotoPeer::OWNER_LASTNAME, $this->owner_lastname);

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
		$criteria = new Criteria(FotoPeer::DATABASE_NAME);

		$criteria->add(FotoPeer::ID, $this->id);

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
	 * @param      object $copyObj An object of Foto (or compatible) type.
	 * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
	 * @throws     PropelException
	 */
	public function copyInto($copyObj, $deepCopy = false)
	{

		$copyObj->setAlbumId($this->album_id);

		$copyObj->setAlternativeAlbumId($this->alternative_album_id);

		$copyObj->setFilename($this->filename);

		$copyObj->setTitle($this->title);

		$copyObj->setDescription($this->description);

		$copyObj->setOwnerFirstname($this->owner_firstname);

		$copyObj->setOwnerLastname($this->owner_lastname);


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
	 * @return     Foto Clone of current object.
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
	 * @return     FotoPeer
	 */
	public function getPeer()
	{
		if (self::$peer === null) {
			self::$peer = new FotoPeer();
		}
		return self::$peer;
	}

	/**
	 * Declares an association between this object and a Album object.
	 *
	 * @param      Album $v
	 * @return     Foto The current object (for fluent API support)
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
			$v->addFotoRelatedByAlbumId($this);
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
			   $this->aAlbumRelatedByAlbumId->addFotosRelatedByAlbumId($this);
			 */
		}
		return $this->aAlbumRelatedByAlbumId;
	}

	/**
	 * Declares an association between this object and a Album object.
	 *
	 * @param      Album $v
	 * @return     Foto The current object (for fluent API support)
	 * @throws     PropelException
	 */
	public function setAlbumRelatedByAlternativeAlbumId(Album $v = null)
	{
		if ($v === null) {
			$this->setAlternativeAlbumId(NULL);
		} else {
			$this->setAlternativeAlbumId($v->getId());
		}

		$this->aAlbumRelatedByAlternativeAlbumId = $v;

		// Add binding for other direction of this n:n relationship.
		// If this object has already been added to the Album object, it will not be re-added.
		if ($v !== null) {
			$v->addFotoRelatedByAlternativeAlbumId($this);
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
	public function getAlbumRelatedByAlternativeAlbumId(PropelPDO $con = null)
	{
		if ($this->aAlbumRelatedByAlternativeAlbumId === null && ($this->alternative_album_id !== null)) {
			$c = new Criteria(AlbumPeer::DATABASE_NAME);
			$c->add(AlbumPeer::ID, $this->alternative_album_id);
			$this->aAlbumRelatedByAlternativeAlbumId = AlbumPeer::doSelectOne($c, $con);
			/* The following can be used additionally to
			   guarantee the related object contains a reference
			   to this object.  This level of coupling may, however, be
			   undesirable since it could result in an only partially populated collection
			   in the referenced object.
			   $this->aAlbumRelatedByAlternativeAlbumId->addFotosRelatedByAlternativeAlbumId($this);
			 */
		}
		return $this->aAlbumRelatedByAlternativeAlbumId;
	}

	/**
	 * Declares an association between this object and a User object.
	 *
	 * @param      User $v
	 * @return     Foto The current object (for fluent API support)
	 * @throws     PropelException
	 */
	public function setUserRelatedByOwnerFirstname(User $v = null)
	{
		if ($v === null) {
			$this->setOwnerFirstname(NULL);
		} else {
			$this->setOwnerFirstname($v->getFirstname());
		}

		$this->aUserRelatedByOwnerFirstname = $v;

		// Add binding for other direction of this n:n relationship.
		// If this object has already been added to the User object, it will not be re-added.
		if ($v !== null) {
			$v->addFotoRelatedByOwnerFirstname($this);
		}

		return $this;
	}


	/**
	 * Get the associated User object
	 *
	 * @param      PropelPDO Optional Connection object.
	 * @return     User The associated User object.
	 * @throws     PropelException
	 */
	public function getUserRelatedByOwnerFirstname(PropelPDO $con = null)
	{
		if ($this->aUserRelatedByOwnerFirstname === null && (($this->owner_firstname !== "" && $this->owner_firstname !== null))) {
			$c = new Criteria(UserPeer::DATABASE_NAME);
			$c->add(UserPeer::FIRSTNAME, $this->owner_firstname);
			$this->aUserRelatedByOwnerFirstname = UserPeer::doSelectOne($c, $con);
			/* The following can be used additionally to
			   guarantee the related object contains a reference
			   to this object.  This level of coupling may, however, be
			   undesirable since it could result in an only partially populated collection
			   in the referenced object.
			   $this->aUserRelatedByOwnerFirstname->addFotosRelatedByOwnerFirstname($this);
			 */
		}
		return $this->aUserRelatedByOwnerFirstname;
	}

	/**
	 * Declares an association between this object and a User object.
	 *
	 * @param      User $v
	 * @return     Foto The current object (for fluent API support)
	 * @throws     PropelException
	 */
	public function setUserRelatedByOwnerLastname(User $v = null)
	{
		if ($v === null) {
			$this->setOwnerLastname(NULL);
		} else {
			$this->setOwnerLastname($v->getLastname());
		}

		$this->aUserRelatedByOwnerLastname = $v;

		// Add binding for other direction of this n:n relationship.
		// If this object has already been added to the User object, it will not be re-added.
		if ($v !== null) {
			$v->addFotoRelatedByOwnerLastname($this);
		}

		return $this;
	}


	/**
	 * Get the associated User object
	 *
	 * @param      PropelPDO Optional Connection object.
	 * @return     User The associated User object.
	 * @throws     PropelException
	 */
	public function getUserRelatedByOwnerLastname(PropelPDO $con = null)
	{
		if ($this->aUserRelatedByOwnerLastname === null && (($this->owner_lastname !== "" && $this->owner_lastname !== null))) {
			$c = new Criteria(UserPeer::DATABASE_NAME);
			$c->add(UserPeer::LASTNAME, $this->owner_lastname);
			$this->aUserRelatedByOwnerLastname = UserPeer::doSelectOne($c, $con);
			/* The following can be used additionally to
			   guarantee the related object contains a reference
			   to this object.  This level of coupling may, however, be
			   undesirable since it could result in an only partially populated collection
			   in the referenced object.
			   $this->aUserRelatedByOwnerLastname->addFotosRelatedByOwnerLastname($this);
			 */
		}
		return $this->aUserRelatedByOwnerLastname;
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
		} // if ($deep)

			$this->aAlbumRelatedByAlbumId = null;
			$this->aAlbumRelatedByAlternativeAlbumId = null;
			$this->aUserRelatedByOwnerFirstname = null;
			$this->aUserRelatedByOwnerLastname = null;
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

} // BaseFoto
