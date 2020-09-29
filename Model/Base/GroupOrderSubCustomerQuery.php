<?php

namespace GroupOrder\Model\Base;

use \Exception;
use \PDO;
use GroupOrder\Model\GroupOrderSubCustomer as ChildGroupOrderSubCustomer;
use GroupOrder\Model\GroupOrderSubCustomerQuery as ChildGroupOrderSubCustomerQuery;
use GroupOrder\Model\Map\GroupOrderSubCustomerTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Thelia\Model\Country;

/**
 * Base class that represents a query for the 'group_order_sub_customer' table.
 *
 *
 *
 * @method     ChildGroupOrderSubCustomerQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildGroupOrderSubCustomerQuery orderByMainCustomerId($order = Criteria::ASC) Order by the main_customer_id column
 * @method     ChildGroupOrderSubCustomerQuery orderByFirstName($order = Criteria::ASC) Order by the first_name column
 * @method     ChildGroupOrderSubCustomerQuery orderByLastName($order = Criteria::ASC) Order by the last_name column
 * @method     ChildGroupOrderSubCustomerQuery orderByEmail($order = Criteria::ASC) Order by the email column
 * @method     ChildGroupOrderSubCustomerQuery orderByAddress1($order = Criteria::ASC) Order by the address1 column
 * @method     ChildGroupOrderSubCustomerQuery orderByAddress2($order = Criteria::ASC) Order by the address2 column
 * @method     ChildGroupOrderSubCustomerQuery orderByAddress3($order = Criteria::ASC) Order by the address3 column
 * @method     ChildGroupOrderSubCustomerQuery orderByCity($order = Criteria::ASC) Order by the city column
 * @method     ChildGroupOrderSubCustomerQuery orderByZipCode($order = Criteria::ASC) Order by the zip_code column
 * @method     ChildGroupOrderSubCustomerQuery orderByCountryId($order = Criteria::ASC) Order by the country_id column
 * @method     ChildGroupOrderSubCustomerQuery orderByLogin($order = Criteria::ASC) Order by the login column
 * @method     ChildGroupOrderSubCustomerQuery orderByPassword($order = Criteria::ASC) Order by the password column
 *
 * @method     ChildGroupOrderSubCustomerQuery groupById() Group by the id column
 * @method     ChildGroupOrderSubCustomerQuery groupByMainCustomerId() Group by the main_customer_id column
 * @method     ChildGroupOrderSubCustomerQuery groupByFirstName() Group by the first_name column
 * @method     ChildGroupOrderSubCustomerQuery groupByLastName() Group by the last_name column
 * @method     ChildGroupOrderSubCustomerQuery groupByEmail() Group by the email column
 * @method     ChildGroupOrderSubCustomerQuery groupByAddress1() Group by the address1 column
 * @method     ChildGroupOrderSubCustomerQuery groupByAddress2() Group by the address2 column
 * @method     ChildGroupOrderSubCustomerQuery groupByAddress3() Group by the address3 column
 * @method     ChildGroupOrderSubCustomerQuery groupByCity() Group by the city column
 * @method     ChildGroupOrderSubCustomerQuery groupByZipCode() Group by the zip_code column
 * @method     ChildGroupOrderSubCustomerQuery groupByCountryId() Group by the country_id column
 * @method     ChildGroupOrderSubCustomerQuery groupByLogin() Group by the login column
 * @method     ChildGroupOrderSubCustomerQuery groupByPassword() Group by the password column
 *
 * @method     ChildGroupOrderSubCustomerQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildGroupOrderSubCustomerQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildGroupOrderSubCustomerQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildGroupOrderSubCustomerQuery leftJoinGroupOrderMainCustomer($relationAlias = null) Adds a LEFT JOIN clause to the query using the GroupOrderMainCustomer relation
 * @method     ChildGroupOrderSubCustomerQuery rightJoinGroupOrderMainCustomer($relationAlias = null) Adds a RIGHT JOIN clause to the query using the GroupOrderMainCustomer relation
 * @method     ChildGroupOrderSubCustomerQuery innerJoinGroupOrderMainCustomer($relationAlias = null) Adds a INNER JOIN clause to the query using the GroupOrderMainCustomer relation
 *
 * @method     ChildGroupOrderSubCustomerQuery leftJoinCountry($relationAlias = null) Adds a LEFT JOIN clause to the query using the Country relation
 * @method     ChildGroupOrderSubCustomerQuery rightJoinCountry($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Country relation
 * @method     ChildGroupOrderSubCustomerQuery innerJoinCountry($relationAlias = null) Adds a INNER JOIN clause to the query using the Country relation
 *
 * @method     ChildGroupOrderSubCustomerQuery leftJoinGroupOrderSubOrder($relationAlias = null) Adds a LEFT JOIN clause to the query using the GroupOrderSubOrder relation
 * @method     ChildGroupOrderSubCustomerQuery rightJoinGroupOrderSubOrder($relationAlias = null) Adds a RIGHT JOIN clause to the query using the GroupOrderSubOrder relation
 * @method     ChildGroupOrderSubCustomerQuery innerJoinGroupOrderSubOrder($relationAlias = null) Adds a INNER JOIN clause to the query using the GroupOrderSubOrder relation
 *
 * @method     ChildGroupOrderSubCustomerQuery leftJoinGroupOrderCartItem($relationAlias = null) Adds a LEFT JOIN clause to the query using the GroupOrderCartItem relation
 * @method     ChildGroupOrderSubCustomerQuery rightJoinGroupOrderCartItem($relationAlias = null) Adds a RIGHT JOIN clause to the query using the GroupOrderCartItem relation
 * @method     ChildGroupOrderSubCustomerQuery innerJoinGroupOrderCartItem($relationAlias = null) Adds a INNER JOIN clause to the query using the GroupOrderCartItem relation
 *
 * @method     ChildGroupOrderSubCustomer findOne(ConnectionInterface $con = null) Return the first ChildGroupOrderSubCustomer matching the query
 * @method     ChildGroupOrderSubCustomer findOneOrCreate(ConnectionInterface $con = null) Return the first ChildGroupOrderSubCustomer matching the query, or a new ChildGroupOrderSubCustomer object populated from the query conditions when no match is found
 *
 * @method     ChildGroupOrderSubCustomer findOneById(int $id) Return the first ChildGroupOrderSubCustomer filtered by the id column
 * @method     ChildGroupOrderSubCustomer findOneByMainCustomerId(int $main_customer_id) Return the first ChildGroupOrderSubCustomer filtered by the main_customer_id column
 * @method     ChildGroupOrderSubCustomer findOneByFirstName(string $first_name) Return the first ChildGroupOrderSubCustomer filtered by the first_name column
 * @method     ChildGroupOrderSubCustomer findOneByLastName(string $last_name) Return the first ChildGroupOrderSubCustomer filtered by the last_name column
 * @method     ChildGroupOrderSubCustomer findOneByEmail(string $email) Return the first ChildGroupOrderSubCustomer filtered by the email column
 * @method     ChildGroupOrderSubCustomer findOneByAddress1(string $address1) Return the first ChildGroupOrderSubCustomer filtered by the address1 column
 * @method     ChildGroupOrderSubCustomer findOneByAddress2(string $address2) Return the first ChildGroupOrderSubCustomer filtered by the address2 column
 * @method     ChildGroupOrderSubCustomer findOneByAddress3(string $address3) Return the first ChildGroupOrderSubCustomer filtered by the address3 column
 * @method     ChildGroupOrderSubCustomer findOneByCity(string $city) Return the first ChildGroupOrderSubCustomer filtered by the city column
 * @method     ChildGroupOrderSubCustomer findOneByZipCode(string $zip_code) Return the first ChildGroupOrderSubCustomer filtered by the zip_code column
 * @method     ChildGroupOrderSubCustomer findOneByCountryId(int $country_id) Return the first ChildGroupOrderSubCustomer filtered by the country_id column
 * @method     ChildGroupOrderSubCustomer findOneByLogin(string $login) Return the first ChildGroupOrderSubCustomer filtered by the login column
 * @method     ChildGroupOrderSubCustomer findOneByPassword(string $password) Return the first ChildGroupOrderSubCustomer filtered by the password column
 *
 * @method     array findById(int $id) Return ChildGroupOrderSubCustomer objects filtered by the id column
 * @method     array findByMainCustomerId(int $main_customer_id) Return ChildGroupOrderSubCustomer objects filtered by the main_customer_id column
 * @method     array findByFirstName(string $first_name) Return ChildGroupOrderSubCustomer objects filtered by the first_name column
 * @method     array findByLastName(string $last_name) Return ChildGroupOrderSubCustomer objects filtered by the last_name column
 * @method     array findByEmail(string $email) Return ChildGroupOrderSubCustomer objects filtered by the email column
 * @method     array findByAddress1(string $address1) Return ChildGroupOrderSubCustomer objects filtered by the address1 column
 * @method     array findByAddress2(string $address2) Return ChildGroupOrderSubCustomer objects filtered by the address2 column
 * @method     array findByAddress3(string $address3) Return ChildGroupOrderSubCustomer objects filtered by the address3 column
 * @method     array findByCity(string $city) Return ChildGroupOrderSubCustomer objects filtered by the city column
 * @method     array findByZipCode(string $zip_code) Return ChildGroupOrderSubCustomer objects filtered by the zip_code column
 * @method     array findByCountryId(int $country_id) Return ChildGroupOrderSubCustomer objects filtered by the country_id column
 * @method     array findByLogin(string $login) Return ChildGroupOrderSubCustomer objects filtered by the login column
 * @method     array findByPassword(string $password) Return ChildGroupOrderSubCustomer objects filtered by the password column
 *
 */
abstract class GroupOrderSubCustomerQuery extends ModelCriteria
{

    /**
     * Initializes internal state of \GroupOrder\Model\Base\GroupOrderSubCustomerQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'thelia', $modelName = '\\GroupOrder\\Model\\GroupOrderSubCustomer', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildGroupOrderSubCustomerQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildGroupOrderSubCustomerQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof \GroupOrder\Model\GroupOrderSubCustomerQuery) {
            return $criteria;
        }
        $query = new \GroupOrder\Model\GroupOrderSubCustomerQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildGroupOrderSubCustomer|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = GroupOrderSubCustomerTableMap::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(GroupOrderSubCustomerTableMap::DATABASE_NAME);
        }
        $this->basePreSelect($con);
        if ($this->formatter || $this->modelAlias || $this->with || $this->select
         || $this->selectColumns || $this->asColumns || $this->selectModifiers
         || $this->map || $this->having || $this->joins) {
            return $this->findPkComplex($key, $con);
        } else {
            return $this->findPkSimple($key, $con);
        }
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return   ChildGroupOrderSubCustomer A model object, or null if the key is not found
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT ID, MAIN_CUSTOMER_ID, FIRST_NAME, LAST_NAME, EMAIL, ADDRESS1, ADDRESS2, ADDRESS3, CITY, ZIP_CODE, COUNTRY_ID, LOGIN, PASSWORD FROM group_order_sub_customer WHERE ID = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $obj = new ChildGroupOrderSubCustomer();
            $obj->hydrate($row);
            GroupOrderSubCustomerTableMap::addInstanceToPool($obj, (string) $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return ChildGroupOrderSubCustomer|array|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($dataFetcher);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ObjectCollection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection($this->getDbName());
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($dataFetcher);
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return ChildGroupOrderSubCustomerQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(GroupOrderSubCustomerTableMap::ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return ChildGroupOrderSubCustomerQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(GroupOrderSubCustomerTableMap::ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE id = 1234
     * $query->filterById(array(12, 34)); // WHERE id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE id > 12
     * </code>
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGroupOrderSubCustomerQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(GroupOrderSubCustomerTableMap::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(GroupOrderSubCustomerTableMap::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GroupOrderSubCustomerTableMap::ID, $id, $comparison);
    }

    /**
     * Filter the query on the main_customer_id column
     *
     * Example usage:
     * <code>
     * $query->filterByMainCustomerId(1234); // WHERE main_customer_id = 1234
     * $query->filterByMainCustomerId(array(12, 34)); // WHERE main_customer_id IN (12, 34)
     * $query->filterByMainCustomerId(array('min' => 12)); // WHERE main_customer_id > 12
     * </code>
     *
     * @see       filterByGroupOrderMainCustomer()
     *
     * @param     mixed $mainCustomerId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGroupOrderSubCustomerQuery The current query, for fluid interface
     */
    public function filterByMainCustomerId($mainCustomerId = null, $comparison = null)
    {
        if (is_array($mainCustomerId)) {
            $useMinMax = false;
            if (isset($mainCustomerId['min'])) {
                $this->addUsingAlias(GroupOrderSubCustomerTableMap::MAIN_CUSTOMER_ID, $mainCustomerId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($mainCustomerId['max'])) {
                $this->addUsingAlias(GroupOrderSubCustomerTableMap::MAIN_CUSTOMER_ID, $mainCustomerId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GroupOrderSubCustomerTableMap::MAIN_CUSTOMER_ID, $mainCustomerId, $comparison);
    }

    /**
     * Filter the query on the first_name column
     *
     * Example usage:
     * <code>
     * $query->filterByFirstName('fooValue');   // WHERE first_name = 'fooValue'
     * $query->filterByFirstName('%fooValue%'); // WHERE first_name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $firstName The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGroupOrderSubCustomerQuery The current query, for fluid interface
     */
    public function filterByFirstName($firstName = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($firstName)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $firstName)) {
                $firstName = str_replace('*', '%', $firstName);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(GroupOrderSubCustomerTableMap::FIRST_NAME, $firstName, $comparison);
    }

    /**
     * Filter the query on the last_name column
     *
     * Example usage:
     * <code>
     * $query->filterByLastName('fooValue');   // WHERE last_name = 'fooValue'
     * $query->filterByLastName('%fooValue%'); // WHERE last_name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $lastName The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGroupOrderSubCustomerQuery The current query, for fluid interface
     */
    public function filterByLastName($lastName = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($lastName)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $lastName)) {
                $lastName = str_replace('*', '%', $lastName);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(GroupOrderSubCustomerTableMap::LAST_NAME, $lastName, $comparison);
    }

    /**
     * Filter the query on the email column
     *
     * Example usage:
     * <code>
     * $query->filterByEmail('fooValue');   // WHERE email = 'fooValue'
     * $query->filterByEmail('%fooValue%'); // WHERE email LIKE '%fooValue%'
     * </code>
     *
     * @param     string $email The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGroupOrderSubCustomerQuery The current query, for fluid interface
     */
    public function filterByEmail($email = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($email)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $email)) {
                $email = str_replace('*', '%', $email);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(GroupOrderSubCustomerTableMap::EMAIL, $email, $comparison);
    }

    /**
     * Filter the query on the address1 column
     *
     * Example usage:
     * <code>
     * $query->filterByAddress1('fooValue');   // WHERE address1 = 'fooValue'
     * $query->filterByAddress1('%fooValue%'); // WHERE address1 LIKE '%fooValue%'
     * </code>
     *
     * @param     string $address1 The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGroupOrderSubCustomerQuery The current query, for fluid interface
     */
    public function filterByAddress1($address1 = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($address1)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $address1)) {
                $address1 = str_replace('*', '%', $address1);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(GroupOrderSubCustomerTableMap::ADDRESS1, $address1, $comparison);
    }

    /**
     * Filter the query on the address2 column
     *
     * Example usage:
     * <code>
     * $query->filterByAddress2('fooValue');   // WHERE address2 = 'fooValue'
     * $query->filterByAddress2('%fooValue%'); // WHERE address2 LIKE '%fooValue%'
     * </code>
     *
     * @param     string $address2 The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGroupOrderSubCustomerQuery The current query, for fluid interface
     */
    public function filterByAddress2($address2 = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($address2)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $address2)) {
                $address2 = str_replace('*', '%', $address2);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(GroupOrderSubCustomerTableMap::ADDRESS2, $address2, $comparison);
    }

    /**
     * Filter the query on the address3 column
     *
     * Example usage:
     * <code>
     * $query->filterByAddress3('fooValue');   // WHERE address3 = 'fooValue'
     * $query->filterByAddress3('%fooValue%'); // WHERE address3 LIKE '%fooValue%'
     * </code>
     *
     * @param     string $address3 The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGroupOrderSubCustomerQuery The current query, for fluid interface
     */
    public function filterByAddress3($address3 = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($address3)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $address3)) {
                $address3 = str_replace('*', '%', $address3);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(GroupOrderSubCustomerTableMap::ADDRESS3, $address3, $comparison);
    }

    /**
     * Filter the query on the city column
     *
     * Example usage:
     * <code>
     * $query->filterByCity('fooValue');   // WHERE city = 'fooValue'
     * $query->filterByCity('%fooValue%'); // WHERE city LIKE '%fooValue%'
     * </code>
     *
     * @param     string $city The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGroupOrderSubCustomerQuery The current query, for fluid interface
     */
    public function filterByCity($city = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($city)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $city)) {
                $city = str_replace('*', '%', $city);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(GroupOrderSubCustomerTableMap::CITY, $city, $comparison);
    }

    /**
     * Filter the query on the zip_code column
     *
     * Example usage:
     * <code>
     * $query->filterByZipCode('fooValue');   // WHERE zip_code = 'fooValue'
     * $query->filterByZipCode('%fooValue%'); // WHERE zip_code LIKE '%fooValue%'
     * </code>
     *
     * @param     string $zipCode The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGroupOrderSubCustomerQuery The current query, for fluid interface
     */
    public function filterByZipCode($zipCode = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($zipCode)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $zipCode)) {
                $zipCode = str_replace('*', '%', $zipCode);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(GroupOrderSubCustomerTableMap::ZIP_CODE, $zipCode, $comparison);
    }

    /**
     * Filter the query on the country_id column
     *
     * Example usage:
     * <code>
     * $query->filterByCountryId(1234); // WHERE country_id = 1234
     * $query->filterByCountryId(array(12, 34)); // WHERE country_id IN (12, 34)
     * $query->filterByCountryId(array('min' => 12)); // WHERE country_id > 12
     * </code>
     *
     * @see       filterByCountry()
     *
     * @param     mixed $countryId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGroupOrderSubCustomerQuery The current query, for fluid interface
     */
    public function filterByCountryId($countryId = null, $comparison = null)
    {
        if (is_array($countryId)) {
            $useMinMax = false;
            if (isset($countryId['min'])) {
                $this->addUsingAlias(GroupOrderSubCustomerTableMap::COUNTRY_ID, $countryId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($countryId['max'])) {
                $this->addUsingAlias(GroupOrderSubCustomerTableMap::COUNTRY_ID, $countryId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GroupOrderSubCustomerTableMap::COUNTRY_ID, $countryId, $comparison);
    }

    /**
     * Filter the query on the login column
     *
     * Example usage:
     * <code>
     * $query->filterByLogin('fooValue');   // WHERE login = 'fooValue'
     * $query->filterByLogin('%fooValue%'); // WHERE login LIKE '%fooValue%'
     * </code>
     *
     * @param     string $login The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGroupOrderSubCustomerQuery The current query, for fluid interface
     */
    public function filterByLogin($login = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($login)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $login)) {
                $login = str_replace('*', '%', $login);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(GroupOrderSubCustomerTableMap::LOGIN, $login, $comparison);
    }

    /**
     * Filter the query on the password column
     *
     * Example usage:
     * <code>
     * $query->filterByPassword('fooValue');   // WHERE password = 'fooValue'
     * $query->filterByPassword('%fooValue%'); // WHERE password LIKE '%fooValue%'
     * </code>
     *
     * @param     string $password The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGroupOrderSubCustomerQuery The current query, for fluid interface
     */
    public function filterByPassword($password = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($password)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $password)) {
                $password = str_replace('*', '%', $password);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(GroupOrderSubCustomerTableMap::PASSWORD, $password, $comparison);
    }

    /**
     * Filter the query by a related \GroupOrder\Model\GroupOrderMainCustomer object
     *
     * @param \GroupOrder\Model\GroupOrderMainCustomer|ObjectCollection $groupOrderMainCustomer The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGroupOrderSubCustomerQuery The current query, for fluid interface
     */
    public function filterByGroupOrderMainCustomer($groupOrderMainCustomer, $comparison = null)
    {
        if ($groupOrderMainCustomer instanceof \GroupOrder\Model\GroupOrderMainCustomer) {
            return $this
                ->addUsingAlias(GroupOrderSubCustomerTableMap::MAIN_CUSTOMER_ID, $groupOrderMainCustomer->getId(), $comparison);
        } elseif ($groupOrderMainCustomer instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(GroupOrderSubCustomerTableMap::MAIN_CUSTOMER_ID, $groupOrderMainCustomer->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByGroupOrderMainCustomer() only accepts arguments of type \GroupOrder\Model\GroupOrderMainCustomer or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the GroupOrderMainCustomer relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildGroupOrderSubCustomerQuery The current query, for fluid interface
     */
    public function joinGroupOrderMainCustomer($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('GroupOrderMainCustomer');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'GroupOrderMainCustomer');
        }

        return $this;
    }

    /**
     * Use the GroupOrderMainCustomer relation GroupOrderMainCustomer object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \GroupOrder\Model\GroupOrderMainCustomerQuery A secondary query class using the current class as primary query
     */
    public function useGroupOrderMainCustomerQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinGroupOrderMainCustomer($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'GroupOrderMainCustomer', '\GroupOrder\Model\GroupOrderMainCustomerQuery');
    }

    /**
     * Filter the query by a related \Thelia\Model\Country object
     *
     * @param \Thelia\Model\Country|ObjectCollection $country The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGroupOrderSubCustomerQuery The current query, for fluid interface
     */
    public function filterByCountry($country, $comparison = null)
    {
        if ($country instanceof \Thelia\Model\Country) {
            return $this
                ->addUsingAlias(GroupOrderSubCustomerTableMap::COUNTRY_ID, $country->getId(), $comparison);
        } elseif ($country instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(GroupOrderSubCustomerTableMap::COUNTRY_ID, $country->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByCountry() only accepts arguments of type \Thelia\Model\Country or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Country relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildGroupOrderSubCustomerQuery The current query, for fluid interface
     */
    public function joinCountry($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Country');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'Country');
        }

        return $this;
    }

    /**
     * Use the Country relation Country object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \Thelia\Model\CountryQuery A secondary query class using the current class as primary query
     */
    public function useCountryQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinCountry($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Country', '\Thelia\Model\CountryQuery');
    }

    /**
     * Filter the query by a related \GroupOrder\Model\GroupOrderSubOrder object
     *
     * @param \GroupOrder\Model\GroupOrderSubOrder|ObjectCollection $groupOrderSubOrder  the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGroupOrderSubCustomerQuery The current query, for fluid interface
     */
    public function filterByGroupOrderSubOrder($groupOrderSubOrder, $comparison = null)
    {
        if ($groupOrderSubOrder instanceof \GroupOrder\Model\GroupOrderSubOrder) {
            return $this
                ->addUsingAlias(GroupOrderSubCustomerTableMap::ID, $groupOrderSubOrder->getSubCustomerId(), $comparison);
        } elseif ($groupOrderSubOrder instanceof ObjectCollection) {
            return $this
                ->useGroupOrderSubOrderQuery()
                ->filterByPrimaryKeys($groupOrderSubOrder->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByGroupOrderSubOrder() only accepts arguments of type \GroupOrder\Model\GroupOrderSubOrder or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the GroupOrderSubOrder relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildGroupOrderSubCustomerQuery The current query, for fluid interface
     */
    public function joinGroupOrderSubOrder($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('GroupOrderSubOrder');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'GroupOrderSubOrder');
        }

        return $this;
    }

    /**
     * Use the GroupOrderSubOrder relation GroupOrderSubOrder object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \GroupOrder\Model\GroupOrderSubOrderQuery A secondary query class using the current class as primary query
     */
    public function useGroupOrderSubOrderQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinGroupOrderSubOrder($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'GroupOrderSubOrder', '\GroupOrder\Model\GroupOrderSubOrderQuery');
    }

    /**
     * Filter the query by a related \GroupOrder\Model\GroupOrderCartItem object
     *
     * @param \GroupOrder\Model\GroupOrderCartItem|ObjectCollection $groupOrderCartItem  the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGroupOrderSubCustomerQuery The current query, for fluid interface
     */
    public function filterByGroupOrderCartItem($groupOrderCartItem, $comparison = null)
    {
        if ($groupOrderCartItem instanceof \GroupOrder\Model\GroupOrderCartItem) {
            return $this
                ->addUsingAlias(GroupOrderSubCustomerTableMap::ID, $groupOrderCartItem->getSubCustomerId(), $comparison);
        } elseif ($groupOrderCartItem instanceof ObjectCollection) {
            return $this
                ->useGroupOrderCartItemQuery()
                ->filterByPrimaryKeys($groupOrderCartItem->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByGroupOrderCartItem() only accepts arguments of type \GroupOrder\Model\GroupOrderCartItem or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the GroupOrderCartItem relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildGroupOrderSubCustomerQuery The current query, for fluid interface
     */
    public function joinGroupOrderCartItem($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('GroupOrderCartItem');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'GroupOrderCartItem');
        }

        return $this;
    }

    /**
     * Use the GroupOrderCartItem relation GroupOrderCartItem object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \GroupOrder\Model\GroupOrderCartItemQuery A secondary query class using the current class as primary query
     */
    public function useGroupOrderCartItemQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinGroupOrderCartItem($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'GroupOrderCartItem', '\GroupOrder\Model\GroupOrderCartItemQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildGroupOrderSubCustomer $groupOrderSubCustomer Object to remove from the list of results
     *
     * @return ChildGroupOrderSubCustomerQuery The current query, for fluid interface
     */
    public function prune($groupOrderSubCustomer = null)
    {
        if ($groupOrderSubCustomer) {
            $this->addUsingAlias(GroupOrderSubCustomerTableMap::ID, $groupOrderSubCustomer->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the group_order_sub_customer table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(GroupOrderSubCustomerTableMap::DATABASE_NAME);
        }
        $affectedRows = 0; // initialize var to track total num of affected rows
        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            GroupOrderSubCustomerTableMap::clearInstancePool();
            GroupOrderSubCustomerTableMap::clearRelatedInstancePool();

            $con->commit();
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }

        return $affectedRows;
    }

    /**
     * Performs a DELETE on the database, given a ChildGroupOrderSubCustomer or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or ChildGroupOrderSubCustomer object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
     public function delete(ConnectionInterface $con = null)
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(GroupOrderSubCustomerTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(GroupOrderSubCustomerTableMap::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();


        GroupOrderSubCustomerTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            GroupOrderSubCustomerTableMap::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }
    }

} // GroupOrderSubCustomerQuery
