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
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Model\Cart;
use Thelia\Model\CartQuery;
use Thelia\Tools\URL;
use Symfony\Component\Routing\Annotation\Route;


#[Route("/cart", name: "group_order_cart_")]
class CartController extends BaseFrontController
{
    /**
     * @throws PropelException
     */
    #[Route("/sub-customer", name: "validate")]
    public function validate(RequestStack $requestStack, Session $session): Response|RedirectResponse
    {
        $subCustomerId = $requestStack->getCurrentRequest()->getSession()->get("GroupOrderLoginSubCustomer");
        $mainCustomerId = $requestStack->getCurrentRequest()->getSession()->get("GroupOrderMainCustomer");

        try {
            if ($subCustomerId && $mainCustomerId) {
                $cartItems = $session->getSessionCart()->getCartItems();

                $mainCustomer = GroupOrderMainCustomerQuery::create()->filterById($mainCustomerId)->findOne();

                $customer = $mainCustomer->getCustomer();

                $subCustomer = GroupOrderSubCustomerQuery::create()->filterById($subCustomerId)->findOne();

                if (!$mainCart = CartQuery::create()->filterByCustomerId($customer->getId())->findOne()) {
                    $mainCart = new Cart();
                    $mainCart->setCustomerId($customer->getId());
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
        } catch (\Exception $e) {
            // todo
        }

        return $this->generateRedirect(URL::getInstance()->absoluteUrl("/cart"));
    }
}