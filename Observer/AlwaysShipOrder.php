<?php
/**
 * Product : B2bzanden Always Ship
 *
 * @copyright Copyright © 2020 B2bzanden. All rights reserved.
 * @author    Isolde van Oosterhout & Hans Kuijpers
 */

namespace B2bzanden\AlwaysShip\Observer;

use B2bzanden\AlwaysShip\Model\Config;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AlwaysShipOrder implements ObserverInterface
{
    protected $config;
    protected $stockState;
    protected $sourceItemsSaveInterface;
    protected $sourceItemFactory;
    protected $devLog;
    protected $devLogging = false;

    public function __construct(
        \B2bzanden\AlwaysShip\Model\Config $config,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Magento\InventoryApi\Api\SourceItemsSaveInterface $sourceItemsSaveInterface,
        \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory $sourceItemFactory
    )
    {
        $this->config = $config;
        $this->stockState = $stockState;
        $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
        $this->sourceItemFactory = $sourceItemFactory;
        if ($this->devLogging) {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/b2bzanden_always_ship.log');
            $this->devLog = new \Zend\Log\Logger();
            $this->devLog->addWriter($writer);
        }
    }

    public function execute(Observer $observer)
    {
        if ($this->config->isEnabled()) {
            if ($this->devLogging) {
                $this->devLog->info(print_r('--AlwaysShipOrder execute', true));
            }
            $items = $observer->getEvent()->getShipment()->getAllItems();
            if ($items) {
                foreach ($items as $item) {
                    $productId = $item->getProductId();
                    $qty = $this->stockState->getStockQty($productId);
                    $itemQty = $item->getQty();
                    if ($this->devLogging) {
                        $this->devLog->info(print_r('-- available qty ' . $qty, true));
                        $this->devLog->info(print_r('-- qty to ship ' . $itemQty, true));
                    }
                    if ($qty < $itemQty) {
                        $sourceItem = $this->sourceItemFactory->create();
                        $sourceItem->setSourceCode('default');
                        $sourceItem->setSku($item->getSku());
                        $sourceItem->setQuantity($itemQty);
                        $sourceItem->setStatus(1);
                        $this->sourceItemsSaveInterface->execute([$sourceItem]);
                    }
                }
            }
        }
        if ($this->devLogging) {
            $this->devLog->info(print_r('--AlwaysShipOrder extension is not enabled', true));
        }
    }
}
