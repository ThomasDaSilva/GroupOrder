<?php

namespace GroupOrder\Model;

use GroupOrder\Model\Base\GroupOrderCart as BaseGroupOrderCart;

/**
 * Skeleton subclass for representing a row from the 'group_order_cart' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class GroupOrderCart extends BaseGroupOrderCart
{
    public function getCurrentSubCustomers()
    {
        $subCustomers = [];

        $groupOrderCartItems = GroupOrderCartItemQuery::create()
            ->filterByGroupOrderCartId($this->getId())
            ->groupBySubCustomerId()
            ->find();

        /** @var GroupOrderCartItem $groupOrderCartItem */
        foreach ($groupOrderCartItems as $groupOrderCartItem) {
            $subCustomers[] = $groupOrderCartItem->getSubCustomerId();
        }

        return $subCustomers;
    }
}
