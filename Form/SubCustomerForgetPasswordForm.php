<?php

namespace GroupOrder\Form;

use Thelia\Form\BaseForm;

class SubCustomerForgetPasswordForm extends SubCustomerLoginForm
{
    protected function buildForm()
    {
        parent::buildForm();
        $this->formBuilder
            ->remove('password');
    }

    public static function getName()
    {
        return 'group_order_sub_customer_password';
    }
}