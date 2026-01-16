<?php
/**
 * Created by PhpStorm.
 * User: nicolasbarbey
 * Date: 25/09/2020
 * Time: 15:18
 */

namespace GroupOrder\Controller;


use GroupOrder\Form\SubCustomerForm;
use GroupOrder\Form\SubCustomerLoginForm;
use GroupOrder\GroupOrder;
use GroupOrder\Model\GroupOrderSubCustomer;
use GroupOrder\Model\GroupOrderSubCustomerQuery;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\Event\Customer\CustomerLoginEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Template\ParserContext;
use Thelia\Core\Translation\Translator;
use Thelia\Tools\URL;
use Symfony\Component\Routing\Annotation\Route;


#[Route("", name: "group_order_log_")]
class LoginController extends BaseFrontController
{
    #[Route("/login/sub-customer", name: "login")]
    public function login(RequestStack $requestStack, ParserContext $parserContext, EventDispatcherInterface $eventDispatcher): RedirectResponse|Response
    {
        $form = $this->createForm(SubCustomerLoginForm::getName());

        try {
            $data = $this->validateForm($form)->getData();

            $login = $data['login'];
            $password = $data['password'];

            /** @var GroupOrderSubCustomer $subCustomer */
            if ($subCustomer = GroupOrderSubCustomerQuery::create()->filterByLogin($login)->findOne()) {
                if (password_verify($password, $subCustomer->getPassword())) {
                    $mainCustomer = $subCustomer->getGroupOrderMainCustomer();

                    $requestStack->getCurrentRequest()->getSession()->set("GroupOrderLoginSubCustomer", $subCustomer->getId());
                    $requestStack->getCurrentRequest()->getSession()->set("GroupOrderMainCustomer", $mainCustomer->getId());

                    return $this->generateRedirect(URL::getInstance()->absoluteUrl(""));
                }
            }

            Throw new \Exception(Translator::getInstance()->trans("Login or password incorrect", [], GroupOrder::DOMAIN_NAME));
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

    #[Route("/logout/sub-customer", name: "logout")]
    public function logout(RequestStack $requestStack): RedirectResponse|Response
    {
        $requestStack->getCurrentRequest()->getSession()->set("GroupOrderLoginSubCustomer", null);
        $requestStack->getCurrentRequest()->getSession()->set("GroupOrderMainCustomer", null);

        return $this->generateRedirect(URL::getInstance()->absoluteUrl(""));
    }
}