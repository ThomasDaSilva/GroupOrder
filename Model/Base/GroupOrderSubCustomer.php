<?php

namespace GroupOrder\Model\Base;

use \Exception;
use \PDO;
use GroupOrder\Model\GroupOrderCartItem as ChildGroupOrderCartItem;
use GroupOrder\Model\GroupOrderCartItemQuery as ChildGroupOrderCartItemQuery;
use GroupOrder\Model\GroupOrderMainCustomer as ChildGroupOrderMainCustomer;
use GroupOrder\Model\GroupOrderMainCustomerQuery as ChildGroupOrderMainCustomerQuery;
use GroupOrder\Model\GroupOrderSubCustomer as ChildGroupOrderSubCustomer;
use GroupOrder\Model\GroupOrderSubCustomerQuery as ChildGroupOrderSubCustomerQuery;
use GroupOrder\Model\GroupOrderSubOrder as ChildGroupOrderSubOrder;
use GroupOrder\Model\GroupOrderSubOrderQuery as ChildGroupOrderSubOrderQuery;
use GroupOrder\Model\Map\GroupOrderSubCustomerTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;
use Thelia\Model\Country as ChildCountry;
use Thelia\Model\CountryQuery;

abstract class GroupOrderSubCustomer implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\GroupOrder\\Model\\Map\\GroupOrderSubCustomerTableMap';


    /**
     * attribute to determine if this object has previously been saved.
     * @var boolean
     */
    protected $new = true;

    /**
     * attribute to determine whether this object has been deleted.
     * @var boolean
     */
    protected $deleted = false;

    /**
     * The columns that have been modified in current object.
     * Tracking modified columns allows us to only update modified columns.
     * @var array
     */
    protected $modifiedColumns = array();

    /**
     * The (virtual) columns that are added at runtime
     * The formatters can add supplementary columns based on a resultset
     * @var array
     */
    protected $virtualColumns = array();

    /**
     * The value for the id field.
     * @var        int
     */
    protected $id;

    /**
     * The value for the main_customer_id field.
     * @var        int
     */
    protected $main_customer_id;

    /**
     * The value for the first_name field.
     * @var        string
     */
    protected $first_name;

    /**
     * The value for the last_name field.
     * @var        string
     */
    protected $last_name;

    /**
     * The value for the email field.
     * @var        string
     */
    protected $email;

    /**
     * The value for the address1 field.
     * @var        string
     */
    protected $address1;

    /**
     * The value for the address2 field.
     * @var        string
     */
    protected $address2;

    /**
     * The value for the address3 field.
     * @var        string
     */
    protected $address3;

    /**
     * The value for the city field.
     * @var        string
     */
    protected $city;

    /**
     * The value for the zip_code field.
     * @var        string
     */
    protected $zip_code;

    /**
     * The value for the country_id field.
     * @var        int
     */
    protected $country_id;

    /**
     * The value for the login field.
     * @var        string
     */
    protected $login;

    /**
     * The value for the password field.
     * @var        string
     */
    protected $password;

    /**
     * @var        GroupOrderMainCustomer
     */
    protected $aGroupOrderMainCustomer;

    /**
     * @var        Country
     */
    protected $aCountry;

    /**
     * @var        ObjectCollection|ChildGroupOrderSubOrder[] Collection to store aggregation of ChildGroupOrderSubOrder objects.
     */
    protected $collGroupOrderSubOrders;
    protected $collGroupOrderSubOrdersPartial;

    /**
     * @var        ObjectCollection|ChildGroupOrderCartItem[] Collection to store aggregation of ChildGroupOrderCartItem objects.
     */
    protected $collGroupOrderCartItems;
    protected $collGroupOrderCartItemsPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var boolean
     */
    protected $alreadyInSave = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection
     */
    protected $groupOrderSubOrdersScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection
     */
    protected $groupOrderCartItemsScheduledForDeletion = null;

    /**
     * Initializes internal state of GroupOrder\Model\Base\GroupOrderSubCustomer object.
     */
    public function __construct()
    {
    }

    /**
     * Returns whether the object has been modified.
     *
     * @return boolean True if the object has been modified.
     */
    public function isModified()
    {
        return !!$this->modifiedColumns;
    }

    /**
     * Has specified column been modified?
     *
     * @param  string  $col column fully qualified name (TableMap::TYPE_COLNAME), e.g. Book::AUTHOR_ID
     * @return boolean True if $col has been modified.
     */
    public function isColumnModified($col)
    {
        return $this->modifiedColumns && isset($this->modifiedColumns[$col]);
    }

    /**
     * Get the columns that have been modified in this object.
     * @return array A unique list of the modified column names for this object.
     */
    public function getModifiedColumns()
    {
        return $this->modifiedColumns ? array_keys($this->modifiedColumns) : [];
    }

    /**
     * Returns whether the object has ever been saved.  This will
     * be false, if the object was retrieved from storage or was created
     * and then saved.
     *
     * @return boolean true, if the object has never been persisted.
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * Setter for the isNew attribute.  This method will be called
     * by Propel-generated children and objects.
     *
     * @param boolean $b the state of the object.
     */
    public function setNew($b)
    {
        $this->new = (Boolean) $b;
    }

    /**
     * Whether this object has been deleted.
     * @return boolean The deleted state of this object.
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Specify whether this object has been deleted.
     * @param  boolean $b The deleted state of this object.
     * @return void
     */
    public function setDeleted($b)
    {
        $this->deleted = (Boolean) $b;
    }

    /**
     * Sets the modified state for the object to be false.
     * @param  string $col If supplied, only the specified column is reset.
     * @return void
     */
    public function resetModified($col = null)
    {
        if (null !== $col) {
            if (isset($this->modifiedColumns[$col])) {
                unset($this->modifiedColumns[$col]);
            }
        } else {
            $this->modifiedColumns = array();
        }
    }

    /**
     * Compares this with another <code>GroupOrderSubCustomer</code> instance.  If
     * <code>obj</code> is an instance of <code>GroupOrderSubCustomer</code>, delegates to
     * <code>equals(GroupOrderSubCustomer)</code>.  Otherwise, returns <code>false</code>.
     *
     * @param  mixed   $obj The object to compare to.
     * @return boolean Whether equal to the object specified.
     */
    public function equals($obj)
    {
        $thisclazz = get_class($this);
        if (!is_object($obj) || !($obj instanceof $thisclazz)) {
            return false;
        }

        if ($this === $obj) {
            return true;
        }

        if (null === $this->getPrimaryKey()
            || null === $obj->getPrimaryKey())  {
            return false;
        }

        return $this->getPrimaryKey() === $obj->getPrimaryKey();
    }

    /**
     * If the primary key is not null, return the hashcode of the
     * primary key. Otherwise, return the hash code of the object.
     *
     * @return int Hashcode
     */
    public function hashCode()
    {
        if (null !== $this->getPrimaryKey()) {
            return crc32(serialize($this->getPrimaryKey()));
        }

        return crc32(serialize(clone $this));
    }

    /**
     * Get the associative array of the virtual columns in this object
     *
     * @return array
     */
    public function getVirtualColumns()
    {
        return $this->virtualColumns;
    }

    /**
     * Checks the existence of a virtual column in this object
     *
     * @param  string  $name The virtual column name
     * @return boolean
     */
    public function hasVirtualColumn($name)
    {
        return array_key_exists($name, $this->virtualColumns);
    }

    /**
     * Get the value of a virtual column in this object
     *
     * @param  string $name The virtual column name
     * @return mixed
     *
     * @throws PropelException
     */
    public function getVirtualColumn($name)
    {
        if (!$this->hasVirtualColumn($name)) {
            throw new PropelException(sprintf('Cannot get value of inexistent virtual column %s.', $name));
        }

        return $this->virtualColumns[$name];
    }

    /**
     * Set the value of a virtual column in this object
     *
     * @param string $name  The virtual column name
     * @param mixed  $value The value to give to the virtual column
     *
     * @return GroupOrderSubCustomer The current object, for fluid interface
     */
    public function setVirtualColumn($name, $value)
    {
        $this->virtualColumns[$name] = $value;

        return $this;
    }

    /**
     * Logs a message using Propel::log().
     *
     * @param  string  $msg
     * @param  int     $priority One of the Propel::LOG_* logging levels
     * @return boolean
     */
    protected function log($msg, $priority = Propel::LOG_INFO)
    {
        return Propel::log(get_class($this) . ': ' . $msg, $priority);
    }

    /**
     * Populate the current object from a string, using a given parser format
     * <code>
     * $book = new Book();
     * $book->importFrom('JSON', '{"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param mixed $parser A AbstractParser instance,
     *                       or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param string $data The source data to import from
     *
     * @return GroupOrderSubCustomer The current object, for fluid interface
     */
    public function importFrom($parser, $data)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        $this->fromArray($parser->toArray($data), TableMap::TYPE_PHPNAME);

        return $this;
    }

    /**
     * Export the current object properties to a string, using a given parser format
     * <code>
     * $book = BookQuery::create()->findPk(9012);
     * echo $book->exportTo('JSON');
     *  => {"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param  mixed   $parser                 A AbstractParser instance, or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param  boolean $includeLazyLoadColumns (optional) Whether to include lazy load(ed) columns. Defaults to TRUE.
     * @return string  The exported data
     */
    public function exportTo($parser, $includeLazyLoadColumns = true)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        return $parser->fromArray($this->toArray(TableMap::TYPE_PHPNAME, $includeLazyLoadColumns, array(), true));
    }

    /**
     * Clean up internal collections prior to serializing
     * Avoids recursive loops that turn into segmentation faults when serializing
     */
    public function __sleep()
    {
        $this->clearAllReferences();

        return array_keys(get_object_vars($this));
    }

    /**
     * Get the [id] column value.
     *
     * @return   int
     */
    public function getId()
    {

        return $this->id;
    }

    /**
     * Get the [main_customer_id] column value.
     *
     * @return   int
     */
    public function getMainCustomerId()
    {

        return $this->main_customer_id;
    }

    /**
     * Get the [first_name] column value.
     *
     * @return   string
     */
    public function getFirstName()
    {

        return $this->first_name;
    }

    /**
     * Get the [last_name] column value.
     *
     * @return   string
     */
    public function getLastName()
    {

        return $this->last_name;
    }

    /**
     * Get the [email] column value.
     *
     * @return   string
     */
    public function getEmail()
    {

        return $this->email;
    }

    /**
     * Get the [address1] column value.
     *
     * @return   string
     */
    public function getAddress1()
    {

        return $this->address1;
    }

    /**
     * Get the [address2] column value.
     *
     * @return   string
     */
    public function getAddress2()
    {

        return $this->address2;
    }

    /**
     * Get the [address3] column value.
     *
     * @return   string
     */
    public function getAddress3()
    {

        return $this->address3;
    }

    /**
     * Get the [city] column value.
     *
     * @return   string
     */
    public function getCity()
    {

        return $this->city;
    }

    /**
     * Get the [zip_code] column value.
     *
     * @return   string
     */
    public function getZipCode()
    {

        return $this->zip_code;
    }

    /**
     * Get the [country_id] column value.
     *
     * @return   int
     */
    public function getCountryId()
    {

        return $this->country_id;
    }

    /**
     * Get the [login] column value.
     *
     * @return   string
     */
    public function getLogin()
    {

        return $this->login;
    }

    /**
     * Get the [password] column value.
     *
     * @return   string
     */
    public function getPassword()
    {

        return $this->password;
    }

    /**
     * Set the value of [id] column.
     *
     * @param      int $v new value
     * @return   \GroupOrder\Model\GroupOrderSubCustomer The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[GroupOrderSubCustomerTableMap::ID] = true;
        }


        return $this;
    } // setId()

    /**
     * Set the value of [main_customer_id] column.
     *
     * @param      int $v new value
     * @return   \GroupOrder\Model\GroupOrderSubCustomer The current object (for fluent API support)
     */
    public function setMainCustomerId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->main_customer_id !== $v) {
            $this->main_customer_id = $v;
            $this->modifiedColumns[GroupOrderSubCustomerTableMap::MAIN_CUSTOMER_ID] = true;
        }

        if ($this->aGroupOrderMainCustomer !== null && $this->aGroupOrderMainCustomer->getId() !== $v) {
            $this->aGroupOrderMainCustomer = null;
        }


        return $this;
    } // setMainCustomerId()

    /**
     * Set the value of [first_name] column.
     *
     * @param      string $v new value
     * @return   \GroupOrder\Model\GroupOrderSubCustomer The current object (for fluent API support)
     */
    public function setFirstName($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->first_name !== $v) {
            $this->first_name = $v;
            $this->modifiedColumns[GroupOrderSubCustomerTableMap::FIRST_NAME] = true;
        }


        return $this;
    } // setFirstName()

    /**
     * Set the value of [last_name] column.
     *
     * @param      string $v new value
     * @return   \GroupOrder\Model\GroupOrderSubCustomer The current object (for fluent API support)
     */
    public function setLastName($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->last_name !== $v) {
            $this->last_name = $v;
            $this->modifiedColumns[GroupOrderSubCustomerTableMap::LAST_NAME] = true;
        }


        return $this;
    } // setLastName()

    /**
     * Set the value of [email] column.
     *
     * @param      string $v new value
     * @return   \GroupOrder\Model\GroupOrderSubCustomer The current object (for fluent API support)
     */
    public function setEmail($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->email !== $v) {
            $this->email = $v;
            $this->modifiedColumns[GroupOrderSubCustomerTableMap::EMAIL] = true;
        }


        return $this;
    } // setEmail()

    /**
     * Set the value of [address1] column.
     *
     * @param      string $v new value
     * @return   \GroupOrder\Model\GroupOrderSubCustomer The current object (for fluent API support)
     */
    public function setAddress1($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->address1 !== $v) {
            $this->address1 = $v;
            $this->modifiedColumns[GroupOrderSubCustomerTableMap::ADDRESS1] = true;
        }


        return $this;
    } // setAddress1()

    /**
     * Set the value of [address2] column.
     *
     * @param      string $v new value
     * @return   \GroupOrder\Model\GroupOrderSubCustomer The current object (for fluent API support)
     */
    public function setAddress2($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->address2 !== $v) {
            $this->address2 = $v;
            $this->modifiedColumns[GroupOrderSubCustomerTableMap::ADDRESS2] = true;
        }


        return $this;
    } // setAddress2()

    /**
     * Set the value of [address3] column.
     *
     * @param      string $v new value
     * @return   \GroupOrder\Model\GroupOrderSubCustomer The current object (for fluent API support)
     */
    public function setAddress3($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->address3 !== $v) {
            $this->address3 = $v;
            $this->modifiedColumns[GroupOrderSubCustomerTableMap::ADDRESS3] = true;
        }


        return $this;
    } // setAddress3()

    /**
     * Set the value of [city] column.
     *
     * @param      string $v new value
     * @return   \GroupOrder\Model\GroupOrderSubCustomer The current object (for fluent API support)
     */
    public function setCity($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->city !== $v) {
            $this->city = $v;
            $this->modifiedColumns[GroupOrderSubCustomerTableMap::CITY] = true;
        }


        return $this;
    } // setCity()

    /**
     * Set the value of [zip_code] column.
     *
     * @param      string $v new value
     * @return   \GroupOrder\Model\GroupOrderSubCustomer The current object (for fluent API support)
     */
    public function setZipCode($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->zip_code !== $v) {
            $this->zip_code = $v;
            $this->modifiedColumns[GroupOrderSubCustomerTableMap::ZIP_CODE] = true;
        }


        return $this;
    } // setZipCode()

    /**
     * Set the value of [country_id] column.
     *
     * @param      int $v new value
     * @return   \GroupOrder\Model\GroupOrderSubCustomer The current object (for fluent API support)
     */
    public function setCountryId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->country_id !== $v) {
            $this->country_id = $v;
            $this->modifiedColumns[GroupOrderSubCustomerTableMap::COUNTRY_ID] = true;
        }

        if ($this->aCountry !== null && $this->aCountry->getId() !== $v) {
            $this->aCountry = null;
        }


        return $this;
    } // setCountryId()

    /**
     * Set the value of [login] column.
     *
     * @param      string $v new value
     * @return   \GroupOrder\Model\GroupOrderSubCustomer The current object (for fluent API support)
     */
    public function setLogin($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->login !== $v) {
            $this->login = $v;
            $this->modifiedColumns[GroupOrderSubCustomerTableMap::LOGIN] = true;
        }


        return $this;
    } // setLogin()

    /**
     * Set the value of [password] column.
     *
     * @param      string $v new value
     * @return   \GroupOrder\Model\GroupOrderSubCustomer The current object (for fluent API support)
     */
    public function setPassword($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->password !== $v) {
            $this->password = $v;
            $this->modifiedColumns[GroupOrderSubCustomerTableMap::PASSWORD] = true;
        }


        return $this;
    } // setPassword()

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return boolean Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues()
    {
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
     * @param array   $row       The row returned by DataFetcher->fetch().
     * @param int     $startcol  0-based offset column which indicates which restultset column to start with.
     * @param boolean $rehydrate Whether this object is being re-hydrated from the database.
     * @param string  $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                  One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                            TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @return int             next starting column
     * @throws PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false, $indexType = TableMap::TYPE_NUM)
    {
        try {


            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : GroupOrderSubCustomerTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : GroupOrderSubCustomerTableMap::translateFieldName('MainCustomerId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->main_customer_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : GroupOrderSubCustomerTableMap::translateFieldName('FirstName', TableMap::TYPE_PHPNAME, $indexType)];
            $this->first_name = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : GroupOrderSubCustomerTableMap::translateFieldName('LastName', TableMap::TYPE_PHPNAME, $indexType)];
            $this->last_name = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : GroupOrderSubCustomerTableMap::translateFieldName('Email', TableMap::TYPE_PHPNAME, $indexType)];
            $this->email = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : GroupOrderSubCustomerTableMap::translateFieldName('Address1', TableMap::TYPE_PHPNAME, $indexType)];
            $this->address1 = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 6 + $startcol : GroupOrderSubCustomerTableMap::translateFieldName('Address2', TableMap::TYPE_PHPNAME, $indexType)];
            $this->address2 = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 7 + $startcol : GroupOrderSubCustomerTableMap::translateFieldName('Address3', TableMap::TYPE_PHPNAME, $indexType)];
            $this->address3 = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 8 + $startcol : GroupOrderSubCustomerTableMap::translateFieldName('City', TableMap::TYPE_PHPNAME, $indexType)];
            $this->city = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 9 + $startcol : GroupOrderSubCustomerTableMap::translateFieldName('ZipCode', TableMap::TYPE_PHPNAME, $indexType)];
            $this->zip_code = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 10 + $startcol : GroupOrderSubCustomerTableMap::translateFieldName('CountryId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->country_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 11 + $startcol : GroupOrderSubCustomerTableMap::translateFieldName('Login', TableMap::TYPE_PHPNAME, $indexType)];
            $this->login = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 12 + $startcol : GroupOrderSubCustomerTableMap::translateFieldName('Password', TableMap::TYPE_PHPNAME, $indexType)];
            $this->password = (null !== $col) ? (string) $col : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 13; // 13 = GroupOrderSubCustomerTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating \GroupOrder\Model\GroupOrderSubCustomer object", 0, $e);
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
     * @throws PropelException
     */
    public function ensureConsistency()
    {
        if ($this->aGroupOrderMainCustomer !== null && $this->main_customer_id !== $this->aGroupOrderMainCustomer->getId()) {
            $this->aGroupOrderMainCustomer = null;
        }
        if ($this->aCountry !== null && $this->country_id !== $this->aCountry->getId()) {
            $this->aCountry = null;
        }
    } // ensureConsistency

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param      boolean $deep (optional) Whether to also de-associated any related objects.
     * @param      ConnectionInterface $con (optional) The ConnectionInterface connection to use.
     * @return void
     * @throws PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload($deep = false, ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(GroupOrderSubCustomerTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildGroupOrderSubCustomerQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aGroupOrderMainCustomer = null;
            $this->aCountry = null;
            $this->collGroupOrderSubOrders = null;

            $this->collGroupOrderCartItems = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see GroupOrderSubCustomer::setDeleted()
     * @see GroupOrderSubCustomer::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(GroupOrderSubCustomerTableMap::DATABASE_NAME);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = ChildGroupOrderSubCustomerQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                $con->commit();
                $this->setDeleted(true);
            } else {
                $con->commit();
            }
        } catch (Exception $e) {
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
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see doSave()
     */
    public function save(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(GroupOrderSubCustomerTableMap::DATABASE_NAME);
        }

        $con->beginTransaction();
        $isInsert = $this->isNew();
        try {
            $ret = $this->preSave($con);
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
            } else {
                $ret = $ret && $this->preUpdate($con);
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                GroupOrderSubCustomerTableMap::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
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
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see save()
     */
    protected function doSave(ConnectionInterface $con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            // We call the save method on the following object(s) if they
            // were passed to this object by their corresponding set
            // method.  This object relates to these object(s) by a
            // foreign key reference.

            if ($this->aGroupOrderMainCustomer !== null) {
                if ($this->aGroupOrderMainCustomer->isModified() || $this->aGroupOrderMainCustomer->isNew()) {
                    $affectedRows += $this->aGroupOrderMainCustomer->save($con);
                }
                $this->setGroupOrderMainCustomer($this->aGroupOrderMainCustomer);
            }

            if ($this->aCountry !== null) {
                if ($this->aCountry->isModified() || $this->aCountry->isNew()) {
                    $affectedRows += $this->aCountry->save($con);
                }
                $this->setCountry($this->aCountry);
            }

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                } else {
                    $this->doUpdate($con);
                }
                $affectedRows += 1;
                $this->resetModified();
            }

            if ($this->groupOrderSubOrdersScheduledForDeletion !== null) {
                if (!$this->groupOrderSubOrdersScheduledForDeletion->isEmpty()) {
                    \GroupOrder\Model\GroupOrderSubOrderQuery::create()
                        ->filterByPrimaryKeys($this->groupOrderSubOrdersScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->groupOrderSubOrdersScheduledForDeletion = null;
                }
            }

                if ($this->collGroupOrderSubOrders !== null) {
            foreach ($this->collGroupOrderSubOrders as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->groupOrderCartItemsScheduledForDeletion !== null) {
                if (!$this->groupOrderCartItemsScheduledForDeletion->isEmpty()) {
                    \GroupOrder\Model\GroupOrderCartItemQuery::create()
                        ->filterByPrimaryKeys($this->groupOrderCartItemsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->groupOrderCartItemsScheduledForDeletion = null;
                }
            }

                if ($this->collGroupOrderCartItems !== null) {
            foreach ($this->collGroupOrderCartItems as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    } // doSave()

    /**
     * Insert the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @throws PropelException
     * @see doSave()
     */
    protected function doInsert(ConnectionInterface $con)
    {
        $modifiedColumns = array();
        $index = 0;

        $this->modifiedColumns[GroupOrderSubCustomerTableMap::ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . GroupOrderSubCustomerTableMap::ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::ID)) {
            $modifiedColumns[':p' . $index++]  = 'ID';
        }
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::MAIN_CUSTOMER_ID)) {
            $modifiedColumns[':p' . $index++]  = 'MAIN_CUSTOMER_ID';
        }
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::FIRST_NAME)) {
            $modifiedColumns[':p' . $index++]  = 'FIRST_NAME';
        }
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::LAST_NAME)) {
            $modifiedColumns[':p' . $index++]  = 'LAST_NAME';
        }
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::EMAIL)) {
            $modifiedColumns[':p' . $index++]  = 'EMAIL';
        }
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::ADDRESS1)) {
            $modifiedColumns[':p' . $index++]  = 'ADDRESS1';
        }
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::ADDRESS2)) {
            $modifiedColumns[':p' . $index++]  = 'ADDRESS2';
        }
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::ADDRESS3)) {
            $modifiedColumns[':p' . $index++]  = 'ADDRESS3';
        }
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::CITY)) {
            $modifiedColumns[':p' . $index++]  = 'CITY';
        }
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::ZIP_CODE)) {
            $modifiedColumns[':p' . $index++]  = 'ZIP_CODE';
        }
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::COUNTRY_ID)) {
            $modifiedColumns[':p' . $index++]  = 'COUNTRY_ID';
        }
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::LOGIN)) {
            $modifiedColumns[':p' . $index++]  = 'LOGIN';
        }
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::PASSWORD)) {
            $modifiedColumns[':p' . $index++]  = 'PASSWORD';
        }

        $sql = sprintf(
            'INSERT INTO group_order_sub_customer (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case 'ID':
                        $stmt->bindValue($identifier, $this->id, PDO::PARAM_INT);
                        break;
                    case 'MAIN_CUSTOMER_ID':
                        $stmt->bindValue($identifier, $this->main_customer_id, PDO::PARAM_INT);
                        break;
                    case 'FIRST_NAME':
                        $stmt->bindValue($identifier, $this->first_name, PDO::PARAM_STR);
                        break;
                    case 'LAST_NAME':
                        $stmt->bindValue($identifier, $this->last_name, PDO::PARAM_STR);
                        break;
                    case 'EMAIL':
                        $stmt->bindValue($identifier, $this->email, PDO::PARAM_STR);
                        break;
                    case 'ADDRESS1':
                        $stmt->bindValue($identifier, $this->address1, PDO::PARAM_STR);
                        break;
                    case 'ADDRESS2':
                        $stmt->bindValue($identifier, $this->address2, PDO::PARAM_STR);
                        break;
                    case 'ADDRESS3':
                        $stmt->bindValue($identifier, $this->address3, PDO::PARAM_STR);
                        break;
                    case 'CITY':
                        $stmt->bindValue($identifier, $this->city, PDO::PARAM_STR);
                        break;
                    case 'ZIP_CODE':
                        $stmt->bindValue($identifier, $this->zip_code, PDO::PARAM_STR);
                        break;
                    case 'COUNTRY_ID':
                        $stmt->bindValue($identifier, $this->country_id, PDO::PARAM_INT);
                        break;
                    case 'LOGIN':
                        $stmt->bindValue($identifier, $this->login, PDO::PARAM_STR);
                        break;
                    case 'PASSWORD':
                        $stmt->bindValue($identifier, $this->password, PDO::PARAM_STR);
                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), 0, $e);
        }

        try {
            $pk = $con->lastInsertId();
        } catch (Exception $e) {
            throw new PropelException('Unable to get autoincrement id.', 0, $e);
        }
        $this->setId($pk);

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @return Integer Number of updated rows
     * @see doSave()
     */
    protected function doUpdate(ConnectionInterface $con)
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();

        return $selectCriteria->doUpdate($valuesCriteria, $con);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param      string $name name
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return mixed Value of field.
     */
    public function getByName($name, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = GroupOrderSubCustomerTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @return mixed Value of field at $pos
     */
    public function getByPosition($pos)
    {
        switch ($pos) {
            case 0:
                return $this->getId();
                break;
            case 1:
                return $this->getMainCustomerId();
                break;
            case 2:
                return $this->getFirstName();
                break;
            case 3:
                return $this->getLastName();
                break;
            case 4:
                return $this->getEmail();
                break;
            case 5:
                return $this->getAddress1();
                break;
            case 6:
                return $this->getAddress2();
                break;
            case 7:
                return $this->getAddress3();
                break;
            case 8:
                return $this->getCity();
                break;
            case 9:
                return $this->getZipCode();
                break;
            case 10:
                return $this->getCountryId();
                break;
            case 11:
                return $this->getLogin();
                break;
            case 12:
                return $this->getPassword();
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
     * @param     string  $keyType (optional) One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME,
     *                    TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                    Defaults to TableMap::TYPE_PHPNAME.
     * @param     boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to TRUE.
     * @param     array $alreadyDumpedObjects List of objects to skip to avoid recursion
     * @param     boolean $includeForeignObjects (optional) Whether to include hydrated related objects. Default to FALSE.
     *
     * @return array an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false)
    {
        if (isset($alreadyDumpedObjects['GroupOrderSubCustomer'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['GroupOrderSubCustomer'][$this->getPrimaryKey()] = true;
        $keys = GroupOrderSubCustomerTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getMainCustomerId(),
            $keys[2] => $this->getFirstName(),
            $keys[3] => $this->getLastName(),
            $keys[4] => $this->getEmail(),
            $keys[5] => $this->getAddress1(),
            $keys[6] => $this->getAddress2(),
            $keys[7] => $this->getAddress3(),
            $keys[8] => $this->getCity(),
            $keys[9] => $this->getZipCode(),
            $keys[10] => $this->getCountryId(),
            $keys[11] => $this->getLogin(),
            $keys[12] => $this->getPassword(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->aGroupOrderMainCustomer) {
                $result['GroupOrderMainCustomer'] = $this->aGroupOrderMainCustomer->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aCountry) {
                $result['Country'] = $this->aCountry->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->collGroupOrderSubOrders) {
                $result['GroupOrderSubOrders'] = $this->collGroupOrderSubOrders->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collGroupOrderCartItems) {
                $result['GroupOrderCartItems'] = $this->collGroupOrderCartItems->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
        }

        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param      string $name
     * @param      mixed  $value field value
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return void
     */
    public function setByName($name, $value, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = GroupOrderSubCustomerTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @param      mixed $value field value
     * @return void
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setMainCustomerId($value);
                break;
            case 2:
                $this->setFirstName($value);
                break;
            case 3:
                $this->setLastName($value);
                break;
            case 4:
                $this->setEmail($value);
                break;
            case 5:
                $this->setAddress1($value);
                break;
            case 6:
                $this->setAddress2($value);
                break;
            case 7:
                $this->setAddress3($value);
                break;
            case 8:
                $this->setCity($value);
                break;
            case 9:
                $this->setZipCode($value);
                break;
            case 10:
                $this->setCountryId($value);
                break;
            case 11:
                $this->setLogin($value);
                break;
            case 12:
                $this->setPassword($value);
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
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param      array  $arr     An array to populate the object from.
     * @param      string $keyType The type of keys the array uses.
     * @return void
     */
    public function fromArray($arr, $keyType = TableMap::TYPE_PHPNAME)
    {
        $keys = GroupOrderSubCustomerTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setMainCustomerId($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setFirstName($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setLastName($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setEmail($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setAddress1($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setAddress2($arr[$keys[6]]);
        if (array_key_exists($keys[7], $arr)) $this->setAddress3($arr[$keys[7]]);
        if (array_key_exists($keys[8], $arr)) $this->setCity($arr[$keys[8]]);
        if (array_key_exists($keys[9], $arr)) $this->setZipCode($arr[$keys[9]]);
        if (array_key_exists($keys[10], $arr)) $this->setCountryId($arr[$keys[10]]);
        if (array_key_exists($keys[11], $arr)) $this->setLogin($arr[$keys[11]]);
        if (array_key_exists($keys[12], $arr)) $this->setPassword($arr[$keys[12]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(GroupOrderSubCustomerTableMap::DATABASE_NAME);

        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::ID)) $criteria->add(GroupOrderSubCustomerTableMap::ID, $this->id);
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::MAIN_CUSTOMER_ID)) $criteria->add(GroupOrderSubCustomerTableMap::MAIN_CUSTOMER_ID, $this->main_customer_id);
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::FIRST_NAME)) $criteria->add(GroupOrderSubCustomerTableMap::FIRST_NAME, $this->first_name);
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::LAST_NAME)) $criteria->add(GroupOrderSubCustomerTableMap::LAST_NAME, $this->last_name);
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::EMAIL)) $criteria->add(GroupOrderSubCustomerTableMap::EMAIL, $this->email);
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::ADDRESS1)) $criteria->add(GroupOrderSubCustomerTableMap::ADDRESS1, $this->address1);
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::ADDRESS2)) $criteria->add(GroupOrderSubCustomerTableMap::ADDRESS2, $this->address2);
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::ADDRESS3)) $criteria->add(GroupOrderSubCustomerTableMap::ADDRESS3, $this->address3);
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::CITY)) $criteria->add(GroupOrderSubCustomerTableMap::CITY, $this->city);
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::ZIP_CODE)) $criteria->add(GroupOrderSubCustomerTableMap::ZIP_CODE, $this->zip_code);
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::COUNTRY_ID)) $criteria->add(GroupOrderSubCustomerTableMap::COUNTRY_ID, $this->country_id);
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::LOGIN)) $criteria->add(GroupOrderSubCustomerTableMap::LOGIN, $this->login);
        if ($this->isColumnModified(GroupOrderSubCustomerTableMap::PASSWORD)) $criteria->add(GroupOrderSubCustomerTableMap::PASSWORD, $this->password);

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @return Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = new Criteria(GroupOrderSubCustomerTableMap::DATABASE_NAME);
        $criteria->add(GroupOrderSubCustomerTableMap::ID, $this->id);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return   int
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * Generic method to set the primary key (id column).
     *
     * @param       int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setId($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {

        return null === $this->getId();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of \GroupOrder\Model\GroupOrderSubCustomer (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setMainCustomerId($this->getMainCustomerId());
        $copyObj->setFirstName($this->getFirstName());
        $copyObj->setLastName($this->getLastName());
        $copyObj->setEmail($this->getEmail());
        $copyObj->setAddress1($this->getAddress1());
        $copyObj->setAddress2($this->getAddress2());
        $copyObj->setAddress3($this->getAddress3());
        $copyObj->setCity($this->getCity());
        $copyObj->setZipCode($this->getZipCode());
        $copyObj->setCountryId($this->getCountryId());
        $copyObj->setLogin($this->getLogin());
        $copyObj->setPassword($this->getPassword());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getGroupOrderSubOrders() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addGroupOrderSubOrder($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getGroupOrderCartItems() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addGroupOrderCartItem($relObj->copy($deepCopy));
                }
            }

        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setId(NULL); // this is a auto-increment column, so set to default value
        }
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
     * @return                 \GroupOrder\Model\GroupOrderSubCustomer Clone of current object.
     * @throws PropelException
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
     * Declares an association between this object and a ChildGroupOrderMainCustomer object.
     *
     * @param                  ChildGroupOrderMainCustomer $v
     * @return                 \GroupOrder\Model\GroupOrderSubCustomer The current object (for fluent API support)
     * @throws PropelException
     */
    public function setGroupOrderMainCustomer(ChildGroupOrderMainCustomer $v = null)
    {
        if ($v === null) {
            $this->setMainCustomerId(NULL);
        } else {
            $this->setMainCustomerId($v->getId());
        }

        $this->aGroupOrderMainCustomer = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildGroupOrderMainCustomer object, it will not be re-added.
        if ($v !== null) {
            $v->addGroupOrderSubCustomer($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildGroupOrderMainCustomer object
     *
     * @param      ConnectionInterface $con Optional Connection object.
     * @return                 ChildGroupOrderMainCustomer The associated ChildGroupOrderMainCustomer object.
     * @throws PropelException
     */
    public function getGroupOrderMainCustomer(ConnectionInterface $con = null)
    {
        if ($this->aGroupOrderMainCustomer === null && ($this->main_customer_id !== null)) {
            $this->aGroupOrderMainCustomer = ChildGroupOrderMainCustomerQuery::create()->findPk($this->main_customer_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aGroupOrderMainCustomer->addGroupOrderSubCustomers($this);
             */
        }

        return $this->aGroupOrderMainCustomer;
    }

    /**
     * Declares an association between this object and a ChildCountry object.
     *
     * @param                  ChildCountry $v
     * @return                 \GroupOrder\Model\GroupOrderSubCustomer The current object (for fluent API support)
     * @throws PropelException
     */
    public function setCountry(ChildCountry $v = null)
    {
        if ($v === null) {
            $this->setCountryId(NULL);
        } else {
            $this->setCountryId($v->getId());
        }

        $this->aCountry = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildCountry object, it will not be re-added.
        if ($v !== null) {
            $v->addGroupOrderSubCustomer($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildCountry object
     *
     * @param      ConnectionInterface $con Optional Connection object.
     * @return                 ChildCountry The associated ChildCountry object.
     * @throws PropelException
     */
    public function getCountry(ConnectionInterface $con = null)
    {
        if ($this->aCountry === null && ($this->country_id !== null)) {
            $this->aCountry = CountryQuery::create()->findPk($this->country_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aCountry->addGroupOrderSubCustomers($this);
             */
        }

        return $this->aCountry;
    }


    /**
     * Initializes a collection based on the name of a relation.
     * Avoids crafting an 'init[$relationName]s' method name
     * that wouldn't work when StandardEnglishPluralizer is used.
     *
     * @param      string $relationName The name of the relation to initialize
     * @return void
     */
    public function initRelation($relationName)
    {
        if ('GroupOrderSubOrder' == $relationName) {
            return $this->initGroupOrderSubOrders();
        }
        if ('GroupOrderCartItem' == $relationName) {
            return $this->initGroupOrderCartItems();
        }
    }

    /**
     * Clears out the collGroupOrderSubOrders collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addGroupOrderSubOrders()
     */
    public function clearGroupOrderSubOrders()
    {
        $this->collGroupOrderSubOrders = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collGroupOrderSubOrders collection loaded partially.
     */
    public function resetPartialGroupOrderSubOrders($v = true)
    {
        $this->collGroupOrderSubOrdersPartial = $v;
    }

    /**
     * Initializes the collGroupOrderSubOrders collection.
     *
     * By default this just sets the collGroupOrderSubOrders collection to an empty array (like clearcollGroupOrderSubOrders());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initGroupOrderSubOrders($overrideExisting = true)
    {
        if (null !== $this->collGroupOrderSubOrders && !$overrideExisting) {
            return;
        }
        $this->collGroupOrderSubOrders = new ObjectCollection();
        $this->collGroupOrderSubOrders->setModel('\GroupOrder\Model\GroupOrderSubOrder');
    }

    /**
     * Gets an array of ChildGroupOrderSubOrder objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildGroupOrderSubCustomer is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return Collection|ChildGroupOrderSubOrder[] List of ChildGroupOrderSubOrder objects
     * @throws PropelException
     */
    public function getGroupOrderSubOrders($criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collGroupOrderSubOrdersPartial && !$this->isNew();
        if (null === $this->collGroupOrderSubOrders || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collGroupOrderSubOrders) {
                // return empty collection
                $this->initGroupOrderSubOrders();
            } else {
                $collGroupOrderSubOrders = ChildGroupOrderSubOrderQuery::create(null, $criteria)
                    ->filterByGroupOrderSubCustomer($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collGroupOrderSubOrdersPartial && count($collGroupOrderSubOrders)) {
                        $this->initGroupOrderSubOrders(false);

                        foreach ($collGroupOrderSubOrders as $obj) {
                            if (false == $this->collGroupOrderSubOrders->contains($obj)) {
                                $this->collGroupOrderSubOrders->append($obj);
                            }
                        }

                        $this->collGroupOrderSubOrdersPartial = true;
                    }

                    reset($collGroupOrderSubOrders);

                    return $collGroupOrderSubOrders;
                }

                if ($partial && $this->collGroupOrderSubOrders) {
                    foreach ($this->collGroupOrderSubOrders as $obj) {
                        if ($obj->isNew()) {
                            $collGroupOrderSubOrders[] = $obj;
                        }
                    }
                }

                $this->collGroupOrderSubOrders = $collGroupOrderSubOrders;
                $this->collGroupOrderSubOrdersPartial = false;
            }
        }

        return $this->collGroupOrderSubOrders;
    }

    /**
     * Sets a collection of GroupOrderSubOrder objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $groupOrderSubOrders A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return   ChildGroupOrderSubCustomer The current object (for fluent API support)
     */
    public function setGroupOrderSubOrders(Collection $groupOrderSubOrders, ConnectionInterface $con = null)
    {
        $groupOrderSubOrdersToDelete = $this->getGroupOrderSubOrders(new Criteria(), $con)->diff($groupOrderSubOrders);


        $this->groupOrderSubOrdersScheduledForDeletion = $groupOrderSubOrdersToDelete;

        foreach ($groupOrderSubOrdersToDelete as $groupOrderSubOrderRemoved) {
            $groupOrderSubOrderRemoved->setGroupOrderSubCustomer(null);
        }

        $this->collGroupOrderSubOrders = null;
        foreach ($groupOrderSubOrders as $groupOrderSubOrder) {
            $this->addGroupOrderSubOrder($groupOrderSubOrder);
        }

        $this->collGroupOrderSubOrders = $groupOrderSubOrders;
        $this->collGroupOrderSubOrdersPartial = false;

        return $this;
    }

    /**
     * Returns the number of related GroupOrderSubOrder objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related GroupOrderSubOrder objects.
     * @throws PropelException
     */
    public function countGroupOrderSubOrders(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collGroupOrderSubOrdersPartial && !$this->isNew();
        if (null === $this->collGroupOrderSubOrders || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collGroupOrderSubOrders) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getGroupOrderSubOrders());
            }

            $query = ChildGroupOrderSubOrderQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByGroupOrderSubCustomer($this)
                ->count($con);
        }

        return count($this->collGroupOrderSubOrders);
    }

    /**
     * Method called to associate a ChildGroupOrderSubOrder object to this object
     * through the ChildGroupOrderSubOrder foreign key attribute.
     *
     * @param    ChildGroupOrderSubOrder $l ChildGroupOrderSubOrder
     * @return   \GroupOrder\Model\GroupOrderSubCustomer The current object (for fluent API support)
     */
    public function addGroupOrderSubOrder(ChildGroupOrderSubOrder $l)
    {
        if ($this->collGroupOrderSubOrders === null) {
            $this->initGroupOrderSubOrders();
            $this->collGroupOrderSubOrdersPartial = true;
        }

        if (!in_array($l, $this->collGroupOrderSubOrders->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddGroupOrderSubOrder($l);
        }

        return $this;
    }

    /**
     * @param GroupOrderSubOrder $groupOrderSubOrder The groupOrderSubOrder object to add.
     */
    protected function doAddGroupOrderSubOrder($groupOrderSubOrder)
    {
        $this->collGroupOrderSubOrders[]= $groupOrderSubOrder;
        $groupOrderSubOrder->setGroupOrderSubCustomer($this);
    }

    /**
     * @param  GroupOrderSubOrder $groupOrderSubOrder The groupOrderSubOrder object to remove.
     * @return ChildGroupOrderSubCustomer The current object (for fluent API support)
     */
    public function removeGroupOrderSubOrder($groupOrderSubOrder)
    {
        if ($this->getGroupOrderSubOrders()->contains($groupOrderSubOrder)) {
            $this->collGroupOrderSubOrders->remove($this->collGroupOrderSubOrders->search($groupOrderSubOrder));
            if (null === $this->groupOrderSubOrdersScheduledForDeletion) {
                $this->groupOrderSubOrdersScheduledForDeletion = clone $this->collGroupOrderSubOrders;
                $this->groupOrderSubOrdersScheduledForDeletion->clear();
            }
            $this->groupOrderSubOrdersScheduledForDeletion[]= clone $groupOrderSubOrder;
            $groupOrderSubOrder->setGroupOrderSubCustomer(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this GroupOrderSubCustomer is new, it will return
     * an empty collection; or if this GroupOrderSubCustomer has previously
     * been saved, it will retrieve related GroupOrderSubOrders from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in GroupOrderSubCustomer.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return Collection|ChildGroupOrderSubOrder[] List of ChildGroupOrderSubOrder objects
     */
    public function getGroupOrderSubOrdersJoinGroupOrder($criteria = null, $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildGroupOrderSubOrderQuery::create(null, $criteria);
        $query->joinWith('GroupOrder', $joinBehavior);

        return $this->getGroupOrderSubOrders($query, $con);
    }

    /**
     * Clears out the collGroupOrderCartItems collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addGroupOrderCartItems()
     */
    public function clearGroupOrderCartItems()
    {
        $this->collGroupOrderCartItems = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collGroupOrderCartItems collection loaded partially.
     */
    public function resetPartialGroupOrderCartItems($v = true)
    {
        $this->collGroupOrderCartItemsPartial = $v;
    }

    /**
     * Initializes the collGroupOrderCartItems collection.
     *
     * By default this just sets the collGroupOrderCartItems collection to an empty array (like clearcollGroupOrderCartItems());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initGroupOrderCartItems($overrideExisting = true)
    {
        if (null !== $this->collGroupOrderCartItems && !$overrideExisting) {
            return;
        }
        $this->collGroupOrderCartItems = new ObjectCollection();
        $this->collGroupOrderCartItems->setModel('\GroupOrder\Model\GroupOrderCartItem');
    }

    /**
     * Gets an array of ChildGroupOrderCartItem objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildGroupOrderSubCustomer is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return Collection|ChildGroupOrderCartItem[] List of ChildGroupOrderCartItem objects
     * @throws PropelException
     */
    public function getGroupOrderCartItems($criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collGroupOrderCartItemsPartial && !$this->isNew();
        if (null === $this->collGroupOrderCartItems || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collGroupOrderCartItems) {
                // return empty collection
                $this->initGroupOrderCartItems();
            } else {
                $collGroupOrderCartItems = ChildGroupOrderCartItemQuery::create(null, $criteria)
                    ->filterByGroupOrderSubCustomer($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collGroupOrderCartItemsPartial && count($collGroupOrderCartItems)) {
                        $this->initGroupOrderCartItems(false);

                        foreach ($collGroupOrderCartItems as $obj) {
                            if (false == $this->collGroupOrderCartItems->contains($obj)) {
                                $this->collGroupOrderCartItems->append($obj);
                            }
                        }

                        $this->collGroupOrderCartItemsPartial = true;
                    }

                    reset($collGroupOrderCartItems);

                    return $collGroupOrderCartItems;
                }

                if ($partial && $this->collGroupOrderCartItems) {
                    foreach ($this->collGroupOrderCartItems as $obj) {
                        if ($obj->isNew()) {
                            $collGroupOrderCartItems[] = $obj;
                        }
                    }
                }

                $this->collGroupOrderCartItems = $collGroupOrderCartItems;
                $this->collGroupOrderCartItemsPartial = false;
            }
        }

        return $this->collGroupOrderCartItems;
    }

    /**
     * Sets a collection of GroupOrderCartItem objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $groupOrderCartItems A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return   ChildGroupOrderSubCustomer The current object (for fluent API support)
     */
    public function setGroupOrderCartItems(Collection $groupOrderCartItems, ConnectionInterface $con = null)
    {
        $groupOrderCartItemsToDelete = $this->getGroupOrderCartItems(new Criteria(), $con)->diff($groupOrderCartItems);


        $this->groupOrderCartItemsScheduledForDeletion = $groupOrderCartItemsToDelete;

        foreach ($groupOrderCartItemsToDelete as $groupOrderCartItemRemoved) {
            $groupOrderCartItemRemoved->setGroupOrderSubCustomer(null);
        }

        $this->collGroupOrderCartItems = null;
        foreach ($groupOrderCartItems as $groupOrderCartItem) {
            $this->addGroupOrderCartItem($groupOrderCartItem);
        }

        $this->collGroupOrderCartItems = $groupOrderCartItems;
        $this->collGroupOrderCartItemsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related GroupOrderCartItem objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related GroupOrderCartItem objects.
     * @throws PropelException
     */
    public function countGroupOrderCartItems(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collGroupOrderCartItemsPartial && !$this->isNew();
        if (null === $this->collGroupOrderCartItems || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collGroupOrderCartItems) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getGroupOrderCartItems());
            }

            $query = ChildGroupOrderCartItemQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByGroupOrderSubCustomer($this)
                ->count($con);
        }

        return count($this->collGroupOrderCartItems);
    }

    /**
     * Method called to associate a ChildGroupOrderCartItem object to this object
     * through the ChildGroupOrderCartItem foreign key attribute.
     *
     * @param    ChildGroupOrderCartItem $l ChildGroupOrderCartItem
     * @return   \GroupOrder\Model\GroupOrderSubCustomer The current object (for fluent API support)
     */
    public function addGroupOrderCartItem(ChildGroupOrderCartItem $l)
    {
        if ($this->collGroupOrderCartItems === null) {
            $this->initGroupOrderCartItems();
            $this->collGroupOrderCartItemsPartial = true;
        }

        if (!in_array($l, $this->collGroupOrderCartItems->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddGroupOrderCartItem($l);
        }

        return $this;
    }

    /**
     * @param GroupOrderCartItem $groupOrderCartItem The groupOrderCartItem object to add.
     */
    protected function doAddGroupOrderCartItem($groupOrderCartItem)
    {
        $this->collGroupOrderCartItems[]= $groupOrderCartItem;
        $groupOrderCartItem->setGroupOrderSubCustomer($this);
    }

    /**
     * @param  GroupOrderCartItem $groupOrderCartItem The groupOrderCartItem object to remove.
     * @return ChildGroupOrderSubCustomer The current object (for fluent API support)
     */
    public function removeGroupOrderCartItem($groupOrderCartItem)
    {
        if ($this->getGroupOrderCartItems()->contains($groupOrderCartItem)) {
            $this->collGroupOrderCartItems->remove($this->collGroupOrderCartItems->search($groupOrderCartItem));
            if (null === $this->groupOrderCartItemsScheduledForDeletion) {
                $this->groupOrderCartItemsScheduledForDeletion = clone $this->collGroupOrderCartItems;
                $this->groupOrderCartItemsScheduledForDeletion->clear();
            }
            $this->groupOrderCartItemsScheduledForDeletion[]= clone $groupOrderCartItem;
            $groupOrderCartItem->setGroupOrderSubCustomer(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this GroupOrderSubCustomer is new, it will return
     * an empty collection; or if this GroupOrderSubCustomer has previously
     * been saved, it will retrieve related GroupOrderCartItems from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in GroupOrderSubCustomer.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return Collection|ChildGroupOrderCartItem[] List of ChildGroupOrderCartItem objects
     */
    public function getGroupOrderCartItemsJoinCartItem($criteria = null, $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildGroupOrderCartItemQuery::create(null, $criteria);
        $query->joinWith('CartItem', $joinBehavior);

        return $this->getGroupOrderCartItems($query, $con);
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id = null;
        $this->main_customer_id = null;
        $this->first_name = null;
        $this->last_name = null;
        $this->email = null;
        $this->address1 = null;
        $this->address2 = null;
        $this->address3 = null;
        $this->city = null;
        $this->zip_code = null;
        $this->country_id = null;
        $this->login = null;
        $this->password = null;
        $this->alreadyInSave = false;
        $this->clearAllReferences();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);
    }

    /**
     * Resets all references to other model objects or collections of model objects.
     *
     * This method is a user-space workaround for PHP's inability to garbage collect
     * objects with circular references (even in PHP 5.3). This is currently necessary
     * when using Propel in certain daemon or large-volume/high-memory operations.
     *
     * @param      boolean $deep Whether to also clear the references on all referrer objects.
     */
    public function clearAllReferences($deep = false)
    {
        if ($deep) {
            if ($this->collGroupOrderSubOrders) {
                foreach ($this->collGroupOrderSubOrders as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collGroupOrderCartItems) {
                foreach ($this->collGroupOrderCartItems as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collGroupOrderSubOrders = null;
        $this->collGroupOrderCartItems = null;
        $this->aGroupOrderMainCustomer = null;
        $this->aCountry = null;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(GroupOrderSubCustomerTableMap::DEFAULT_STRING_FORMAT);
    }

    /**
     * Code to be run before persisting the object
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preSave(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after persisting the object
     * @param ConnectionInterface $con
     */
    public function postSave(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before inserting to database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preInsert(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after inserting to database
     * @param ConnectionInterface $con
     */
    public function postInsert(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before updating the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preUpdate(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after updating the object in database
     * @param ConnectionInterface $con
     */
    public function postUpdate(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before deleting the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preDelete(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after deleting the object in database
     * @param ConnectionInterface $con
     */
    public function postDelete(ConnectionInterface $con = null)
    {

    }


    /**
     * Derived method to catches calls to undefined methods.
     *
     * Provides magic import/export method support (fromXML()/toXML(), fromYAML()/toYAML(), etc.).
     * Allows to define default __call() behavior if you overwrite __call()
     *
     * @param string $name
     * @param mixed  $params
     *
     * @return array|string
     */
    public function __call($name, $params)
    {
        if (0 === strpos($name, 'get')) {
            $virtualColumn = substr($name, 3);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }

            $virtualColumn = lcfirst($virtualColumn);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }
        }

        if (0 === strpos($name, 'from')) {
            $format = substr($name, 4);

            return $this->importFrom($format, reset($params));
        }

        if (0 === strpos($name, 'to')) {
            $format = substr($name, 2);
            $includeLazyLoadColumns = isset($params[0]) ? $params[0] : true;

            return $this->exportTo($format, $includeLazyLoadColumns);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method: %s.', $name));
    }

}
