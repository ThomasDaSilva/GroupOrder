<?php

namespace GroupOrder\Controller;

use GroupOrder\GroupOrder;
use GroupOrder\Model\GroupOrderMainCustomerQuery;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\Mailer;
use Thelia\Controller\Admin\BaseAdminController;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Mailer\MailerFactory;

/**
 * @Route("/admin/GroupOrder", name="group_order_back_")
 */
class BackController extends BaseAdminController
{
    /**
     * @Route("/active-switch", name="active_switch")
     */
    public function handleStatusMainCustomer(RequestStack $requestStack, MailerFactory $mailer)
    {
        if ($customerId = $requestStack->getCurrentRequest()->get('customer_id')) {
            $mainCustomer = GroupOrderMainCustomerQuery::create()
                ->filterByCustomerId($customerId)
                ->findOne();

            if (null === $mainCustomer) {
                return $this->generateRedirect("/admin");
            }

            if ($mainCustomer->getActive()) {
                $mainCustomer->setActive(0)->save();
            } else {
                $mainCustomer->setActive(1)->save();
            }

            if($mainCustomer->getActive()){
                $mailer->sendEmailToCustomer(GroupOrder::EMAIL_MESSAGE_NAME, $mainCustomer->getCustomer(), [
                    "code" => $mainCustomer->getCode(),
                    "customer_id" => $mainCustomer->getCustomer()->getId()
                ]);
            }

            return $this->generateRedirect("/admin/customer/update?customer_id=" . $customerId);
        }

        return $this->generateRedirect("/admin");
    }
}
