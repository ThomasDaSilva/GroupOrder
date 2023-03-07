<?php

namespace GroupOrder\Form;

use GroupOrder\GroupOrder;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

class SubCustomerSelfRegisterForm extends SubCustomerForm
{
    protected function buildForm()
    {
        parent::buildForm();

        $this->formBuilder
            ->add(
                'group_code',
                TextType::class,
                [
                    "required" => true,
                    'label' => $this->translator->trans('Code de groupe, permettant de vous lier à un groupe', [], GroupOrder::DOMAIN_NAME),
                    'label_attr' => [
                        'for' => $this->getName() . '-label'
                    ],
                    'constraints' => [
                        new Assert\NotBlank
                    ]

                ]);
    }

    public static function getName()
    {
        return 'group_order_sub_customer_sel_register';
    }
}