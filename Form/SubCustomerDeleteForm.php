<?php

namespace GroupOrder\Form;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Thelia\Form\BaseForm;

class SubCustomerDeleteForm extends BaseForm
{
    protected function buildForm()
    {
        $this->formBuilder
            ->add('sub_customer_id', HiddenType::class);
    }

    public static function getName()
    {
        return 'group_order_sub_customer_delete';
    }

}