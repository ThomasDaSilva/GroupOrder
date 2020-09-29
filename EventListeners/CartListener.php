<?php
/**
 * Created by PhpStorm.
 * User: nicolasbarbey
 * Date: 24/09/2020
 * Time: 10:14
 */

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
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Cart\CartEvent;
use Thelia\Core\Event\Order\OrderProductEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Model\CartItem;
use Thelia\Model\CartItemQuery;

class CartListener implements EventSubscriberInterface
{
    /** @var Container $container */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
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
        if ($subCustomerId = $this->getRequest()->getSession()->get("GroupOrderSelectedSubCustomer")) {

            $groupOrderCartItem = null;

            $foundItems = CartItemQuery::create()
                ->filterByCartId($event->getCart()->getId())
                ->filterByProductId($event->getProduct())
                ->filterByProductSaleElementsId($event->getProductSaleElementsId())
                ->find();

            $foundItemsIds = array_map(static function($item) {return $item->getId();}, $foundItems);

            if ($foundItemsIds) {
                $groupOrderCartItem = GroupOrderCartItemQuery::create()->filterBySubCustomerId($subCustomerId)->filterByCartItemId($foundItemsIds)->findOne();
            }

            if ($groupOrderCartItem) {
                /** @var GroupOrderCartItem $groupOrderCartItem */
                $groupOrderCartItem->getCartItem()->addQuantity($event->getQuantity())->save();

                $event->stopPropagation();
            }
            $event->setNewness(true);
        }
    }

    /**
     * @param CartEvent $event
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function addItem(CartEvent $event)
    {
        if ($subCustomerId = $this->getRequest()->getSession()->get("GroupOrderSelectedSubCustomer")) {
            $groupOrderCartItem = new GroupOrderCartItem();
            $groupOrderCartItem
                ->setSubCustomerId($subCustomerId)
                ->setCartItemId($event->getCartItem()->getId())
                ->save();
        }
    }

    /**
     * @param OrderProductEvent $event
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function addOrderItem(OrderProductEvent $event)
    {
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
                $subOrder = GroupOrderSubOrderQuery::create()
                    ->filterByGroupOrderId($groupOrder->getId())
                    ->filterBySubCustomerId($subCustomer->getId())
                    ->findOne();
                if (!$subOrder && $subCustomer->getGroupOrderCartItems()->getData()) {
                    $subOrder = new GroupOrderSubOrder();
                    $subOrder
                        ->setSubCustomerId($subCustomer->getId())
                        ->setGroupOrderId($groupOrder->getId())
                        ->save();
                }

                /** @var GroupOrderCartItem $cartItem */
                foreach ($subCustomer->getGroupOrderCartItems() as $cartItem) {
                    if ($cartItem->getCartItemId() === $event->getCartItemId()) {
                        $groupOrderProduct = new GroupOrderProduct();
                        $groupOrderProduct
                            ->setSubOrderId($subOrder->getId())
                            ->setOrderProductId($event->getId())
                            ->save();

                        $cartItem->delete();
                    }
                }
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
                ['addItem', 110]
            ],
            TheliaEvents::CART_DELETEITEM => ['deleteItem', 150],
            TheliaEvents::ORDER_PRODUCT_AFTER_CREATE => ['addOrderItem', 110]
        ];
    }

}