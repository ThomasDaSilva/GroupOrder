<?php
/**
 * Created by PhpStorm.
 * User: nicolasbarbey
 * Date: 23/09/2020
 * Time: 10:09
 */

namespace GroupOrder\Loop;


use GroupOrder\Model\GroupOrderMainCustomer;
use GroupOrder\Model\GroupOrderMainCustomerQuery;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

class MainCustomerLoop extends BaseLoop implements PropelSearchLoopInterface
{
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntListTypeArgument('id'),
            Argument::createIntListTypeArgument('sub_customer_id'),
            Argument::createIntListTypeArgument('customer_id'),
            Argument::createIntTypeArgument('active')
        );
    }

    public function buildModelCriteria()
    {
        $query = GroupOrderMainCustomerQuery::create();

        if ($ids = $this->getId()) {
            $query->filterById($ids);
        }

        if ($subCustomerIds = $this->getSubCustomerId()) {
            $query
                ->useGroupOrderSubCustomerQuery()
                ->filterById($subCustomerIds)
                ->endUse();
        }

        if ($customerIds = $this->getCustomerId()) {
            $query->filterByCustomerId($customerIds);
        } else {
            $theliaCustomer = $this->getCurrentRequest()->getSession()->getCustomerUser();

            if ($theliaCustomer) {
                $mainCustomer = GroupOrderMainCustomerQuery::create()
                    ->filterByCustomerId($theliaCustomer->getId())
                    ->findOne();

                if($mainCustomer){
                    $query->filterByCustomerId($mainCustomer->getCustomerId());
                }
            }
        }

        if ($this->getActive() === true) {
            $query->filterByActive(1);
        }

        return $query;
    }

    public function parseResults(LoopResult $loopResult)
    {
        /** @var GroupOrderMainCustomer $mainCustomer */
        foreach ($loopResult->getResultDataCollection() as $mainCustomer) {
            $loopResultRow = new LoopResultRow($mainCustomer);
            $loopResultRow
                ->set("ID", $mainCustomer->getId())
                ->set("CUSTOMER_ID", $mainCustomer->getCustomerId())
                ->set("GROUP_CODE", $mainCustomer->getCode());

            $loopResult->addRow($loopResultRow);
        }
        return $loopResult;
    }

}