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
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Tools\URL;

class LoginController extends BaseFrontController
{

    public function login()
    {
        try {
            $form = $this->validateForm(new SubCustomerLoginForm($this->getRequest()));

            $login = $form->get("login")->getData();
            $password = $form->get("password")->getData();

            /** @var GroupOrderSubCustomer $subCustomer */
            if ($subCustomer = GroupOrderSubCustomerQuery::create()->filterByLogin($login)->findOne()) {
                if (password_verify($password, $subCustomer->getPassword())) {

                    $mainCustomer = $subCustomer->getGroupOrderMainCustomer();

                    $this->getRequest()->getSession()->set("GroupOrderLoginSubCustomer", $subCustomer->getId());
                    $this->getRequest()->getSession()->set("GroupOrderMainCustomer", $mainCustomer->getId());

                    return $this->generateRedirect(URL::getInstance()->absoluteUrl(""));
                }
            }
        } catch (\Exception $e) {
            return $this->generateRedirect(URL::getInstance()->absoluteUrl("/login"));
        }
        return $this->generateRedirect(URL::getInstance()->absoluteUrl("/login"));
    }

    public function logout()
    {
        $this->getRequest()->getSession()->set("GroupOrderLoginSubCustomer", null);
        $this->getRequest()->getSession()->set("GroupOrderMainCustomer", null);

        return $this->generateRedirect(URL::getInstance()->absoluteUrl(""));
    }
}