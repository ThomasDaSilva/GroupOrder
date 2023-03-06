<?php
/**
 * Created by PhpStorm.
 * User: nicolasbarbey
 * Date: 25/09/2020
 * Time: 17:28
 */

namespace GroupOrder\Controller;


use GroupOrder\Model\Base\GroupOrderMainCustomerQuery;
use GroupOrder\Model\GroupOrderCartItem;
use GroupOrder\Model\GroupOrderSubCustomerQuery;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Model\Cart;
use Thelia\Model\CartQuery;
use Thelia\Tools\URL;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/cart", name="group_order_cart_")
 */

class CartController extends BaseFrontController
{
    /**
     * @Route("/sub-customer", name="validate")
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function validate(Session $session)
    {
        $subCustomerId = $this->getRequest()->getSession()->get("GroupOrderLoginSubCustomer");
        $mainCustomerId = $this->getRequest()->getSession()->get("GroupOrderMainCustomer");

        if ($subCustomerId && $mainCustomerId) {
            $cartItems = $session->getSessionCart()->getCartItems();

            $mainCustomer = GroupOrderMainCustomerQuery::create()->filterById($mainCustomerId)->findOne();

            $customer = $mainCustomer->getCustomer();

            $subCustomer = GroupOrderSubCustomerQuery::create()->filterById($subCustomerId)->findOne();

            if (!$mainCart = CartQuery::create()->filterByCustomerId($customer->getId())->findOne()) {
                $mainCart = new Cart();
                $mainCart
                    ->setCustomerId($customer->getId());
            }

            foreach ($cartItems as $cartItem) {
                $cartItem
                    ->setCartId($mainCart->getId())
                    ->save();

                $groupOrderCartItem = new GroupOrderCartItem();
                $groupOrderCartItem
                    ->setSubCustomerId($subCustomer->getId())
                    ->setCartItemId($cartItem->getId())
                    ->save();
            }

            $session->getSessionCart()->delete();

            return $this->render("valide-cart");
        }
        return $this->generateRedirect(URL::getInstance()->absoluteUrl("/cart"));
    }
}