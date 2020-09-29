<?php
/**
 * Created by PhpStorm.
 * User: nicolasbarbey
 * Date: 28/09/2020
 * Time: 11:48
 */

namespace GroupOrder\Smarty\Plugins;


use GroupOrder\Model\GroupOrderCartItemQuery;
use GroupOrder\Model\GroupOrderProductQuery;
use TheliaSmarty\Template\AbstractSmartyPlugin;
use TheliaSmarty\Template\SmartyPluginDescriptor;

class GroupOrderSubCustomer extends AbstractSmartyPlugin
{
    public function getPluginDescriptors()
    {
        return [
            new SmartyPluginDescriptor('function', 'groupOrderSubCustomerName', $this, 'groupOrderSubCustomerName'),
        ];
    }

    /**
     * @param $params
     * @param $smarty
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function groupOrderSubCustomerName($params, $smarty)
    {
        $cartItemId = $params["item_id"];

        $orderProductId = $params["order_product_id"];

        $subCustomer = null;

        if ($cartItemId) {
            $subCustomer = GroupOrderCartItemQuery::create()->filterByCartItemId($cartItemId)->findOne()->getGroupOrderSubCustomer();
        }

        if ($orderProductId) {
            $subCustomer = GroupOrderProductQuery::create()->filterByOrderProductId($orderProductId)->findOne()->getGroupOrderSubOrder()->getGroupOrderSubCustomer();
        }

        $smarty->assign('subCustomerName', $subCustomer->getFirstName() . " " . $subCustomer->getLastName());
    }
}