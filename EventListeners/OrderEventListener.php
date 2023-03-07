<?php

namespace GroupOrder\EventListeners;

use GroupOrder\Model\GroupOrderCartQuery;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Model\ModuleQuery;

class OrderEventListener implements EventSubscriberInterface
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::ORDER_PAY => ['onOrderPay', 127],
        ];
    }

    public function onOrderPay(OrderEvent $event)
    {
        $placedOrder = $event->getPlacedOrder();

        if(null === $placedOrder){
            return;
        }

        $paymentModuleId = $placedOrder->getPaymentModuleId();

        $paymentModule = ModuleQuery::create()->findPk($paymentModuleId);

        if ($paymentModule->getCode() === "ETransaction") {
            return ;
        }

        $cart = $this->request->getSession()->getSessionCart();
        $groupCartOrder = GroupOrderCartQuery::create()
            ->filterByCartId($cart->getId())
            ->filterByStatus(0)
            ->findOne();

        if (null !== $groupCartOrder) {
            $groupCartOrder
                ->setStatus(2)
                ->save();
        }
    }
}