<?php

namespace GroupOrder\Controller;

use GroupOrder\Form\SubCustomerLoginForm;
use GroupOrder\Model\GroupOrderMainCustomerQuery;
use GroupOrder\Model\GroupOrderSubCustomer;
use GroupOrder\Model\GroupOrderSubCustomerQuery;
use GroupOrder\Service\GroupOrderLoginService;
use GroupOrder\Service\GroupOrderService;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\Template\ParserContext;
use Thelia\Core\Translation\Translator;
use Thelia\Mailer\MailerFactory;
use Thelia\Model\CartItem;
use Thelia\Model\ConfigQuery;
use Thelia\Tools\URL;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("", name="group_order_log_")
 */
class LoginController extends BaseFrontController
{
    /**
     * @Route("/login/sub-customer", name="login")
     */
    public function login(
        RequestStack $requestStack,
        GroupOrderService $service,
        EventDispatcher $dispatcher,
        ParserContext $parcerContext,
        Translator $translator)
    {
        try {
            $formModel = $this->createForm(SubCustomerLoginForm::getName());
            $form = $this->validateForm($formModel);

            $code = $form->get("group_code")->getData();
            $login = $form->get("login")->getData();
            $password = $form->get("password")->getData();

            $subCustomer = GroupOrderSubCustomerQuery::create()
                ->useGroupOrderMainCustomerQuery()
                ->filterByCode($code)
                ->endUse()
                ->filterByLogin($login)
                ->findOne();

            if (!$subCustomer) {
                if (!GroupOrderMainCustomerQuery::create()->filterByCode($code)->count()) {
                    throw new \Exception($translator->trans('Login / mot de passe incohérents'));
                }

                return $this->generateRedirect(URL::getInstance()->absoluteUrl("/register/sub-customer", [
                    'login' => $login,
                    'code' => $code,
                ]));
            }

            if (!password_verify($password, $subCustomer->getPassword())) {
                throw new \Exception($translator->trans('Login / mot de passe incohérents'));
            }

            /** @var GroupOrderSubCustomer $subCustomer */
            $mainCustomer = $subCustomer->getGroupOrderMainCustomer();

            $cartItems = $service->getSubCustomerCartItems($subCustomer->getId());

            if (count($cartItems)) {
                $cart = $requestStack->getCurrentRequest()->getSession()->getSessionCart($dispatcher);
                $cart->setCartItems(new Collection());
                $cart->save();
                $requestStack->getCurrentRequest()->getSession()->setSessionCart($cart);

                $cartItemIdsUpdated = [];

                /** @var CartItem $cartItem */
                foreach ($cartItems as $cartItem) {
                    if (null === $cartItem) {
                        continue;
                    }

                    try {
                        /** @var GroupOrderLoginService $loginService */
                        $loginService = $this->getContainer()->get('group.order.login.service');
                        $updated = $loginService->updateCartItem($cartItem, $mainCustomer->getCustomer());

                    } catch (\Exception $e) {
                        continue;
                    }

                    $newCartItem = new CartItem();
                    $newCartItem->setCartId($cart->getId())
                        ->setPrice($cartItem->getPrice())
                        ->setProductId($cartItem->getProductId())
                        ->setPromoPrice($cartItem->getPromoPrice())
                        ->setQuantity($cartItem->getQuantity())
                        ->setProductSaleElementsId($cartItem->getProductSaleElementsId())
                        ->setPriceEndOfLife($cartItem->getPriceEndOfLife())
                        ->setPromo($cartItem->getPromo())
                        ->save();

                    if ($updated) {
                        $cartItemIdsUpdated[] = $newCartItem->getId();
                    }
                }
            }

            $requestStack->getCurrentRequest()->getSession()->set("cartItemIdsUpdated", null);

            if (count($cartItemIdsUpdated) > 0) {
                $requestStack->getCurrentRequest()->getSession()->set("cartItemIdsUpdated", $cartItemIdsUpdated);
            }

            $requestStack->getCurrentRequest()->getSession()->set("SubCustomerSessionCart", true);
            $requestStack->getCurrentRequest()->getSession()->set("GroupOrderLoginSubCustomer", $subCustomer->getId());
            $requestStack->getCurrentRequest()->getSession()->set("GroupOrderMainCustomer", $mainCustomer->getId());

            return $this->generateRedirect(URL::getInstance()->absoluteUrl(""));

        } catch (PropelException $e) {
            $error = $e->getMessage();

        } catch (\Exception $e) {
            return $this->generateRedirect(URL::getInstance()->absoluteUrl("/login"));
        }

        $formModel->setErrorMessage($error);

        $parcerContext
            ->setGeneralError($error)
            ->addForm($formModel);

        // If an error URL is defined in the form, redirect to it, otherwise use the defaut view
        if ($formModel->hasErrorUrl()) {
            return $this->generateErrorRedirect($formModel);
        }

        return $this->generateRedirect(URL::getInstance()->absoluteUrl("/login"));
    }

    /**
     * @Route("/logout/sub-customer", name="logout")
     */
    public function logout(RequestStack $requestStack)
    {
        $requestStack->getCurrentRequest()->getSession()->set("GroupOrderLoginSubCustomer", null);
        $requestStack->getCurrentRequest()->getSession()->set("GroupOrderMainCustomer", null);
        $requestStack->getCurrentRequest()->getSession()->set("SubCustomerSessionCart", null);

        return $this->generateRedirect(URL::getInstance()->absoluteUrl(""));
    }

    public function showForgetPassword()
    {
        return $this->render('password-sub-customer');
    }

    public function confirmPasswordSent()
    {
        return $this->render('password-sub-customer', ['password_sent' => 1]);
    }

    public function forgetPassword(ParserContext $parserContext, MailerFactory $mailer)
    {
        $passwordFrom = $this->createForm('group_order_sub_customer_password');
        try {
            $form = $this->validateForm($passwordFrom);
            $data = $form->getData();

            $mainCustomer = GroupOrderMainCustomerQuery::create()
                ->filterByCode($data['group_code'])
                ->findOne();

            if ($mainCustomer === null){
                throw new \Exception(Translator::getInstance()->trans("Le code groupe est invalide"));
            }

            $subCustomer = GroupOrderSubCustomerQuery::create()
                ->filterByEmail($data['login'])
                ->filterByMainCustomerId($mainCustomer->getId())
                ->findOne();

            if (null === $subCustomer){
                throw new \Exception(Translator::getInstance()->trans("L'adresse email n'a pas été trouvé"));
            }

            $password = $this->randomPassword();

            $subCustomer
                ->setPassword(password_hash($password, PASSWORD_BCRYPT))
                ->save();

            $mailer->sendEmailMessage(
                'lost_password',
                [ ConfigQuery::getStoreEmail() => ConfigQuery::getStoreName() ],
                [ $subCustomer->getEmail() => $subCustomer->getFirstname()." ".$subCustomer->getLastname() ],
                ['password' => $password]
            );

            return $this->generateSuccessRedirect($passwordFrom);

        } catch (\Exception $exception){

            $passwordFrom->setErrorMessage($exception->getMessage());

            $parserContext
                ->addForm($passwordFrom)
                ->setGeneralError($exception->getMessage());
        }

        return $this->generateErrorRedirect($passwordFrom);
    }

    public function randomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }
}