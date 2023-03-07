<?php

namespace GroupOrder\Service;

use CustomerFamily\Service\CustomerFamilyService;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Model\CartItem;
use Thelia\Model\Customer;
use Thelia\Model\Map\ProductPriceTableMap;
use Thelia\Model\ProductSaleElements;
use Thelia\Model\ProductSaleElementsQuery;

class GroupOrderLoginService
{
    /**
     * @var CustomerFamilyService
     */
    private $customerFamilyService;

    public function __construct(CustomerFamilyService $customerFamilyService)
    {
        $this->customerFamilyService = $customerFamilyService;
    }

    /**
     * @param CartItem $cartItem
     * @return bool
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function updateCartItem(CartItem $cartItem, Customer $customer)
    {
        $isUpdated = false;
        $product = $cartItem->getProduct();

        $pse = ProductSaleElementsQuery::create()
            ->useProductPriceQuery()
            ->withColumn(ProductPriceTableMap::COL_PRICE, "price_PRICE")
            ->withColumn(ProductPriceTableMap::COL_PROMO_PRICE, "price_PROMO_PRICE")
            ->endUse()
            ->filterById($cartItem->getProductSaleElementsId())
            ->findOne();

        if (!$product || !$pse || $pse->getQuantity() <= 0) {
            throw new \Exception(' Missing Product ou PSE');
        }

        if ($pse->getQuantity() < $cartItem->getQuantity()) {
            $cartItem->setQuantity($pse->getQuantity());
            $isUpdated = true;
        }

        if ($pse->getPromo() != $cartItem->getPromo()) {
            $cartItem->setPromo($pse->getPromo());
            $isUpdated = true;
        }

        $this->updatePrice($cartItem, $pse, $customer, $isUpdated);

        return $isUpdated;
    }

    protected function updatePrice(CartItem $cartItem, ProductSaleElements $pse, Customer $customer, &$isUpdated, $currencyId = 1)
    {
        $prices = $this->customerFamilyService->calculateCustomerPsePrice(
            $pse,
            $customer->getId(),
            $currencyId
        );

        if ($prices['price'] !== null && $cartItem->getPrice() != $prices['price']) {
            $cartItem->setPrice($prices['price']);
            $isUpdated = true;
        }

        if ($prices['promoPrice'] !== null && $cartItem->getPromoPrice() != $prices['promoPrice']) {
            $cartItem->setPromoPrice($prices['promoPrice']);
            $isUpdated = true;
        }

        $cartItem->save();
    }
}