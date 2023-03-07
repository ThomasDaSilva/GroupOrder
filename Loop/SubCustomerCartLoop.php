<?php

namespace GroupOrder\Loop;


use GroupOrder\Model\GroupOrderCartItem;
use GroupOrder\Model\GroupOrderCartItemQuery;
use GroupOrder\Model\GroupOrderCartQuery;
use GroupOrder\Model\GroupOrderMainCustomerQuery;
use GroupOrder\Model\GroupOrderSubCustomer;
use GroupOrder\Model\GroupOrderSubCustomerQuery;
use GroupOrder\Model\Map\GroupOrderSubCustomerTableMap;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

class SubCustomerCartLoop extends BaseLoop implements PropelSearchLoopInterface
{
    protected function getArgDefinitions()
    {
        return new ArgumentCollection();
    }

    public function buildModelCriteria()
    {
        $query = GroupOrderCartItemQuery::create()
            ->useGroupOrderSubCustomerQuery()
                ->withColumn(GroupOrderSubCustomerTableMap::COL_FIRST_NAME, 'FIRSTNAME')
                ->withColumn(GroupOrderSubCustomerTableMap::COL_LAST_NAME, 'LASTNAME')
            ->endUse();

        $theliaCustomer = $this->getCurrentRequest()->getSession()->getCustomerUser();

        if ($theliaCustomer) {
            $mainCustomer = GroupOrderMainCustomerQuery::create()
                ->filterByCustomerId($theliaCustomer->getId())
                ->findOne();

            if ($mainCustomer) {
                $mainCustomerId = $mainCustomer->getId();
            }
        }

        if ($mainCustomerId) {
            $query
                ->useGroupOrderCartQuery()
                    ->filterByStatus(0)
                    ->filterByMainCustomerId($mainCustomerId)
                ->endUse();
        }

        return $query->groupBySubCustomerId();
    }

    public function parseResults(LoopResult $loopResult)
    {
        $currentSubcustomer = $this->getCurrentRequest()->getSession()->get("GroupOrderSelectedSubCustomer");

        /** @var GroupOrderCartItem $subCustomer */
        foreach ($loopResult->getResultDataCollection() as $subCustomer) {
            $loopResultRow = new LoopResultRow($subCustomer);
            $loopResultRow
                ->set("SUBCUSTOMER_ID", $subCustomer->getSubCustomerId())
                ->set("FIRSTNAME", $subCustomer->getVirtualColumn('FIRSTNAME'))
                ->set("LASTNAME", $subCustomer->getVirtualColumn('LASTNAME'))
                ->set("ISCURRENT_SUBCUSTOMER", ($currentSubcustomer == $subCustomer->getSubCustomerId()) ? 1 : 0);

            $loopResult->addRow($loopResultRow);
        }
        return $loopResult;
    }

}