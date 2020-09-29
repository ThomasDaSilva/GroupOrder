<?php
/**
 * Created by PhpStorm.
 * User: nicolasbarbey
 * Date: 17/09/2020
 * Time: 16:45
 */

namespace GroupOrder\EventListeners;


use GroupOrder\GroupOrder;
use GroupOrder\Model\GroupOrderMainCustomer;
use GroupOrder\Model\GroupOrderMainCustomerQuery;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\Callback;
use Thelia\Core\Event\Customer\CustomerCreateOrUpdateEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Event\TheliaFormEvent;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Translation\Translator;

class CustomerListener implements EventSubscriberInterface
{

    /** @var Request $request */
    protected $request;

    const MAIN_CUSTOMER_CHECKBOX = "main_customer_checkbox";

    public function __construct(Request $request)
    {
        $this->request = $request;
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
            if ($isMainCustomer) {
                if (!$mainCustomer) {
                    $mainCustomer = new GroupOrderMainCustomer();
                    $mainCustomer
                        ->setCustomerId($event->getCustomer()->getId());
                }
                $mainCustomer
                    ->setActive(1)
                    ->save();
            }
            if ($mainCustomer && !$isMainCustomer) {
                $mainCustomer
                    ->setActive(0)
                    ->save();
            }
        }
        $this->getRequest()->getSession()->set(self::MAIN_CUSTOMER_CHECKBOX, null);
    }


    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_customer_create" => ['addMainCustomerCheckBox', 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".thelia_customer_update" => ['addMainCustomerCheckBox', 128],
            TheliaEvents::CUSTOMER_CREATEACCOUNT => ['processMainCustomerCheckBox', 10],
            TheliaEvents::CUSTOMER_UPDATEACCOUNT => ['processMainCustomerCheckBox', 10],
        ];
    }
}