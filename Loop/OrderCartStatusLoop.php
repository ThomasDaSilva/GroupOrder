<?php


namespace GroupOrder\Loop;


use GroupOrder\Model\GroupOrderSubOrder;
use GroupOrder\Model\GroupOrderSubOrderQuery;
use Thelia\Core\Template\Element\ArraySearchLoopInterface;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Model\OrderAddressQuery;
use Thelia\Model\ProductSaleElementsQuery;
use Thelia\TaxEngine\Calculator;
use Thelia\Tools\MoneyFormat;

class OrderCartStatusLoop extends BaseLoop implements ArraySearchLoopInterface
{
    const ORDER_CART_STATUS = [
        0 => [
            'title' => 'Ouverte',
            'color' => '#F00',
            'code' => 'open'
        ],
        1 => [
            'title' => 'Fermée',
            'color' => '#0F0',
            'code' => 'close'
        ],
        2 => [
            'title' => 'A preparer',
            'color' => '#0F0',
            'code' => 'topick'
        ]
    ];

    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntTypeArgument('id', null)
        );
    }

    public function buildArray()
    {
        if(null !== $id = (int)$this->getId()){
            return [self::ORDER_CART_STATUS[$id]];
        }

        return self::ORDER_CART_STATUS;
    }

    public function parseResults(LoopResult $loopResult)
    {
        foreach ($loopResult->getResultDataCollection() as $orderCartStatus) {

            $loopResultRow = new LoopResultRow($orderCartStatus);
            $loopResultRow
                ->set("ID", $orderCartStatus['id'])
                ->set("TITLE", $orderCartStatus['title'])
                ->set("CODE", $orderCartStatus['code']);

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}