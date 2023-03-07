<?php

namespace GroupOrder\EventListeners;

use GroupOrder\Model\GroupOrder;
use GroupOrder\Model\GroupOrderCartItem;
use GroupOrder\Model\GroupOrderCartItemQuery;
use GroupOrder\Model\GroupOrderMainCustomer;
use GroupOrder\Model\GroupOrderMainCustomerQuery;
use GroupOrder\Model\GroupOrderProduct;
use GroupOrder\Model\GroupOrderQuery;
use GroupOrder\Model\GroupOrderSubCustomer;
use GroupOrder\Model\GroupOrderSubOrder;
use GroupOrder\Model\GroupOrderSubOrderQuery;
use GroupOrder\Service\GroupOrderService;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Thelia\Core\Event\Cart\CartEvent;
use Thelia\Core\Event\Order\OrderProductEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Model\CartItemQuery;
use Thelia\Model\OrderProductQuery;

class CartListener implements EventSubscriberInterface
{
    /** @var Container $container */
    protected $request;
    /**
     * @var GroupOrderService
     */
    private $service;

    public function __construct(Request $request, GroupOrderService $service)
    {
        $this->request = $request;
        $this->service = $service;
    }

    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param CartEvent $event
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function isNew(CartEvent $event)
    {
        if (null === $subCustomerId = $this->service->getSubCustomerId()) {
            return;
        }

        $foundItems = CartItemQuery::create()
            ->filterByCartId($event->getCart()->getId())
            ->filterByProductId($event->getProduct())
            ->filterByProductSaleElementsId($event->getProductSaleElementsId())
            ->useGroupOrderCartItemQuery()
            ->filterBySubCustomerId($subCustomerId)
            ->endUse()
            ->find();

        $foundItemsIds = array_map(static function ($item) {
            return $item->getId();
        }, $foundItems->getData());

        if (null == $foundItemsIds) {
            $event->setNewness(true);
            return;
        }

        $groupOrderCartItem = GroupOrderCartItemQuery::create()
            ->filterBySubCustomerId($subCustomerId)
            ->filterByCartItemId($foundItemsIds)
            ->findOne();

        /** @var GroupOrderCartItem $groupOrderCartItem */
        $groupOrderCartItem->getCartItem()->addQuantity($event->getQuantity())->save();

        $event->stopPropagation();

    }

    public function checkSubCustomer(CartEvent $event)
    {
        $mainCustomer = $this->getRequest()->getSession()->get("CurrentUserIsMainCustomer");

        if ($mainCustomer && null === $subCustomerId = $this->service->getSubCustomerId()) {
            $event->stopPropagation();

            throw new BadRequestHttpException(" Pas de subcustomer séléctionné");
        }
    }

    /**
     * @param CartEvent $event
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function addItem(CartEvent $event)
    {
        if (null === $subCustomerId = $this->service->getSubCustomerId()) {
            return;
        }

        $cart = $event->getCart();
        $currentCartOrder = $this->service->getCurrentCartOrder($cart);

        if ($currentCartOrder) {
            $currentCartOrder->getGroupOrderCartItems();

            $groupOrderCartItem = new GroupOrderCartItem();
            $groupOrderCartItem
                ->setSubCustomerId($subCustomerId)
                ->setCartItemId($event->getCartItem()->getId())
                ->setGroupOrderCartId($currentCartOrder->getId())
                ->save();
        }
    }

    /**
     * @param OrderProductEvent $event
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function addOrderItem(OrderProductEvent $event)
    {
        $orderProduct = OrderProductQuery::create()->findPk($event->getId());
        if (!$orderProduct) {
            return null;
        }

        /** @var GroupOrderMainCustomer $mainCustomer */
        if ($mainCustomer = $this->getRequest()->getSession()->get("CurrentUserIsMainCustomer")) {
            if (!$groupOrder = GroupOrderQuery::create()->filterByOrderId($event->getOrder()->getId())->findOne()) {
                $groupOrder = new GroupOrder();
                $groupOrder
                    ->setMainCustomerId($mainCustomer->getId())
                    ->setOrderId($event->getOrder()->getId())
                    ->save();
            }
            /** @var GroupOrderSubCustomer $subCustomer */
            foreach ($mainCustomer->getGroupOrderSubCustomers() as $subCustomer) {
                $this->service->saveGroupOrderProduct($groupOrder, $subCustomer, $orderProduct, $event->getCartItemId());
            }
        }
    }

    /**
     * @param CartEvent $event
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function deleteItem(CartEvent $event)
    {
        $customerId = null;
        if ($customer = $this->getRequest()->getSession()->getCustomerUser()) {
            $customerId = $customer->getId();
        }
        if ($mainCustomer = GroupOrderMainCustomerQuery::create()->filterByCustomerId($customerId)->findOne()) {
            $cartItem = GroupOrderCartItemQuery::create()->filterByCartItemId($event->getCartItemId());
            if ($cartItem) {
                $cartItem->delete();
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::CART_ADDITEM => [
                ['isNew', 150],
                ['addItem', 110],
                ['checkSubCustomer', 150]
            ],
            TheliaEvents::CART_DELETEITEM => ['deleteItem', 150],
            TheliaEvents::ORDER_PRODUCT_AFTER_CREATE => ['addOrderItem', 110]
        ];
    }
}