<?php
/**
 * Created by PhpStorm.
 * User: nicolasbarbey
 * Date: 25/09/2020
 * Time: 15:18
 */

namespace GroupOrder\Controller;


use GroupOrder\Form\SubCustomerLoginForm;
use GroupOrder\Model\GroupOrderSubCustomer;
use GroupOrder\Model\GroupOrderSubCustomerQuery;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Controller\Front\BaseFrontController;
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
    public function login(RequestStack $requestStack)
    {
        try {
            $form = $this->validateForm($this->createForm(SubCustomerLoginForm::getName()));

            $login = $form->get("login")->getData();
            $password = $form->get("password")->getData();

            /** @var GroupOrderSubCustomer $subCustomer */
            if ($subCustomer = GroupOrderSubCustomerQuery::create()->filterByLogin($login)->findOne()) {
                if (password_verify($password, $subCustomer->getPassword())) {

                    $mainCustomer = $subCustomer->getGroupOrderMainCustomer();
                    $requestStack->getCurrentRequest()
                        ->getSession()->set("GroupOrderLoginSubCustomer", $subCustomer->getId());
                    $requestStack->getCurrentRequest()
                        ->getSession()->set("GroupOrderMainCustomer", $mainCustomer->getId());

                    return $this->generateRedirect(URL::getInstance()->absoluteUrl(""));
                }
            }
        } catch (\Exception $e) {
            return $this->generateRedirect(URL::getInstance()->absoluteUrl("/login"));
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

        return $this->generateRedirect(URL::getInstance()->absoluteUrl(""));
    }
}