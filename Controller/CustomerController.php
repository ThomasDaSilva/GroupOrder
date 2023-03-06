<?php
/**
 * Created by PhpStorm.
 * User: nicolasbarbey
 * Date: 22/09/2020
 * Time: 09:03
 */

namespace GroupOrder\Controller;


use GroupOrder\Form\SubCustomerForm;
use GroupOrder\Model\GroupOrderCartItem;
use GroupOrder\Model\GroupOrderCartItemQuery;
use GroupOrder\Model\GroupOrderSubCustomer;
use GroupOrder\Model\GroupOrderSubCustomerQuery;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Model\AddressQuery;
use Thelia\Model\CustomerQuery;
use Thelia\TaxEngine\Calculator;
use Thelia\Tools\MoneyFormat;
use Thelia\Tools\URL;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("", name="group_order_customer_")
 */
class CustomerController extends BaseFrontController
{
    /**
     * @Route("/GroupOrder/SubCustomer/CreateOrUpdate", name="create")
     * @Route("/GroupOrder/SubCustomer/CreateOrUpdate/{id}", name="update")
     */
    public function createOrUpdateSubCustomer(RequestStack $requestStack)
    {
        $subCustomerId = $requestStack->getCurrentRequest()->get('id');
        $mainCustomer = $requestStack->getCurrentRequest()->getSession()->get("CurrentUserIsMainCustomer");
        try {
            $form = $this->validateForm($this->createForm(SubCustomerForm::getName()));
            $plainPassword = $form->get('password')->getData();
            $password = password_hash($plainPassword, PASSWORD_BCRYPT);

            $subCustomer = new GroupOrderSubCustomer();
            if ($subCustomerId) {
                $subCustomer = GroupOrderSubCustomerQuery::create()->filterById($subCustomerId)->findOne();
            }

            $subCustomer
                ->setMainCustomerId($mainCustomer->getId())
                ->setFirstName($form->get('firstname')->getData())
                ->setLastName($form->get('lastname')->getData())
                ->setEmail($form->get('email')->getData())
                ->setAddress1($form->get('address1')->getData())
                ->setAddress2($form->get('address2')->getData())
                ->setAddress3($form->get('address3')->getData())
                ->setCity($form->get('city')->getData())
                ->setZipCode($form->get('zipcode')->getData())
                ->setCountryId($form->get('country_id')->getData())
                ->setLogin($form->get('login')->getData());

            if ($plainPassword) {
                $subCustomer->setPassword($password);
            }

            $subCustomer->save();

            return $this->generateRedirect(URL::getInstance()->absoluteUrl("/account"));

        } catch (\Exception $exception) {
            return $this->generateRedirect(URL::getInstance()->absoluteUrl("/account"));
        }
    }

    /**
     * @Route("/module/groupOrder/getSubCustomerCart", name="get_sub_customer_cart")
     * @return \Thelia\Core\HttpFoundation\Response
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getSubCustomerCart(RequestStack $requestStack)
    {
        $subCustomerId = $requestStack->getCurrentRequest()->get('sub_customer_id');

        $requestStack->getCurrentRequest()->getSession()->set('GroupOrderSelectedSubCustomer', null);

        $subCustomerCartItems = GroupOrderCartItemQuery::create()->filterBySubCustomerId($subCustomerId)->find();
        $calc = new Calculator();
        $cartItems = [];

        $lang = $requestStack->getCurrentRequest()->getSession()->getLang();
        /** @var GroupOrderCartItem $subCustomerCartItem */
        foreach ($subCustomerCartItems as $subCustomerCartItem) {
            $product = $subCustomerCartItem->getCartItem()->getProduct();
            $customer = CustomerQuery::create()
                ->filterById($requestStack->getCurrentRequest()->getSession()->getCustomerUser()->getId())->findOne();
            $address = AddressQuery::create()->filterByCustomerId($customer->getId())->findOne();
            $calc->load($product, $address->getCountry());
            $cartItem = $subCustomerCartItem->getCartItem();
            $product = $cartItem->getProductSaleElements()->getProduct();
            $cartItems[] = [
                'quantity' => $cartItem->getQuantity(),
                'price' => MoneyFormat::getInstance($requestStack->getCurrentRequest())
                    ->formatByCurrency($calc->getTaxedPrice($cartItem->getPrice())),
                'title' => $product->setLocale($lang->getLocale())->getTitle(),
            ];
        }

        $response = [
            "cartItems" => $cartItems
        ];

        $requestStack->getCurrentRequest()->getSession()->set('GroupOrderSelectedSubCustomer', $subCustomerId);

        return $this->jsonResponse(json_encode($response));
    }

    /**
     * @Route("/module/groupOrder/goHome", name="go_home")
     */
    public function goHome(RequestStack $requestStack)
    {
        $requestStack->getCurrentRequest()->getSession()->set('GroupOrderSelectedSubCustomer', null);
    }
}