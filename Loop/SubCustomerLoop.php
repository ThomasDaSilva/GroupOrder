<?php
/**
 * Created by PhpStorm.
 * User: nicolasbarbey
 * Date: 22/09/2020
 * Time: 10:28
 */

namespace GroupOrder\Loop;


use GroupOrder\Model\GroupOrderMainCustomerQuery;
use GroupOrder\Model\GroupOrderSubCustomer;
use GroupOrder\Model\GroupOrderSubCustomerQuery;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

class SubCustomerLoop extends BaseLoop implements PropelSearchLoopInterface
{
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntListTypeArgument('id'),
            Argument::createIntListTypeArgument('main_customer'),
            Argument::createAlphaNumStringTypeArgument('login'),
            Argument::createBooleanTypeArgument('front')
        );
    }

    public function buildModelCriteria()
    {
        $query = GroupOrderSubCustomerQuery::create();

        $mainCustomerIds = $this->getMainCustomer();

        if ($this->getFront()) {
            $theliaCustomer = $this->getCurrentRequest()->getSession()->getCustomerUser();

            if ($theliaCustomer) {
                $mainCustomer = GroupOrderMainCustomerQuery::create()
                    ->filterByCustomerId($theliaCustomer->getId())
                    ->findOne();

                if($mainCustomer){
                    $mainCustomerIds = $mainCustomer->getId();
                }
            }
        }

        if ($ids = $this->getId()) {
            $query->filterById($ids);
        }

        if ($mainCustomerIds = $this->getMainCustomer()) {
            $query->filterByMainCustomerId($mainCustomerIds);
        }

        if ($login = $this->getLogin()) {
            $query->filterByLogin($login);
        }

        return $query->orderByLastName();
    }

    public function parseResults(LoopResult $loopResult)
    {
        $currentSubcustomer = $this->getCurrentRequest()->getSession()->get("GroupOrderSelectedSubCustomer");

        /** @var GroupOrderSubCustomer $subCustomer */
        foreach ($loopResult->getResultDataCollection() as $subCustomer) {
            $loopResultRow = new LoopResultRow($subCustomer);
            $loopResultRow
                ->set("ID", $subCustomer->getId())
                ->set("MAIN_CUSTOMER_ID", $subCustomer->getMainCustomerId())
                ->set("FIRSTNAME", $subCustomer->getFirstName())
                ->set("LASTNAME", $subCustomer->getLastName())
                ->set("EMAIL", $subCustomer->getEmail())
                ->set("ADDRESS1", $subCustomer->getAddress1())
                ->set("ADDRESS2", $subCustomer->getAddress2())
                ->set("ADDRESS3", $subCustomer->getAddress3())
                ->set("CITY", $subCustomer->getCity())
                ->set("ZIPCODE", $subCustomer->getZipCode())
                ->set("COUNTRY_ID", $subCustomer->getCountryId())
                ->set("LOGIN", $subCustomer->getLogin())
                ->set("ISCURRENT_SUBCUSTOMER", ($currentSubcustomer == $subCustomer->getId()) ? 1 : 0);

            $loopResult->addRow($loopResultRow);
        }
        return $loopResult;
    }

}