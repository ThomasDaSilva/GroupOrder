<?php

namespace GroupOrder\Model\Map;

use GroupOrder\Model\GroupOrderSubCustomer;
use GroupOrder\Model\GroupOrderSubCustomerQuery;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\InstancePoolTrait;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\RelationMap;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Map\TableMapTrait;


/**
 * This class defines the structure of the 'group_order_sub_customer' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 */
class GroupOrderSubCustomerTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;
    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'GroupOrder.Model.Map.GroupOrderSubCustomerTableMap';

    /**
     * The default database name for this class
     */
    const DATABASE_NAME = 'thelia';

    /**
     * The table name for this class
     */
    const TABLE_NAME = 'group_order_sub_customer';

    /**
     * The related Propel class for this table
     */
    const OM_CLASS = '\\GroupOrder\\Model\\GroupOrderSubCustomer';

    /**
     * A class that can be returned by this tableMap
     */
    const CLASS_DEFAULT = 'GroupOrder.Model.GroupOrderSubCustomer';

    /**
     * The total number of columns
     */
    const NUM_COLUMNS = 13;

    /**
     * The number of lazy-loaded columns
     */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    const NUM_HYDRATE_COLUMNS = 13;

    /**
     * the column name for the ID field
     */
    const ID = 'group_order_sub_customer.ID';

    /**
     * the column name for the MAIN_CUSTOMER_ID field
     */
    const MAIN_CUSTOMER_ID = 'group_order_sub_customer.MAIN_CUSTOMER_ID';

    /**
     * the column name for the FIRST_NAME field
     */
    const FIRST_NAME = 'group_order_sub_customer.FIRST_NAME';

    /**
     * the column name for the LAST_NAME field
     */
    const LAST_NAME = 'group_order_sub_customer.LAST_NAME';

    /**
     * the column name for the EMAIL field
     */
    const EMAIL = 'group_order_sub_customer.EMAIL';

    /**
     * the column name for the ADDRESS1 field
     */
    const ADDRESS1 = 'group_order_sub_customer.ADDRESS1';

    /**
     * the column name for the ADDRESS2 field
     */
    const ADDRESS2 = 'group_order_sub_customer.ADDRESS2';

    /**
     * the column name for the ADDRESS3 field
     */
    const ADDRESS3 = 'group_order_sub_customer.ADDRESS3';

    /**
     * the column name for the CITY field
     */
    const CITY = 'group_order_sub_customer.CITY';

    /**
     * the column name for the ZIP_CODE field
     */
    const ZIP_CODE = 'group_order_sub_customer.ZIP_CODE';

    /**
     * the column name for the COUNTRY_ID field
     */
    const COUNTRY_ID = 'group_order_sub_customer.COUNTRY_ID';

    /**
     * the column name for the LOGIN field
     */
    const LOGIN = 'group_order_sub_customer.LOGIN';

    /**
     * the column name for the PASSWORD field
     */
    const PASSWORD = 'group_order_sub_customer.PASSWORD';

    /**
     * The default string format for model objects of the related table
     */
    const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        self::TYPE_PHPNAME       => array('Id', 'MainCustomerId', 'FirstName', 'LastName', 'Email', 'Address1', 'Address2', 'Address3', 'City', 'ZipCode', 'CountryId', 'Login', 'Password', ),
        self::TYPE_STUDLYPHPNAME => array('id', 'mainCustomerId', 'firstName', 'lastName', 'email', 'address1', 'address2', 'address3', 'city', 'zipCode', 'countryId', 'login', 'password', ),
        self::TYPE_COLNAME       => array(GroupOrderSubCustomerTableMap::ID, GroupOrderSubCustomerTableMap::MAIN_CUSTOMER_ID, GroupOrderSubCustomerTableMap::FIRST_NAME, GroupOrderSubCustomerTableMap::LAST_NAME, GroupOrderSubCustomerTableMap::EMAIL, GroupOrderSubCustomerTableMap::ADDRESS1, GroupOrderSubCustomerTableMap::ADDRESS2, GroupOrderSubCustomerTableMap::ADDRESS3, GroupOrderSubCustomerTableMap::CITY, GroupOrderSubCustomerTableMap::ZIP_CODE, GroupOrderSubCustomerTableMap::COUNTRY_ID, GroupOrderSubCustomerTableMap::LOGIN, GroupOrderSubCustomerTableMap::PASSWORD, ),
        self::TYPE_RAW_COLNAME   => array('ID', 'MAIN_CUSTOMER_ID', 'FIRST_NAME', 'LAST_NAME', 'EMAIL', 'ADDRESS1', 'ADDRESS2', 'ADDRESS3', 'CITY', 'ZIP_CODE', 'COUNTRY_ID', 'LOGIN', 'PASSWORD', ),
        self::TYPE_FIELDNAME     => array('id', 'main_customer_id', 'first_name', 'last_name', 'email', 'address1', 'address2', 'address3', 'city', 'zip_code', 'country_id', 'login', 'password', ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        self::TYPE_PHPNAME       => array('Id' => 0, 'MainCustomerId' => 1, 'FirstName' => 2, 'LastName' => 3, 'Email' => 4, 'Address1' => 5, 'Address2' => 6, 'Address3' => 7, 'City' => 8, 'ZipCode' => 9, 'CountryId' => 10, 'Login' => 11, 'Password' => 12, ),
        self::TYPE_STUDLYPHPNAME => array('id' => 0, 'mainCustomerId' => 1, 'firstName' => 2, 'lastName' => 3, 'email' => 4, 'address1' => 5, 'address2' => 6, 'address3' => 7, 'city' => 8, 'zipCode' => 9, 'countryId' => 10, 'login' => 11, 'password' => 12, ),
        self::TYPE_COLNAME       => array(GroupOrderSubCustomerTableMap::ID => 0, GroupOrderSubCustomerTableMap::MAIN_CUSTOMER_ID => 1, GroupOrderSubCustomerTableMap::FIRST_NAME => 2, GroupOrderSubCustomerTableMap::LAST_NAME => 3, GroupOrderSubCustomerTableMap::EMAIL => 4, GroupOrderSubCustomerTableMap::ADDRESS1 => 5, GroupOrderSubCustomerTableMap::ADDRESS2 => 6, GroupOrderSubCustomerTableMap::ADDRESS3 => 7, GroupOrderSubCustomerTableMap::CITY => 8, GroupOrderSubCustomerTableMap::ZIP_CODE => 9, GroupOrderSubCustomerTableMap::COUNTRY_ID => 10, GroupOrderSubCustomerTableMap::LOGIN => 11, GroupOrderSubCustomerTableMap::PASSWORD => 12, ),
        self::TYPE_RAW_COLNAME   => array('ID' => 0, 'MAIN_CUSTOMER_ID' => 1, 'FIRST_NAME' => 2, 'LAST_NAME' => 3, 'EMAIL' => 4, 'ADDRESS1' => 5, 'ADDRESS2' => 6, 'ADDRESS3' => 7, 'CITY' => 8, 'ZIP_CODE' => 9, 'COUNTRY_ID' => 10, 'LOGIN' => 11, 'PASSWORD' => 12, ),
        self::TYPE_FIELDNAME     => array('id' => 0, 'main_customer_id' => 1, 'first_name' => 2, 'last_name' => 3, 'email' => 4, 'address1' => 5, 'address2' => 6, 'address3' => 7, 'city' => 8, 'zip_code' => 9, 'country_id' => 10, 'login' => 11, 'password' => 12, ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, )
    );

    /**
     * Initialize the table attributes and columns
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws PropelException
     */
    public function initialize()
    {
        // attributes
        $this->setName('group_order_sub_customer');
        $this->setPhpName('GroupOrderSubCustomer');
        $this->setClassName('\\GroupOrder\\Model\\GroupOrderSubCustomer');
        $this->setPackage('GroupOrder.Model');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('ID', 'Id', 'INTEGER', true, null, null);
        $this->addForeignKey('MAIN_CUSTOMER_ID', 'MainCustomerId', 'INTEGER', 'group_order_main_customer', 'ID', true, null, null);
        $this->addColumn('FIRST_NAME', 'FirstName', 'VARCHAR', true, 255, null);
        $this->addColumn('LAST_NAME', 'LastName', 'VARCHAR', true, 255, null);
        $this->addColumn('EMAIL', 'Email', 'VARCHAR', false, 255, null);
        $this->addColumn('ADDRESS1', 'Address1', 'VARCHAR', true, 255, null);
        $this->addColumn('ADDRESS2', 'Address2', 'VARCHAR', false, 255, null);
        $this->addColumn('ADDRESS3', 'Address3', 'VARCHAR', false, 255, null);
        $this->addColumn('CITY', 'City', 'VARCHAR', true, 255, null);
        $this->addColumn('ZIP_CODE', 'ZipCode', 'VARCHAR', true, 255, null);
        $this->addForeignKey('COUNTRY_ID', 'CountryId', 'INTEGER', 'country', 'ID', true, null, null);
        $this->addColumn('LOGIN', 'Login', 'VARCHAR', true, 255, null);
        $this->addColumn('PASSWORD', 'Password', 'VARCHAR', true, 255, null);
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('GroupOrderMainCustomer', '\\GroupOrder\\Model\\GroupOrderMainCustomer', RelationMap::MANY_TO_ONE, array('main_customer_id' => 'id', ), 'RESTRICT', 'RESTRICT');
        $this->addRelation('Country', '\\Thelia\\Model\\Country', RelationMap::MANY_TO_ONE, array('country_id' => 'id', ), 'RESTRICT', 'RESTRICT');
        $this->addRelation('GroupOrderSubOrder', '\\GroupOrder\\Model\\GroupOrderSubOrder', RelationMap::ONE_TO_MANY, array('id' => 'sub_customer_id', ), 'RESTRICT', 'RESTRICT', 'GroupOrderSubOrders');
        $this->addRelation('GroupOrderCartItem', '\\GroupOrder\\Model\\GroupOrderCartItem', RelationMap::ONE_TO_MANY, array('id' => 'sub_customer_id', ), 'RESTRICT', 'RESTRICT', 'GroupOrderCartItems');
    } // buildRelations()

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     */
    public static function getPrimaryKeyHashFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        // If the PK cannot be derived from the row, return NULL.
        if ($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] === null) {
            return null;
        }

        return (string) $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
    }

    /**
     * Retrieves the primary key from the DB resultset row
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, an array of the primary key columns will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {

            return (int) $row[
                            $indexType == TableMap::TYPE_NUM
                            ? 0 + $offset
                            : self::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)
                        ];
    }

    /**
     * The class that the tableMap will make instances of.
     *
     * If $withPrefix is true, the returned path
     * uses a dot-path notation which is translated into a path
     * relative to a location on the PHP include_path.
     * (e.g. path.to.MyClass -> 'path/to/MyClass.php')
     *
     * @param boolean $withPrefix Whether or not to return the path with the class name
     * @return string path.to.ClassName
     */
    public static function getOMClass($withPrefix = true)
    {
        return $withPrefix ? GroupOrderSubCustomerTableMap::CLASS_DEFAULT : GroupOrderSubCustomerTableMap::OM_CLASS;
    }

    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param array  $row       row returned by DataFetcher->fetch().
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                 One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     * @return array (GroupOrderSubCustomer object, last column rank)
     */
    public static function populateObject($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        $key = GroupOrderSubCustomerTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = GroupOrderSubCustomerTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + GroupOrderSubCustomerTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = GroupOrderSubCustomerTableMap::OM_CLASS;
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            GroupOrderSubCustomerTableMap::addInstanceToPool($obj, $key);
        }

        return array($obj, $col);
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @param DataFetcherInterface $dataFetcher
     * @return array
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
    public static function populateObjects(DataFetcherInterface $dataFetcher)
    {
        $results = array();

        // set the class once to avoid overhead in the loop
        $cls = static::getOMClass(false);
        // populate the object(s)
        while ($row = $dataFetcher->fetch()) {
            $key = GroupOrderSubCustomerTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = GroupOrderSubCustomerTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                GroupOrderSubCustomerTableMap::addInstanceToPool($obj, $key);
            } // if key exists
        }

        return $results;
    }
    /**
     * Add all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be added to the select list and only loaded
     * on demand.
     *
     * @param Criteria $criteria object containing the columns to add.
     * @param string   $alias    optional table alias
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
    public static function addSelectColumns(Criteria $criteria, $alias = null)
    {
        if (null === $alias) {
            $criteria->addSelectColumn(GroupOrderSubCustomerTableMap::ID);
            $criteria->addSelectColumn(GroupOrderSubCustomerTableMap::MAIN_CUSTOMER_ID);
            $criteria->addSelectColumn(GroupOrderSubCustomerTableMap::FIRST_NAME);
            $criteria->addSelectColumn(GroupOrderSubCustomerTableMap::LAST_NAME);
            $criteria->addSelectColumn(GroupOrderSubCustomerTableMap::EMAIL);
            $criteria->addSelectColumn(GroupOrderSubCustomerTableMap::ADDRESS1);
            $criteria->addSelectColumn(GroupOrderSubCustomerTableMap::ADDRESS2);
            $criteria->addSelectColumn(GroupOrderSubCustomerTableMap::ADDRESS3);
            $criteria->addSelectColumn(GroupOrderSubCustomerTableMap::CITY);
            $criteria->addSelectColumn(GroupOrderSubCustomerTableMap::ZIP_CODE);
            $criteria->addSelectColumn(GroupOrderSubCustomerTableMap::COUNTRY_ID);
            $criteria->addSelectColumn(GroupOrderSubCustomerTableMap::LOGIN);
            $criteria->addSelectColumn(GroupOrderSubCustomerTableMap::PASSWORD);
        } else {
            $criteria->addSelectColumn($alias . '.ID');
            $criteria->addSelectColumn($alias . '.MAIN_CUSTOMER_ID');
            $criteria->addSelectColumn($alias . '.FIRST_NAME');
            $criteria->addSelectColumn($alias . '.LAST_NAME');
            $criteria->addSelectColumn($alias . '.EMAIL');
            $criteria->addSelectColumn($alias . '.ADDRESS1');
            $criteria->addSelectColumn($alias . '.ADDRESS2');
            $criteria->addSelectColumn($alias . '.ADDRESS3');
            $criteria->addSelectColumn($alias . '.CITY');
            $criteria->addSelectColumn($alias . '.ZIP_CODE');
            $criteria->addSelectColumn($alias . '.COUNTRY_ID');
            $criteria->addSelectColumn($alias . '.LOGIN');
            $criteria->addSelectColumn($alias . '.PASSWORD');
        }
    }

    /**
     * Returns the TableMap related to this object.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
    public static function getTableMap()
    {
        return Propel::getServiceContainer()->getDatabaseMap(GroupOrderSubCustomerTableMap::DATABASE_NAME)->getTable(GroupOrderSubCustomerTableMap::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this tableMap class.
     */
    public static function buildTableMap()
    {
      $dbMap = Propel::getServiceContainer()->getDatabaseMap(GroupOrderSubCustomerTableMap::DATABASE_NAME);
      if (!$dbMap->hasTable(GroupOrderSubCustomerTableMap::TABLE_NAME)) {
        $dbMap->addTableObject(new GroupOrderSubCustomerTableMap());
      }
    }

    /**
     * Performs a DELETE on the database, given a GroupOrderSubCustomer or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or GroupOrderSubCustomer object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, ConnectionInterface $con = null)
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(GroupOrderSubCustomerTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \GroupOrder\Model\GroupOrderSubCustomer) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(GroupOrderSubCustomerTableMap::DATABASE_NAME);
            $criteria->add(GroupOrderSubCustomerTableMap::ID, (array) $values, Criteria::IN);
        }

        $query = GroupOrderSubCustomerQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) { GroupOrderSubCustomerTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) { GroupOrderSubCustomerTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the group_order_sub_customer table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(ConnectionInterface $con = null)
    {
        return GroupOrderSubCustomerQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a GroupOrderSubCustomer or Criteria object.
     *
     * @param mixed               $criteria Criteria or GroupOrderSubCustomer object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(GroupOrderSubCustomerTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from GroupOrderSubCustomer object
        }

        if ($criteria->containsKey(GroupOrderSubCustomerTableMap::ID) && $criteria->keyContainsValue(GroupOrderSubCustomerTableMap::ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.GroupOrderSubCustomerTableMap::ID.')');
        }


        // Set the correct dbName
        $query = GroupOrderSubCustomerQuery::create()->mergeWith($criteria);

        try {
            // use transaction because $criteria could contain info
            // for more than one table (I guess, conceivably)
            $con->beginTransaction();
            $pk = $query->doInsert($con);
            $con->commit();
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }

        return $pk;
    }

} // GroupOrderSubCustomerTableMap
// This is the static code needed to register the TableMap for this table with the main Propel class.
//
GroupOrderSubCustomerTableMap::buildTableMap();
