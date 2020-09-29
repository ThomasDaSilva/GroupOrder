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
use GroupOrder\Model\GroupOrderMainCustomerQuery;
use GroupOrder\Model\GroupOrderSubCustomer;
use GroupOrder\Model\GroupOrderSubCustomerQuery;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Model\AddressQuery;
use Thelia\Model\CustomerQuery;
use Thelia\TaxEngine\Calculator;
use Thelia\Tools\MoneyFormat;
use Thelia\Tools\URL;

class CustomerController extends BaseFrontController
{
    public function createOrUpdateSubCustomer()
    {
        $subCustomerId = $this->getRequest()->get('id');
        $customerId = $this->getRequest()->getSession()->getCustomerUser()->getId();
        $mainCustomer = GroupOrderMainCustomerQuery::create()->filterByCustomerId($customerId)->findOne();
        try {
            $form = $this->validateForm(new SubCustomerForm($this->getRequest()));
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
     * @return \Thelia\Core\HttpFoundation\Response
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getSubCustomerCart()
    {
        $subCustomerId = $this->getRequest()->get('sub_customer_id');

        $this->getRequest()->getSession()->set('GroupOrderSelectedSubCustomer', null);

        $subCustomerCartItems = GroupOrderCartItemQuery::create()->filterBySubCustomerId($subCustomerId)->find();
        $calc = new Calculator();
        $cartItems = [];

        $lang = $this->getRequest()->getSession()->getLang();
        /** @var GroupOrderCartItem $subCustomerCartItem */
        foreach ($subCustomerCartItems as $subCustomerCartItem) {
            $product = $subCustomerCartItem->getCartItem()->getProduct();
            $customer = CustomerQuery::create()->filterById($this->getRequest()->getSession()->getCustomerUser()->getId())->findOne();
            $address = AddressQuery::create()->filterByCustomerId($customer->getId())->findOne();
            $calc->load($product, $address->getCountry());
            $cartItem = $subCustomerCartItem->getCartItem();
            $product = $cartItem->getProductSaleElements()->getProduct();
            $cartItems[] = [
                'quantity' => $cartItem->getQuantity(),
                'price' => MoneyFormat::getInstance($this->getRequest())->formatByCurrency($calc->getTaxedPrice($cartItem->getPrice())),
                'title' => $product->setLocale($lang->getLocale())->getTitle(),
            ];
        }

        $response = [
            "cartItems" => $cartItems
        ];

        $this->getRequest()->getSession()->set('GroupOrderSelectedSubCustomer', $subCustomerId);

        return $this->jsonResponse(json_encode($response));
    }

    public function goHome()
    {
        $this->getRequest()->getSession()->set('GroupOrderSelectedSubCustomer', null);
    }
}