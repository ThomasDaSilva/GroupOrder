<?php

namespace GroupOrder\Form;

use GroupOrder\GroupOrder;
use GroupOrder\Model\GroupOrderMainCustomerQuery;
use GroupOrder\Model\GroupOrderSubCustomerQuery;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Thelia\Model\Country;
use Thelia\Model\CountryQuery;

class OrderOnCartForm extends BaseForm
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
            ->add("title", TextType::class, [
                "required" => true,
                "constraints" => [
                    new NotBlank()
                ],
                "label" => Translator::getInstance()->trans("Titre", [], GroupOrder::DOMAIN_NAME),
                "label_attr" => [
                    "for" => "firstname",
                ]
            ])
            ->add("date_end", DateType::class, [
                'widget' => 'single_text',
                "required" => false,
                "label" => Translator::getInstance()->trans("Date de fin de préparation de commande", [], GroupOrder::DOMAIN_NAME),
                "label_attr" => [
                    "for" => "date_end",
                ],
            ])
            ->add("sub_customer_id", ChoiceType::class, [
                'choices' => $this->getSubCustomer(),
                'multiple' => true,
                'expanded' => true,
                "label" => Translator::getInstance()->trans('Selection d\'utilisateurs pour cette commande'),
            ]);
    }

    protected function getSubCustomer()
    {
        $tab = [];
        $customer = $this->getRequest()->getSession()->getCustomerUser();

        if (null === $customer) {
            return $tab;
        }

        $mainCustomer = GroupOrderMainCustomerQuery::create()
            ->filterByCustomerId($customer->getId())
            ->findOne();

        if (null === $mainCustomer) {
            return $tab;
        }

        $subCustomers = GroupOrderSubCustomerQuery::create()
            ->filterByMainCustomerId($mainCustomer->getId())
            ->find();

        foreach ($subCustomers as $subCustomer) {
            $tab [$subCustomer->getId()] = $subCustomer->getLastName() . ' ' . $subCustomer->getFirstName();
        }

        return $tab;
    }

    public static function getName()
    {
        return 'order_on_cart_form';
    }
}