<?php

namespace GroupOrder\Loop;

use GroupOrder\Model\GroupOrderProduct;
use GroupOrder\Model\GroupOrderProductQuery;
use GroupOrder\Model\Map\GroupOrderSubCustomerTableMap;
use GroupOrder\Model\Map\GroupOrderSubOrderTableMap;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

class SubOrderProductLoop extends BaseLoop implements PropelSearchLoopInterface
{
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntListTypeArgument('order_id')
        );
    }

    public function buildModelCriteria()
    {
        $query = GroupOrderProductQuery::create()
        ->useGroupOrderSubOrderQuery()
            ->withColumn(GroupOrderSubOrderTableMap::COL_SUB_CUSTOMER_ID, "COL_SUB_CUSTOMER_ID")
            ->useGroupOrderSubCustomerQuery()
                ->withColumn(GroupOrderSubCustomerTableMap::COL_FIRST_NAME, "COL_SUB_CUSTOMER_FIRSTNAME")
                ->withColumn(GroupOrderSubCustomerTableMap::COL_LAST_NAME, "COL_SUB_CUSTOMER_LASTNAME")
            ->endUse()
            ->useGroupOrderQuery()
                ->filterByOrderId($this->getOrderId())
            ->endUse()
        ->endUse()
        ->orderBySubOrderId();

        return $query;
    }

    public function parseResults(LoopResult $loopResult)
    {
        /** @var GroupOrderProduct $orderProduct */
        foreach ($loopResult->getResultDataCollection() as $orderProduct){

            $loopResultRow = new LoopResultRow($orderProduct);
            $loopResultRow
                ->set("ID", $orderProduct->getId())
                ->set("ORDER_PRODUCT_ID", $orderProduct->getOrderProductId())
                ->set("SUB_CUSTOMER_ID", $orderProduct->getVirtualColumn("COL_SUB_CUSTOMER_ID"))
                ->set("SUB_CUSTOMER_FIRSTNAME", $orderProduct->getVirtualColumn("COL_SUB_CUSTOMER_FIRSTNAME"))
                ->set("SUB_CUSTOMER_LASTNAME", $orderProduct->getVirtualColumn("COL_SUB_CUSTOMER_LASTNAME"))

            ;

            $loopResult->addRow($loopResultRow);
        }
        return $loopResult;
    }



}