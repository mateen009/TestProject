<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Orders\Data\Block;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Module\Manager;
use Magento\Reports\Model\ResourceModel\Order\CollectionFactory;

/**
 * Adminhtml dashboard sales statistics bar
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Sales extends \Magento\Backend\Block\Dashboard\Sales
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::dashboard/salebar.phtml';

    /**
     * @var Manager
     */
    protected $_moduleManager;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Manager $moduleManager,
        array $data = []
    ) {
        $this->_moduleManager = $moduleManager;
        parent::__construct($context, $collectionFactory, $moduleManager, $data);
    }

    /**
     * Prepare layout.
     *
     * @return $this|void
     */
    protected function _prepareLayout()
    {
        if (!$this->_moduleManager->isEnabled('Magento_Reports')) {
            return $this;
        }
        $isFilter = $this->getRequest()->getParam(
            'store'
        ) || $this->getRequest()->getParam(
            'website'
        ) || $this->getRequest()->getParam(
            'group'
        );

        $collection = $this->_collectionFactory->create()->calculateSales($isFilter);

        if ($this->getRequest()->getParam('store')) {
            $collection->addFieldToFilter('store_id', 1);
        }elseif ($this->getRequest()->getParam('website')) {
            $storeIds = $this->_storeManager->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
            $collection->addFieldToFilter('store_id', 1);
        } elseif ($this->getRequest()->getParam('group')) {
            $storeIds = $this->_storeManager->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
            $collection->addFieldToFilter('store_id', 1);
        }

        $collection->addFieldToFilter('store_id', 1)->load();
        $sales = $collection->getFirstItem();

        if ($this->getRequest()->getParam('store') && $this->getRequest()->getParam('store') == 2) {
           
        } else {
            $this->addTotal(__('Lifetime Sales'), $sales->getLifetime());
            $this->addTotal(__('Average Order'), $sales->getAverage());
        }
    }
}

