<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Controller\Adminhtml;

use Magenest\RentalSystem\Model\RentalOrderFactory;
use Magenest\RentalSystem\Model\ResourceModel\RentalOrder;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\Model\View\Result\Page;
use Magenest\RentalSystem\Helper\Rental as RentalHelper;
use Magenest\RentalSystem\Model\ResourceModel\RentalOrder\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\Order\ItemFactory as OrderItem;
use Psr\Log\LoggerInterface;

abstract class Order extends Action
{
    /**
     * Page result factory
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Page factory
     * @var Page
     */
    protected $_resultPage;

    /**
     * Mass Action Filter
     * @var Filter
     */
    protected $_filter;

    /** @var RentalOrderFactory */
    protected $_rentalOrderFactory;

    /** @var \Magenest\RentalSystem\Model\ResourceModel\RentalOrder */
    protected $rentalOrderResource;

    /** @var OrderCollectionFactory */
    protected $_collectionFactory;

    /** @var RentalHelper */
    protected $_rentalHelper;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * Order constructor.
     *
     * @param Context $context
     * @param RentalOrderFactory $rentalOrderFactory
     * @param RentalOrder $rentalOrderResource
     * @param PageFactory $resultPageFactory
     * @param Filter $filter
     * @param RentalHelper $_rentalHelper
     * @param OrderCollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        RentalOrderFactory $rentalOrderFactory,
        RentalOrder $rentalOrderResource,
        PageFactory $resultPageFactory,
        Filter $filter,
        RentalHelper $_rentalHelper,
        OrderCollectionFactory $collectionFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->_rentalOrderFactory = $rentalOrderFactory;
        $this->rentalOrderResource = $rentalOrderResource;
        $this->_resultPageFactory  = $resultPageFactory;
        $this->_collectionFactory  = $collectionFactory;
        $this->_filter             = $filter;
        $this->_rentalHelper       = $_rentalHelper;
        $this->logger              = $logger;
    }

    /**
     * instantiate result page object
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page
     */
    public function getResultPage()
    {
        if ($this->_resultPage === null) {
            $this->_resultPage = $this->_resultPageFactory->create();
        }

        return $this->_resultPage;
    }

    /**
     * set page data
     * @return $this
     */
    protected function _setPageData()
    {
        $resultPage = $this->getResultPage();
        $resultPage->setActiveMenu('Magenest_RentalSystem::orders');
        $resultPage->getConfig()->getTitle()->prepend((__('Manage Rental Orders')));

        return $this;
    }

    /**
     * Check ACL
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_RentalSystem::orders');
    }
}
