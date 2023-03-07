<?php

namespace GroupOrder\Service;

use GroupOrder\Model\GroupOrder;
use GroupOrder\Model\GroupOrderCart;
use GroupOrder\Model\GroupOrderCartItem;
use GroupOrder\Model\GroupOrderCartItemQuery;
use GroupOrder\Model\GroupOrderCartQuery;
use GroupOrder\Model\GroupOrderMainCustomer;
use GroupOrder\Model\GroupOrderMainCustomerQuery;
use GroupOrder\Model\GroupOrderProduct;
use GroupOrder\Model\GroupOrderSubCustomer;
use GroupOrder\Model\GroupOrderSubCustomerQuery;
use GroupOrder\Model\GroupOrderSubOrder;
use GroupOrder\Model\GroupOrderSubOrderQuery;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Model\AddressQuery;
use Thelia\Model\AttributeCombination;
use Thelia\Model\Cart;
use Thelia\Model\CustomerQuery;
use Thelia\Model\OrderProduct;
use Thelia\Model\ProductSaleElements;
use Thelia\TaxEngine\Calculator;
use Thelia\Tools\MoneyFormat;

class GroupOrderService
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function isSubCustomer($subCustomerId)
    {
        $subcustomer = GroupOrderSubCustomerQuery::create()->findPk($subCustomerId);

        if ($subcustomer) {
            return true;
        }

        return false;
    }

    public function getMainCartOrder($subCustomerId)
    {
        $cartOrder = GroupOrderCartQuery::create()
            ->useGroupOrderMainCustomerQuery()
            ->useGroupOrderSubCustomerQuery()
            ->filterById($subCustomerId)
            ->endUse()
            ->endUse()
            ->filterByStatus(0)
            ->findOne();

        if ($cartOrder) {
            return $cartOrder;
        }

        return null;
    }

    public function getSubCustomerTaxedCart($subCustomerId, $groupOrderCart = null)
    {
        $cartItems = [];

        if (null === $subCustomerId) {
            return $cartItems;
        }

        $subCustomerCartItems = GroupOrderCartItemQuery::create()
            ->filterBySubCustomerId($subCustomerId);

        if ($groupOrderCart) {
            $subCustomerCartItems->filterByGroupOrderCartId($groupOrderCart);
        }

        $subCustomerCartItems->find();

        $calc = new Calculator();

        $lang = $this->request->getSession()->getLang();

        /** @var GroupOrderCartItem $subCustomerCartItem */
        foreach ($subCustomerCartItems as $subCustomerCartItem) {
            if (null === $subCustomerCartItem->getCartItem()) {
                continue;
            }

            $product = $subCustomerCartItem->getCartItem()->getProduct();
            $customer = CustomerQuery::create()->filterById($this->request->getSession()->getCustomerUser()->getId())->findOne();
            $address = AddressQuery::create()->filterByCustomerId($customer->getId())->findOne();
            $calc->load($product, $address->getCountry());
            $cartItem = $subCustomerCartItem->getCartItem();
            $product = $cartItem->getProductSaleElements()->getProduct();

            $price = $calc->getTaxedPrice($cartItem->getPrice());

            if($cartItem->getPromo()){
                $price = $calc->getTaxedPrice($cartItem->getPromoPrice());
            }

            $cartItems[] = [
                'cartItem_id' => $cartItem->getId(),
                'quantity' => $cartItem->getQuantity(),
                'price' => MoneyFormat::getInstance($this->request)->formatByCurrency($price),
                'title' => $product->setLocale($lang->getLocale())->getTitle(),
                'format' => $this->getFormatProduct($cartItem->getProductSaleElements()),
                'total' => $cartItem->getQuantity() * $price,
            ];
        }

        return $cartItems;
    }

    public function getSubcustomersByOrder($orderId)
    {
        $subcustomers = GroupOrderSubCustomerQuery::create()
            ->useGroupOrderSubOrderQuery()
                ->useGroupOrderProductQuery()
                ->endUse()
                ->useGroupOrderQuery()
                    ->filterByOrderId($orderId)
                ->endUse()
            ->endUse()
            ->groupById()
            ->find();

        return ($subcustomers)?$subcustomers->toArray():[];
    }

    public function getSubCustomersByCartOrder(GroupOrderMainCustomer $mainCustomer = null)
    {
        $subCustomers = [];

        if (null === $mainCustomer) {
            $theliaCustomer = $this->request->getSession()->getCustomerUser();

            $mainCustomer = GroupOrderMainCustomerQuery::create()
                ->filterByCustomerId($theliaCustomer->getId())
                ->findOne();
        }

        if (null === $mainCustomer) {
            return $subCustomers;
        }

        $groupOrdersCart = GroupOrderCartQuery::create()
            ->filterByMainCustomerId($mainCustomer->getId())
            ->find();

        /** @var GroupOrderCart $groupOrderCart */
        foreach ($groupOrdersCart as $groupOrderCart) {
            if (null === $groupOrderCart->getCartId()) {
                continue;
            }

            $currentSubCustomers = $groupOrderCart->getCurrentSubCustomers();

            foreach ($currentSubCustomers as $currentSubCustomerId) {
                $subCustomerCartItems = $this->getSubCustomerTaxedCart($currentSubCustomerId, $groupOrderCart->getId());
                $subCustomer = GroupOrderSubCustomerQuery::create()->findPk($currentSubCustomerId);

                $subCustomers[$groupOrderCart->getId()][$subCustomer->getId()] = [
                    "name" => $subCustomer->getComplexName(),
                    "totalCart" => $this->getTotalCartBySubCustomers($subCustomerCartItems),
                    "cartItems" => $subCustomerCartItems
                ];
            }
        }

        return $subCustomers;
    }

    public function getTotalCartBySubCustomers($subCustomerCartItems)
    {
        $total = 0;

        foreach ($subCustomerCartItems as $subCustomerCartItem) {
            $total += $subCustomerCartItem["total"];
        }

        return $total;
    }

    public function getSubCustomerCartItems($subCustomerId)
    {
        $cartOrder = $this->getMainCartOrder($subCustomerId);
        $tab = [];

        if (null == $cartOrder) {
            return $tab;
        }

        $groupOrderCartItems = GroupOrderCartItemQuery::create()
            ->filterBySubCustomerId($subCustomerId)
            ->filterByGroupOrderCartId($cartOrder->getId())
            ->find();

        /** @var GroupOrderCartItem $groupOrderCartItem */
        foreach ($groupOrderCartItems->getData() as $groupOrderCartItem) {
            $tab [] = $groupOrderCartItem->getCartItem();
        }

        return $tab;
    }

    public function getFormatProduct(ProductSaleElements $pse)
    {
        $combinations = $pse->getAttributeCombinations();

        $format = "";

        /** @var AttributeCombination $combination */
        foreach ($combinations as $combination) {
            $locale = $this->request->getSession()->getLang()->getLocale();
            $attributeAv = $combination->getAttributeAv();

            $format .= $attributeAv->setLocale($locale)->getTitle();
        }

        return $format;
    }

    public function getSubCustomerId()
    {
        $subCustomerId = $this->request->getSession()->get("GroupOrderSelectedSubCustomer", null);

        if (null == $subCustomerId) {
            $subCustomerId = $this->request->getSession()->get("GroupOrderLoginSubCustomer", null);
        }

        return $subCustomerId;
    }

    public function getCurrentCartOrder(Cart $cart)
    {
        $cartOrder = GroupOrderCartQuery::create()
            ->filterByCartId($cart->getId())
            ->filterByStatus(0)
            ->findOne();

        if ($cartOrder) {
            return $cartOrder;
        }

        return null;
    }

    public function saveGroupOrderProduct(GroupOrder $groupOrder, GroupOrderSubCustomer $subCustomer, OrderProduct $orderProduct, $cartItemId)
    {
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
            if ($cartItem->getCartItemId() !== $cartItemId) {
                continue;
            }

            $groupOrderProduct = new GroupOrderProduct();
            $groupOrderProduct
                ->setSubOrderId($subOrder->getId())
                ->setOrderProductId($orderProduct->getId())
                ->save();
        }
    }
}