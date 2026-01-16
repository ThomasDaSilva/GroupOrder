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
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\Template\ParserContext;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Model\AddressQuery;
use Thelia\Model\CustomerQuery;
use Thelia\TaxEngine\Calculator;
use Thelia\Tools\MoneyFormat;
use Thelia\Tools\URL;
use Symfony\Component\Routing\Annotation\Route;


#[Route("", name: "group_order_customer_")]
class CustomerController extends BaseFrontController
{
    #[Route("/GroupOrder/SubCustomer/CreateOrUpdate", name: "create")]
    #[Route("/GroupOrder/SubCustomer/CreateOrUpdate/{id}", name: "update")]
    public function createOrUpdateSubCustomer(RequestStack $requestStack, ParserContext $parserContext): RedirectResponse|Response
    {
        $subCustomerId = $requestStack->getCurrentRequest()->get('id');
        $mainCustomer = $requestStack->getCurrentRequest()->getSession()->get("CurrentUserIsMainCustomer");

        $form = $this->createForm(SubCustomerForm::getName());

        try {
            $data = $this->validateForm($form)->getData();

            $plainPassword = $data['password'];
            $password = password_hash($plainPassword, PASSWORD_BCRYPT);

            if ($subCustomerId) {
                $subCustomer = GroupOrderSubCustomerQuery::create()->filterById($subCustomerId)->findOne();
            }

            if (!$subCustomerId) {
                $subCustomer = new GroupOrderSubCustomer();
            }

            $subCustomer
                ->setMainCustomerId($mainCustomer->getId())
                ->setFirstName($data['firstname'])
                ->setLastName($data['lastname'])
                ->setEmail($data['email'])
                ->setAddress1($data['address1'])
                ->setAddress2($data['address2'])
                ->setAddress3($data['address3'])
                ->setCity($data['city'])
                ->setZipCode($data['zipcode'])
                ->setCountryId($data['country_id'])
                ->setLogin($data['login']);

            if ($plainPassword) {
                $subCustomer->setPassword($password);
            }

            $subCustomer->save();

            return $this->generateSuccessRedirect($form);
        } catch (\Exception $e) {
            $error_message = $e->getMessage();
        }

        $form->setErrorMessage($error_message);
        $parserContext
            ->addForm($form)
            ->setGeneralError($error_message)
        ;

        return $this->generateErrorRedirect($form);
    }

    /**
     * @throws PropelException
     */
    #[Route("/module/groupOrder/getSubCustomerCart", name: "get_sub_customer_cart")]
    public function getSubCustomerCart(RequestStack $requestStack): Response
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


    #[Route("/module/groupOrder/goHome", name: "go_home")]
    public function goHome(RequestStack $requestStack): void
    {
        $requestStack->getCurrentRequest()->getSession()->set('GroupOrderSelectedSubCustomer', null);
    }
}