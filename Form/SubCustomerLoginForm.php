<?php

namespace GroupOrder\Form;

use GroupOrder\GroupOrder;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;

class SubCustomerLoginForm extends BaseForm
{
    protected function buildForm()
    {
        $this->getFormBuilder()
            ->add("login", TextType::class, [
                "required" => true,
                "label" => Translator::getInstance()->trans("Login", [], GroupOrder::DOMAIN_NAME),
                "constraints" => [
                    new NotBlank()
                ],
                "label_attr" => [
                    "for" => "login",
                ],
            ])
            ->add(
                'group_code',
                TextType::class,
                [
                    "required" => true,
                    'label' => $this->translator->trans('Votre code groupe', [], GroupOrder::DOMAIN_NAME),
                    'label_attr' => [
                        'for' => $this->getName() . '-label'
                    ],
                    'constraints' => [
                        new NotBlank
                    ]

                ])
            ->add("password", PasswordType::class, [
                "required" => true,
                "label" => Translator::getInstance()->trans("Password", [], GroupOrder::DOMAIN_NAME),
                "constraints" => [
                    new NotBlank()
                ],
                "label_attr" => [
                    "for" => "password",
                ],
            ]);
    }

    public static function getName()
    {
        return 'group_order_sub_customer_login';
    }
}