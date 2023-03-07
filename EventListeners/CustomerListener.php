<?php

namespace GroupOrder\EventListeners;

use GroupOrder\GroupOrder;
use GroupOrder\Model\GroupOrderMainCustomer;
use GroupOrder\Model\GroupOrderMainCustomerQuery;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Callback;
use Thelia\Core\Event\Customer\CustomerCreateOrUpdateEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Event\TheliaFormEvent;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Translation\Translator;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Mailer\MailerFactory;
use Thelia\Model\ConfigQuery;
use Thelia\Tools\URL;

class CustomerListener implements EventSubscriberInterface
{
    /** @var Request $request */
    protected $request;

    /** @var MailerFactory */
    protected $mailer;

    const MAIN_CUSTOMER_CHECKBOX = "main_customer_checkbox";
    const GROUP_ORDER_TYPE = "group_order_type";

    const STRING_CODE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

    public function __construct(Request $request, MailerFactory $mailer)
    {
        $this->request = $request;
        $this->mailer = $mailer;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function addMainCustomerCheckBox(TheliaFormEvent $event)
    {
        $event->getForm()->getFormBuilder()->add(
            self::MAIN_CUSTOMER_CHECKBOX,
            CheckboxType::class,
            [
                'constraints' => [
                    new Callback(['methods' => [[$this, 'setIsMainCustomer']]])
                ],
                'required' => false,
                'label' => Translator::getInstance()->trans(
                    'Main Customer', [],
                    GroupOrder::DOMAIN_NAME
                ),
                'label_attr' => [
                    'for' => self::MAIN_CUSTOMER_CHECKBOX
                ]
            ]
        )
            ->add(
                self::GROUP_ORDER_TYPE,
                ChoiceType::class,
                [
                    'required' => false,
                    'label' => Translator::getInstance()->trans(
                        'Type de groupe :', [],
                        GroupOrder::DOMAIN_NAME
                    ),
                    'label_attr' => [
                        'for' => self::GROUP_ORDER_TYPE
                    ],
                    'choices' => explode(',', ConfigQuery::read('type_group_order', []))
                ]
            );

    }

    public function setIsMainCustomer($value)
    {
        $this->getRequest()->getSession()->set(self::MAIN_CUSTOMER_CHECKBOX, null);
        if ($value) {
            $this->getRequest()->getSession()->set(self::MAIN_CUSTOMER_CHECKBOX, $value);
        }
    }

    /**
     * @param CustomerCreateOrUpdateEvent $event
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function processMainCustomerCheckBox(CustomerCreateOrUpdateEvent $event)
    {
        if ($event->hasCustomer()) {
            $isMainCustomer = $this->getRequest()->getSession()->get(self::MAIN_CUSTOMER_CHECKBOX);
            $mainCustomer = GroupOrderMainCustomerQuery::create()->filterByCustomerId($event->getCustomer()->getId())->findOne();

            if ($mainCustomer) {
                return;
            }

            if (isset($this->request->get('thelia_customer_create')['group_order_type'])) {
                $type = $this->request->get('thelia_customer_create')['group_order_type'];
            } elseif (isset($this->request->get('thelia_customer_profile_update')['group_order_type'])) {
                $type = $this->request->get('thelia_customer_profile_update')['group_order_type'];
            } elseif ($mainCustomer) {
                $type = $mainCustomer->getType();
            } else {
                $type = 3;
            }

            if ($isMainCustomer) {
                $mainCustomer = new GroupOrderMainCustomer();
                $mainCustomer
                    ->setCustomerId($event->getCustomer()->getId());

                $code = "";

                for ($i = 0; $i < 8; $i++) {
                    $code .= self::STRING_CODE[rand() % strlen(self::STRING_CODE)];
                }

                $mainCustomer
                    ->setType($type)
                    ->setActive(0)
                    ->setCode($code)
                    ->save();

                $address = $event->getCustomer()->getDefaultAddress();

                if ($address) {
                    $types = explode(',', ConfigQuery::read('type_group_order', []));
                    $address->setCompany($types[$type] . ' / ' . $address->getCompany())->save();
                }

                $url = URL::getInstance()->absoluteUrl("admin/customer/update?customer_id=" . $event->getCustomer()->getId());

                $this->mailer->sendSimpleEmailMessage(
                    [ConfigQuery::getStoreEmail() => ConfigQuery::getStoreName()],
                    [ConfigQuery::getStoreEmail() => ConfigQuery::getStoreName()],
                    "Demande creation compte maitre CSE",
                    '
                        <p>Bonjour</p>
                        <p>Une nouvelle demande de compte maitre pour gestion de commandes groupées : </p>
                        <p>Utilisateur : <a href="' . $url . '">' . $event->getCustomer()->getFirstname() . ' ' . $event->getCustomer()->getLastname() . '</a></p>',
                    '
                        Bonjour, 
                        Une nouvelle demande de compte maitre pour gestion de commandes groupées :
                        Utilisateur : ' . $event->getCustomer()->getFirstname() . ' ' . $event->getCustomer()->getLastname() . '
                        ID : ' . $event->getCustomer()->getId()
                );

                $this->getRequest()->getSession()->set(self::MAIN_CUSTOMER_CHECKBOX, null);
                return;
            }

            if (!$mainCustomer) {
                return;
            }

            $mainCustomer
                ->setType($type)
                ->setActive(0)
                ->save();
        }
    }

    public function checkGroupOrderInfo(CustomerCreateOrUpdateEvent $event)
    {
        $isMainCustomer = $this->getRequest()->get("thelia_customer_create");
        if (isset($isMainCustomer['main_customer_checkbox']) && $isMainCustomer['main_customer_checkbox'] == "on" && $event->getCompany() === null) {
            throw new FormValidationException(Translator::getInstance()->trans("Une raison sociale est obligatoire pour un compte commande groupée"));
        }
    }

    public function logOutClean($event)
    {
        $this->getRequest()->getSession()->set("GroupOrderLoginSubCustomer", null);
        $this->getRequest()->getSession()->set("GroupOrderMainCustomer", null);
        $this->getRequest()->getSession()->set("GroupOrderMainCustomer", null);
        $this->getRequest()->getSession()->set('GroupOrderSelectedSubCustomer', null);
    }

    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_customer_create" => ['addMainCustomerCheckBox', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_customer_update" => ['addMainCustomerCheckBox', 128],
            TheliaEvents::CUSTOMER_CREATEACCOUNT => [['processMainCustomerCheckBox', 105], ['checkGroupOrderInfo', 888]],
            TheliaEvents::CUSTOMER_UPDATEACCOUNT => ['processMainCustomerCheckBox', 90],
            TheliaEvents::CUSTOMER_LOGOUT => ['logOutClean', 120],
        ];
    }
}