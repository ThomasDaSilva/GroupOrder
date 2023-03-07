<?php

namespace GroupOrder\EventListeners;

use GroupOrder\Model\GroupOrderCartQuery;
use GroupOrder\Model\GroupOrderMainCustomerQuery;
use GroupOrder\Service\GroupOrderLoginService;
use Propel\Runtime\Collection\Collection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Customer\CustomerLoginEvent;
use Thelia\Core\Event\DefaultActionEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Model\CartItem;
use Thelia\Model\CartQuery;

class LoginListener implements EventSubscriberInterface
{
    /** @var Request $request */
    protected $request;
    /**
     * @var GroupOrderLoginService
     */
    private $loginService;

    public function __construct(Request $request, GroupOrderLoginService $loginService)
    {
        $this->request = $request;
        $this->loginService = $loginService;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function login(CustomerLoginEvent $event)
    {
        $mainCustomer = GroupOrderMainCustomerQuery::create()
            ->filterByCustomerId($event->getCustomer()->getId())
            ->filterByActive(1)
            ->findOne();

        if (null === $mainCustomer) {
            return;
        }

        $cartMainCustomer = GroupOrderCartQuery::create()
            ->filterByMainCustomerId($mainCustomer->getId())
            ->filterByStatus(0)
            ->findOne();

        if ($cartMainCustomer) {
            $cart = CartQuery::create()->findPk($cartMainCustomer->getCartId());

            if ($cart) {
                $cartItemIdsUpdated = [];

                /** @var CartItem $cartItem */
                foreach ($cart->getCartItems() as $cartItem){
                    try {
                        $updated = $this->loginService->updateCartItem($cartItem, $mainCustomer->getCustomer());
                        if($updated){
                            $cartItemIdsUpdated[] = $cartItem->getId();
                            $cartItem->save();
                        }
                    } catch (\Exception $e) {
                        $cartItem->delete();
                        continue;
                    }
                }

                $this->getRequest()->getSession()->setSessionCart($cart);
            }
        }

        $this->getRequest()->getSession()->set('CurrentUserIsMainCustomer', $mainCustomer);
    }

    public function logout(DefaultActionEvent $event)
    {
        if ($this->getRequest()->getSession()->get('CurrentUserIsMainCustomer')) {
            $this->getRequest()->getSession()->set('CurrentUserIsMainCustomer', null);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::CUSTOMER_LOGIN => ["login", 128],
            TheliaEvents::CUSTOMER_LOGOUT => ["logout", 110]
        ];
    }
}