<?php
/**
 * Created by PhpStorm.
 * User: nicolasbarbey
 * Date: 25/09/2020
 * Time: 11:46
 */

namespace GroupOrder\EventListeners;

use GroupOrder\Model\GroupOrderMainCustomerQuery;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Customer\CustomerLoginEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Model\CartQuery;

class LoginListener implements EventSubscriberInterface
{
    /** @var Request $request */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function login(CustomerLoginEvent $event)
    {
        if (GroupOrderMainCustomerQuery::create()->filterByCustomerId($event->getCustomer()->getId())->findOne() &&
            $cart = CartQuery::create()->filterByCustomerId($event->getCustomer()->getId())->findOne()) {
            $this->getRequest()->getSession()->setSessionCart($cart);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::CUSTOMER_LOGIN => ["login", 128]
        ];
    }
}