<?php

namespace GroupOrder\Form;

use GroupOrder\GroupOrder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Thelia\Model\Country;
use Thelia\Model\CountryQuery;

class SubCustomerForm extends BaseForm
{
    protected function buildForm()
    {
        $countries = CountryQuery::create()->filterByVisible(1)->find();
        $choice = [];

        /** @var Country $country */
        foreach ($countries as $country) {
            $choice[$country->getId()] = $country->getTitle();
        }

        $this->getFormBuilder()
            ->add("type", TextType::class, [
                "required" => false,
            ])
            ->add("firstname", TextType::class, [
                "required" => true,
                "constraints" => [
                    new NotBlank()
                ],
                "label" => Translator::getInstance()->trans("First Name", [], GroupOrder::DOMAIN_NAME),
                "label_attr" => [
                    "for" => "firstname",
                ],
            ])
            ->add("lastname", TextType::class, [
                "required" => true,
                "constraints" => [
                    new NotBlank()
                ],
                "label" => Translator::getInstance()->trans("Last Name", [], GroupOrder::DOMAIN_NAME),
                "label_attr" =>  [
                    "for" => "lastname",
                ],
            ])
            ->add("login", TextType::class, [
                "required" => false,
                "label" => Translator::getInstance()->trans("Login", [], GroupOrder::DOMAIN_NAME),
                "label_attr" => [
                    "for" => "login",
                ],
            ])
            ->add("password", PasswordType::class, [
                "required" => false,
                "label" => Translator::getInstance()->trans("Mot de passe de votre choix (non obligatoire)", [], GroupOrder::DOMAIN_NAME),
                "label_attr" => [
                    "for" => "password",
                ],
            ])
            ->add("email", EmailType::class, [
                "required" => false,
                "constraints" => [
                    new Email(),
                ],
                "label" => Translator::getInstance()->trans("Email (nécessaire si vous voulez recevoir les notifications)", [], GroupOrder::DOMAIN_NAME),
                "label_attr" => [
                    "for" => "email",
                ],
            ])
            ->add("address1", TextType::class, [
                "required" => false,
                "label" => Translator::getInstance()->trans("Street Address", [], GroupOrder::DOMAIN_NAME),
                "label_attr" => [
                    "for" => "address1",
                ],
            ])
            ->add("address2", TextType::class, [
                "label" => Translator::getInstance()->trans("Address Line 2", [], GroupOrder::DOMAIN_NAME),
                "label_attr" => [
                    "for" => "address2",
                ],
                "required" => false,
            ])
            ->add("address3", TextType::class, [
                "label" => Translator::getInstance()->trans("Address Line 3", [], GroupOrder::DOMAIN_NAME),
                "label_attr" => [
                    "for" => "address3",
                ],
                "required" => false,
            ])
            ->add("city", TextType::class, [
                "required" => false,
                "label" => Translator::getInstance()->trans("City", [], GroupOrder::DOMAIN_NAME),
                "label_attr" => [
                    "for" => "city",
                ],
            ])
            ->add("zipcode", TextType::class, [
                "required" => false,
                "label" => Translator::getInstance()->trans("Zip code", [], GroupOrder::DOMAIN_NAME),
                "label_attr" => [
                    "for" => "zipcode",
                ],
            ])
            ->add("country_id", ChoiceType::class, [
                "required" => false,
                "label" => Translator::getInstance()->trans("Country", [], GroupOrder::DOMAIN_NAME),
                "label_attr" => [
                    "for" => "country",
                ],
                "choices" => $choice
            ]);
    }

    public static function getName()
    {
        return 'group_order_sub_customer_form';
    }
}