<?php

namespace GroupOrder\Controller;

use Thelia\Controller\Front\BaseFrontController;

class FrontController extends BaseFrontController
{
    public function viewLogin()
    {
        $createSubCustomer = $this->getRequest()->get('create');

        return $this->render("front-sub-customer-login");
    }

    public function createSubCustomer()
    {
        return $this->render("front-sub-customer-create");
    }
}