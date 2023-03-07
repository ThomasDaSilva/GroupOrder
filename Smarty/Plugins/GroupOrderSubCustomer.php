<?php

namespace GroupOrder\Smarty\Plugins;

use GroupOrder\Model\GroupOrderCartItem;
use GroupOrder\Model\GroupOrderCartItemQuery;
use GroupOrder\Model\GroupOrderMainCustomerQuery;
use GroupOrder\Model\GroupOrderProductQuery;
use GroupOrder\Model\GroupOrderQuery;
use GroupOrder\Model\GroupOrderSubCustomerQuery;
use Propel\Runtime\Propel;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Model\CustomerQuery;
use Thelia\Model\Map\CartItemTableMap;
use TheliaSmarty\Template\AbstractSmartyPlugin;
use TheliaSmarty\Template\SmartyPluginDescriptor;

class GroupOrderSubCustomer extends AbstractSmartyPlugin
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    public function __construct(Request $request, EventDispatcher $dispatcher)
    {
        $this->request = $request;
        $this->dispatcher = $dispatcher;
    }

    public function getPluginDescriptors()
    {
        return [
            new SmartyPluginDescriptor('function', 'isSubCustomer', $this, 'isSubCustomer'),
            new SmartyPluginDescriptor('function', 'unSelectCustomer', $this, 'unSelectCustomer'),
            new SmartyPluginDescriptor('function', 'isMainCustomer', $this, 'isMainCustomer'),
            new SmartyPluginDescriptor('function', 'isGroupOrder', $this, 'isGroupOrder'),
            new SmartyPluginDescriptor('function', 'isSubCustomerOrder', $this, 'isSubCustomerOrder'),
            new SmartyPluginDescriptor('function', 'getCurrentSubCustomerCartItems', $this, 'getCurrentSubCustomerCartItems'),
            new SmartyPluginDescriptor('function', 'subCustomerSelected', $this, 'subCustomerSelected'),
            new SmartyPluginDescriptor('function', 'groupOrderSubCustomerName', $this, 'groupOrderSubCustomerName'),
            new SmartyPluginDescriptor('function', 'getTotalOrderBySubCustomer', $this, 'getTotalOrderBySubCustomer'),
        ];
    }

    public function getTotalOrderBySubCustomer($params, $smarty)
    {
        if (!isset($params['order_id']) || !isset($params['sub_customer_id'])) {
            return null;
        }

        $subCustomerId = $params['sub_customer_id'];
        $orderId = $params['order_id'];

        $con = Propel::getConnection();

        $sql = "
            SELECT 
           `order_product`.`was_in_promo` AS was_in_promo,
            ROUND(SUM(ROUND((price+`order_product_tax`.`amount`),2) *`order_product`.`quantity`),2) as total,
            ROUND(SUM(ROUND((`order_product`.`promo_price`+`order_product_tax`.`promo_amount`),2) *`order_product`.`quantity`),2) as totalpromo
            FROM `group_order`             
            INNER JOIN `group_order_sub_order` ON (`group_order`.`id`=`group_order_sub_order`.`group_order_id`) 
            INNER JOIN `group_order_product` ON (`group_order_sub_order`.`id`=`group_order_product`.`sub_order_id`) 
            INNER JOIN `order_product` ON (`group_order_product`.`order_product_id`=`order_product`.`id`) 
            LEFT JOIN `order_product_tax` ON (`order_product`.`id`=`order_product_tax`.`order_product_id`)             
            WHERE `group_order`.`order_id`=:p0 AND `group_order_sub_order`.`sub_customer_id`=:p1 
        ";

        $stmt = $con->prepare($sql);
        $stmt->bindValue(':p0', $orderId, \PDO::PARAM_INT);
        $stmt->bindValue(':p1', $subCustomerId, \PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($data['was_in_promo'] === '1') {
            $smarty->assign('sub_total_taxed_order', $data['totalpromo']);
            return;
        }

        $smarty->assign('sub_total_taxed_order', $data['total']);
    }

    public function unSelectCustomer($params)
    {
        $this->request->getSession()->set("GroupOrderSelectedSubCustomer", null);
    }

    public function isSubCustomerOrder($params, $smarty)
    {
        if (!isset($params['order_id']) || empty($params['order_id'])) {
            return 0;
        }

        if (!isset($params['subCustomerId']) || empty($params['subCustomerId'])) {
            return 0;
        }

        $order = GroupOrderQuery::create()
            ->filterByOrderId($params['order_id'])
            ->useGroupOrderSubOrderQuery()
            ->filterBySubCustomerId($params['subCustomerId'])
            ->endUse()
            ->count();

        if ($order > 0) {
            return 1;
        }

        return 0;
    }

    public function subCustomerSelected($params, $smarty)
    {
        $subCustomerId = $this->request->getSession()->get("GroupOrderSelectedSubCustomer", null);
        $showCurrentCartData = $this->request->getSession()->get("SubCustomerSessionCart", false);

        if (isset($params['subcustomerId']) && !empty($params['subcustomerId'])) {
            $subCustomerId = $params['subcustomerId'];
        }

        $cart = $this->request->getSession()->getSessionCart($this->dispatcher);

        if (null == $subCustomerId) {
            $subCustomerId = $this->request->getSession()->get("GroupOrderLoginSubCustomer", null);
        }

        if (!$subCustomerId) {
            $smarty->assign('wasSubCustomerSelected', 0);
            return;
        }

        if ($showCurrentCartData) {
            $smarty->assign('wasSubCustomerSelected', 0);
            $smarty->assign('islogedSubcustomer', 1);
            return;
        }

        $subCustomer = GroupOrderSubCustomerQuery::create()->findPk($subCustomerId);

        if (!$subCustomer) {
            $smarty->assign('wasSubCustomerSelected', 0);
            return;
        }

        $smarty->assign('subCustomerName', $subCustomer->getFirstName() . ' ' . $subCustomer->getLastName());

        $smarty->assign('wasSubCustomerSelected', 1);

        $cartItems = GroupOrderCartItemQuery::create()
            ->useGroupOrderCartQuery()
            ->filterByCartId($cart->getId())
            ->endUse()
            ->useCartItemQuery()
            ->withColumn(CartItemTableMap::COL_QUANTITY, 'cart_item_quantity')
            ->endUse()
            ->filterBySubCustomerId($subCustomerId)
            ->find();

        $cartItemsCount = 0;

        /** @var GroupOrderCartItem $cartItem */
        foreach ($cartItems as $cartItem) {
            $quantity = $cartItem->getVirtualColumn('cart_item_quantity');
            $cartItemsCount += $quantity;
        }

        $smarty->assign('subCustomerCartItemsCount', $cartItemsCount);

        $cartItemsID = [];
        $totalBySubcustomer = 0;

        $mainCustomer = CustomerQuery::create()
            ->useGroupOrderMainCustomerQuery()
            ->filterById($subCustomer->getMainCustomerId())
            ->endUse()
            ->findOne();

        if (null == $mainCustomer) {
            $smarty->assign('wasSubCustomerSelected', 0);
            return;
        }

        $country = $mainCustomer->getDefaultAddress()->getCountry();

        foreach ($cartItems as $cartItem) {
            $totalBySubcustomer += ($cartItem->getCartItem()->getPromo()) ? $cartItem->getCartItem()->getTotalTaxedPromoPrice($country) : $cartItem->getCartItem()->getTotalTaxedPrice($country);
            $cartItemsID[] = $cartItem->getCartItem()->getId();
        }

        $smarty->assign('totalCartBySubcustomer', $totalBySubcustomer);
        $smarty->assign('subCustomerCartItemsId', $cartItemsID);
    }

    public function isGroupOrder($params)
    {
        $orderId = $params['order_id'];
        $isGroupOrder = 0;

        if (null === $orderId) {
            return;
        }

        $order = GroupOrderQuery::create()
            ->filterByOrderId($orderId)
            ->findOne();

        if ($order) {
            $isGroupOrder = 1;
        }

        return $isGroupOrder;
    }

    public function isSubCustomer($params)
    {
        $subCustomerId = $this->request->getSession()->get("GroupOrderLoginSubCustomer", null);
        $subcustomer = GroupOrderSubCustomerQuery::create()->findPk($subCustomerId);

        if ($subcustomer) {
            return 1;
        }

        return 0;
    }

    public function isMainCustomer($params)
    {
        if (isset($params['customer_id']) && !empty($params['customer_id'])) {
            $mainCustomer = GroupOrderMainCustomerQuery::create()
                ->useCustomerQuery()
                ->filterById($params['customer_id'])
                ->endUse()
                ->findOne();

            if ($mainCustomer) {
                return 1;
            }
        }

        $mainCustomer = $this->request->getSession()->get("CurrentUserIsMainCustomer", null);

        if ($mainCustomer) {
            return 1;
        }

        return 0;
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

        if ($cartItemId && $cartItem = GroupOrderCartItemQuery::create()->filterByCartItemId($cartItemId)->findOne()) {
            $subCustomer = $cartItem->getGroupOrderSubCustomer();
        }

        if (!$subCustomer && $orderProductId && $orderProduct = GroupOrderProductQuery::create()->filterByOrderProductId($orderProductId)->findOne()) {
            $subCustomer = $orderProduct->getGroupOrderSubOrder()->getGroupOrderSubCustomer();
        }

        if ($subCustomer) {
            $smarty->assign('subCustomerName', $subCustomer->getFirstName() . " " . $subCustomer->getLastName());
        }
    }
}