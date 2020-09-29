<?php

namespace GroupOrder\Model\Base;

use \Exception;
use \PDO;
use GroupOrder\Model\GroupOrderProduct as ChildGroupOrderProduct;
use GroupOrder\Model\GroupOrderProductQuery as ChildGroupOrderProductQuery;
use GroupOrder\Model\Map\GroupOrderProductTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Thelia\Model\OrderProduct;

/**
 * Base class that represents a query for the 'group_order_product' table.
 *
 *
 *
 * @method     ChildGroupOrderProductQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildGroupOrderProductQuery orderBySubOrderId($order = Criteria::ASC) Order by the sub_order_id column
 * @method     ChildGroupOrderProductQuery orderByOrderProductId($order = Criteria::ASC) Order by the order_product_id column
 *
 * @method     ChildGroupOrderProductQuery groupById() Group by the id column
 * @method     ChildGroupOrderProductQuery groupBySubOrderId() Group by the sub_order_id column
 * @method     ChildGroupOrderProductQuery groupByOrderProductId() Group by the order_product_id column
 *
 * @method     ChildGroupOrderProductQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildGroupOrderProductQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildGroupOrderProductQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildGroupOrderProductQuery leftJoinGroupOrderSubOrder($relationAlias = null) Adds a LEFT JOIN clause to the query using the GroupOrderSubOrder relation
 * @method     ChildGroupOrderProductQuery rightJoinGroupOrderSubOrder($relationAlias = null) Adds a RIGHT JOIN clause to the query using the GroupOrderSubOrder relation
 * @method     ChildGroupOrderProductQuery innerJoinGroupOrderSubOrder($relationAlias = null) Adds a INNER JOIN clause to the query using the GroupOrderSubOrder relation
 *
 * @method     ChildGroupOrderProductQuery leftJoinOrderProduct($relationAlias = null) Adds a LEFT JOIN clause to the query using the OrderProduct relation
 * @method     ChildGroupOrderProductQuery rightJoinOrderProduct($relationAlias = null) Adds a RIGHT JOIN clause to the query using the OrderProduct relation
 * @method     ChildGroupOrderProductQuery innerJoinOrderProduct($relationAlias = null) Adds a INNER JOIN clause to the query using the OrderProduct relation
 *
 * @method     ChildGroupOrderProduct findOne(ConnectionInterface $con = null) Return the first ChildGroupOrderProduct matching the query
 * @method     ChildGroupOrderProduct findOneOrCreate(ConnectionInterface $con = null) Return the first ChildGroupOrderProduct matching the query, or a new ChildGroupOrderProduct object populated from the query conditions when no match is found
 *
 * @method     ChildGroupOrderProduct findOneById(int $id) Return the first ChildGroupOrderProduct filtered by the id column
 * @method     ChildGroupOrderProduct findOneBySubOrderId(int $sub_order_id) Return the first ChildGroupOrderProduct filtered by the sub_order_id column
 * @method     ChildGroupOrderProduct findOneByOrderProductId(int $order_product_id) Return the first ChildGroupOrderProduct filtered by the order_product_id column
 *
 * @method     array findById(int $id) Return ChildGroupOrderProduct objects filtered by the id column
 * @method     array findBySubOrderId(int $sub_order_id) Return ChildGroupOrderProduct objects filtered by the sub_order_id column
 * @method     array findByOrderProductId(int $order_product_id) Return ChildGroupOrderProduct objects filtered by the order_product_id column
 *
 */
abstract class GroupOrderProductQuery extends ModelCriteria
{

    /**
     * Initializes internal state of \GroupOrder\Model\Base\GroupOrderProductQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'thelia', $modelName = '\\GroupOrder\\Model\\GroupOrderProduct', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildGroupOrderProductQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildGroupOrderProductQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof \GroupOrder\Model\GroupOrderProductQuery) {
            return $criteria;
        }
        $query = new \GroupOrder\Model\GroupOrderProductQuery();
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
     * @return ChildGroupOrderProduct|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = GroupOrderProductTableMap::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(GroupOrderProductTableMap::DATABASE_NAME);
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
     * @return   ChildGroupOrderProduct A model object, or null if the key is not found
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT ID, SUB_ORDER_ID, ORDER_PRODUCT_ID FROM group_order_product WHERE ID = :p0';
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
            $obj = new ChildGroupOrderProduct();
            $obj->hydrate($row);
            GroupOrderProductTableMap::addInstanceToPool($obj, (string) $key);
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
     * @return ChildGroupOrderProduct|array|mixed the result, formatted by the current formatter
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
     * @return ChildGroupOrderProductQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(GroupOrderProductTableMap::ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return ChildGroupOrderProductQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(GroupOrderProductTableMap::ID, $keys, Criteria::IN);
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
     * @return ChildGroupOrderProductQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(GroupOrderProductTableMap::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(GroupOrderProductTableMap::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GroupOrderProductTableMap::ID, $id, $comparison);
    }

    /**
     * Filter the query on the sub_order_id column
     *
     * Example usage:
     * <code>
     * $query->filterBySubOrderId(1234); // WHERE sub_order_id = 1234
     * $query->filterBySubOrderId(array(12, 34)); // WHERE sub_order_id IN (12, 34)
     * $query->filterBySubOrderId(array('min' => 12)); // WHERE sub_order_id > 12
     * </code>
     *
     * @see       filterByGroupOrderSubOrder()
     *
     * @param     mixed $subOrderId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGroupOrderProductQuery The current query, for fluid interface
     */
    public function filterBySubOrderId($subOrderId = null, $comparison = null)
    {
        if (is_array($subOrderId)) {
            $useMinMax = false;
            if (isset($subOrderId['min'])) {
                $this->addUsingAlias(GroupOrderProductTableMap::SUB_ORDER_ID, $subOrderId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($subOrderId['max'])) {
                $this->addUsingAlias(GroupOrderProductTableMap::SUB_ORDER_ID, $subOrderId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GroupOrderProductTableMap::SUB_ORDER_ID, $subOrderId, $comparison);
    }

    /**
     * Filter the query on the order_product_id column
     *
     * Example usage:
     * <code>
     * $query->filterByOrderProductId(1234); // WHERE order_product_id = 1234
     * $query->filterByOrderProductId(array(12, 34)); // WHERE order_product_id IN (12, 34)
     * $query->filterByOrderProductId(array('min' => 12)); // WHERE order_product_id > 12
     * </code>
     *
     * @see       filterByOrderProduct()
     *
     * @param     mixed $orderProductId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGroupOrderProductQuery The current query, for fluid interface
     */
    public function filterByOrderProductId($orderProductId = null, $comparison = null)
    {
        if (is_array($orderProductId)) {
            $useMinMax = false;
            if (isset($orderProductId['min'])) {
                $this->addUsingAlias(GroupOrderProductTableMap::ORDER_PRODUCT_ID, $orderProductId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($orderProductId['max'])) {
                $this->addUsingAlias(GroupOrderProductTableMap::ORDER_PRODUCT_ID, $orderProductId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GroupOrderProductTableMap::ORDER_PRODUCT_ID, $orderProductId, $comparison);
    }

    /**
     * Filter the query by a related \GroupOrder\Model\GroupOrderSubOrder object
     *
     * @param \GroupOrder\Model\GroupOrderSubOrder|ObjectCollection $groupOrderSubOrder The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGroupOrderProductQuery The current query, for fluid interface
     */
    public function filterByGroupOrderSubOrder($groupOrderSubOrder, $comparison = null)
    {
        if ($groupOrderSubOrder instanceof \GroupOrder\Model\GroupOrderSubOrder) {
            return $this
                ->addUsingAlias(GroupOrderProductTableMap::SUB_ORDER_ID, $groupOrderSubOrder->getId(), $comparison);
        } elseif ($groupOrderSubOrder instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(GroupOrderProductTableMap::SUB_ORDER_ID, $groupOrderSubOrder->toKeyValue('PrimaryKey', 'Id'), $comparison);
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
     * @return ChildGroupOrderProductQuery The current query, for fluid interface
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
     * Filter the query by a related \Thelia\Model\OrderProduct object
     *
     * @param \Thelia\Model\OrderProduct|ObjectCollection $orderProduct The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGroupOrderProductQuery The current query, for fluid interface
     */
    public function filterByOrderProduct($orderProduct, $comparison = null)
    {
        if ($orderProduct instanceof \Thelia\Model\OrderProduct) {
            return $this
                ->addUsingAlias(GroupOrderProductTableMap::ORDER_PRODUCT_ID, $orderProduct->getId(), $comparison);
        } elseif ($orderProduct instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(GroupOrderProductTableMap::ORDER_PRODUCT_ID, $orderProduct->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByOrderProduct() only accepts arguments of type \Thelia\Model\OrderProduct or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the OrderProduct relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildGroupOrderProductQuery The current query, for fluid interface
     */
    public function joinOrderProduct($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('OrderProduct');

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
            $this->addJoinObject($join, 'OrderProduct');
        }

        return $this;
    }

    /**
     * Use the OrderProduct relation OrderProduct object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \Thelia\Model\OrderProductQuery A secondary query class using the current class as primary query
     */
    public function useOrderProductQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinOrderProduct($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'OrderProduct', '\Thelia\Model\OrderProductQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildGroupOrderProduct $groupOrderProduct Object to remove from the list of results
     *
     * @return ChildGroupOrderProductQuery The current query, for fluid interface
     */
    public function prune($groupOrderProduct = null)
    {
        if ($groupOrderProduct) {
            $this->addUsingAlias(GroupOrderProductTableMap::ID, $groupOrderProduct->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the group_order_product table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(GroupOrderProductTableMap::DATABASE_NAME);
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
            GroupOrderProductTableMap::clearInstancePool();
            GroupOrderProductTableMap::clearRelatedInstancePool();

            $con->commit();
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }

        return $affectedRows;
    }

    /**
     * Performs a DELETE on the database, given a ChildGroupOrderProduct or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or ChildGroupOrderProduct object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(GroupOrderProductTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(GroupOrderProductTableMap::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();


        GroupOrderProductTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            GroupOrderProductTableMap::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }
    }

} // GroupOrderProductQuery
