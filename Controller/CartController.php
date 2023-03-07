<?php

namespace GroupOrder\Controller;

use GroupOrder\Model\Base\GroupOrderMainCustomerQuery;
use GroupOrder\Model\GroupOrderCart;
use GroupOrder\Model\GroupOrderCartItem;
use GroupOrder\Model\GroupOrderCartItemQuery;
use GroupOrder\Model\GroupOrderCartQuery;
use GroupOrder\Model\GroupOrderSubCustomerQuery;
use GroupOrder\Service\GroupOrderService;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\Event\Cart\CartCreateEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Core\Template\ParserContext;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Model\Cart;
use Thelia\Model\CartItem;
use Thelia\Model\CartQuery;
use Thelia\Tools\URL;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("", name="group_order_cart_")
 */

class CartController extends BaseFrontController
{
    /**
     * @Route("/cart/sub-customer", name="validate")
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function validate(Session $session, RequestStack $requestStack)
    {
        $subCustomerId = $requestStack->getSession()->get("GroupOrderLoginSubCustomer");
        $mainCustomerId = $requestStack->getSession()->get("GroupOrderMainCustomer");

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

    /**
     * @Route("/grouporder/cart-order/create", name="create_order")
     */
    public function createOrder(RequestStack $requestStack, EventDispatcher $dispatcher, ParserContext $parserContext)
    {
        $forms = $this->createForm("order_on_cart_form");
        $message = "";

        try {
            $form = $this->validateForm($forms);

            $userId = $requestStack->getCurrentRequest()->getSession()->getCustomerUser()->getId();
            $maincustomer = GroupOrderMainCustomerQuery::create()->findOneByCustomerId($userId);

            $oldCartOrders = GroupOrderCartQuery::create()
                ->filterByMainCustomerId($maincustomer->getId())
                ->filterByStatus(0)
                ->find();


            $dispatcher->dispatch( $newCartEvent = new CartCreateEvent(), TheliaEvents::CART_CREATE_NEW);

            $cart = $requestStack->getCurrentRequest()->getSession()->getSessionCart($dispatcher);
            $cart->save();
            $requestStack->getCurrentRequest()->getSession()->setSessionCart($cart);

            /** @var GroupOrderCart $oldCartOrder */
            foreach ($oldCartOrders as $oldCartOrder) {
                $oldCartOrder->setStatus(1)->save();
            }

            $orderCart = GroupOrderCartQuery::create()
                ->filterByMainCustomerId($maincustomer->getId())
                ->filterByCartId($requestStack->getCurrentRequest()->getSession()->getSessionCart()->getId())
                ->findOneOrCreate();

            $orderCart
                ->setTitle($form->get('title')->getData())
                ->setStatus(0)
                ->setDateEnd($form->get('date_end')->getData())
                ->save();

            $subCustomerIds = $form->get('sub_customer_id')->getData();

            foreach ($subCustomerIds as $subCustomerId) {
                $subCustomer = GroupOrderCartItemQuery::create()
                    ->filterByGroupOrderCartId($orderCart->getId())
                    ->filterBySubCustomerId($subCustomerId)
                    ->find();

                if (count($subCustomer->getData())) {
                    continue;
                }

                (new GroupOrderCartItem)
                    ->setSubCustomerId($subCustomerId)
                    ->setGroupOrderCartId($orderCart->getId())
                    ->save();
            }

            return $this->generateSuccessRedirect($forms);

        } catch (FormValidationException $exf) {
            $message = $exf->getMessage();
        } catch (PropelException $exp) {
            $message .= " | " . $exp->getMessage();
        } catch (\Exception $e) {
            $message .= " | " . $e->getMessage();
        }

        if ($message) {
            $forms->setErrorMessage($message);

            $parserContext
                ->setGeneralError($message)
                ->addForm($forms);
        }

        return $this->generateErrorRedirect($forms);
    }

    /**
     * @Route("/grouporder/cart-order/close", name="close_order")
     */
    public function closeOrder(RequestStack $requestStack)
    {
        $id = $requestStack->getCurrentRequest()->get("id");

        $cartOrder = GroupOrderCartQuery::create()
            ->filterById($id)
            ->findOne();

        if ($cartOrder) {
            $cartOrder
                ->setStatus(1)
                ->save();
        }

        $requestStack->getCurrentRequest()->getSession()->set('GroupOrderSelectedSubCustomer', null);

        return $this->generateRedirect(URL::getInstance()->absoluteUrl("/account"));
    }

    /**
     * @Route("/grouporder/cart-order/topick", name="topick_order")
     */
    public function topickOrder(RequestStack $requestStack)
    {
        $id = $requestStack->getCurrentRequest()->get("id");

        $cartOrder = GroupOrderCartQuery::create()
            ->filterById($id)
            ->findOne();

        if ($cartOrder) {
            $cartOrder
                ->setStatus(2)
                ->save();
        }

        return $this->generateRedirect(URL::getInstance()->absoluteUrl("/account-cse-order"));
    }

    /**
     * @Route("/order/sub-customer/invoice", name="sub_customer_invoice")
     */
    public function subCustomerInvoice(RequestStack $requestStack, GroupOrderService $service)
    {
        $subCustomerId = $requestStack->getCurrentRequest()->getSession()->get("GroupOrderLoginSubCustomer", null);

        if (false === $service->isSubCustomer($subCustomerId)) {
            return $this->generateRedirect('/cart');
        }

        $cart = $requestStack->getCurrentRequest()->getSession()->getSessionCart();

        /** @var GroupOrderCart $mainCartOrder */
        $mainCartOrder = $service->getMainCartOrder($subCustomerId);

        /** @var GroupOrderCartItem $cartOrderCartItem */
        foreach ($mainCartOrder->getGroupOrderCartItems() as $cartOrderCartItem) {
            if ($cartOrderCartItem->getSubCustomerId() !== $subCustomerId) {
                continue;
            }

            if ($cartOrderCartItem->getCartItem()) {
                $cartOrderCartItem->getCartItem()->delete();
            }
        }

        /** @var CartItem $cartItem */
        foreach ($cart->getCartItems() as $cartItem) {
            $cartItem->setCartId($mainCartOrder->getCartId())->save();

            (new GroupOrderCartItem())
                ->setSubCustomerId($subCustomerId)
                ->setCartItemId($cartItem->getId())
                ->setGroupOrderCartId($mainCartOrder->getId())
                ->save();
        }

        return $this->generateRedirect("/order-placed-group-order");
    }

    /**
     * @Route("/cart/sub-customer/unselect-all", name="sub_customer_unselect_all")
     */
    public function unSelectSubCustomer(RequestStack $requestStack)
    {
        $requestStack->getCurrentRequest()->getSession()->set("GroupOrderSelectedSubCustomer", null);
        if($url = $requestStack->getCurrentRequest()->get('url')){
            return $this->generateRedirect($url);
        }

        return $this->generateRedirect("/cart");
    }
}