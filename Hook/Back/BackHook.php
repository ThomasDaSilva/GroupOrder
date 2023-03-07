<?php

namespace GroupOrder\Hook\Back;

use GroupOrder\Model\GroupOrderMainCustomerQuery;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

class BackHook extends BaseHook
{
    public function customerEditJs(HookRenderEvent $event)
    {
        $customerId = $event->getArgument('customer_id');

        $mainCustomer = GroupOrderMainCustomerQuery::create()
            ->filterByCustomerId($customerId)
            ->findOne();

        $isMainCustomerActive = 0;

        if ($mainCustomer && $mainCustomer->getActive()) {
            $isMainCustomerActive = 1;
        }

        $event->add($this->render('customerEditJs.html',
            [
                "isMainCustomerActive" => $isMainCustomerActive
            ])
        );
    }
}