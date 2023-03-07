<?php

namespace GroupOrder\Loop;

use GroupOrder\Model\GroupOrderCart;
use GroupOrder\Model\GroupOrderCartQuery;
use GroupOrder\Model\GroupOrderMainCustomerQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

class OrderCartLoop extends BaseLoop implements PropelSearchLoopInterface
{
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntListTypeArgument('id', null),
            Argument::createIntListTypeArgument('cart_id', null),
            Argument::createIntListTypeArgument('main_customer_id', null),
            Argument::createIntListTypeArgument('only_active', null)
        );
    }

    public function buildModelCriteria()
    {
        $query = GroupOrderCartQuery::create();

        if ($ids = $this->getOnlyActive()) {
            $query->filterByStatus(0);
        }

        if ($ids = $this->getId()) {
            $query->filterById($ids);
        }

        if ($cartId = $this->getCartId()) {
            $query->filterByCartId($cartId);
        }

        $mainCustomerId = null;
        $theliaCustomer = $this->getCurrentRequest()->getSession()->getCustomerUser();

        if ($theliaCustomer) {
            $mainCustomer = GroupOrderMainCustomerQuery::create()
                ->filterByCustomerId($theliaCustomer->getId())
                ->findOne();

            if ($mainCustomer) {
                $mainCustomerId = $mainCustomer->getId();
            }
        }

        if ($customerId = $this->getMainCustomerId()) {
            $query->filterByMainCustomerId($customerId);
        } elseif ($mainCustomerId) {
            $query->filterByMainCustomerId($mainCustomerId);
        }

        return $query
            ->orderById(Criteria::DESC)
            ->limit(1);
    }

    public function parseResults(LoopResult $loopResult)
    {
        /** @var GroupOrderCart $orderCart */
        foreach ($loopResult->getResultDataCollection() as $orderCart) {
            $status = ($orderCart->getStatus() == 0) ? 0 : $orderCart->getStatus();
            $date = ($orderCart->getDateEnd())?$orderCart->getDateEnd()->format('d-m-Y'):null;

            $loopResultRow = new LoopResultRow($orderCart);
            $loopResultRow
                ->set("ID", $orderCart->getId())
                ->set("MAIN_CUSTOMER_ID", $orderCart->getMainCustomerId())
                ->set("CART_ID", $orderCart->getCartId())
                ->set("TITLE", $orderCart->getTitle())
                ->set("STATUS", $status)
                ->set("DATE_END", $date);

            $loopResult->addRow($loopResultRow);
        }
        return $loopResult;
    }
}