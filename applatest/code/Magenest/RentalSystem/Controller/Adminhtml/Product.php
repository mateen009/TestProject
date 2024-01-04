<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Controller\Adminhtml;

use Magenest\RentalSystem\Model\RentalFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\Model\View\Result\Page;
use Magenest\RentalSystem\Model\ResourceModel\Rental\CollectionFactory as ProductCollectionFactory;
use Psr\Log\LoggerInterface;

abstract class Product extends Action
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

    /** @var ProductCollectionFactory */
    protected $_collectionFactory;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Filter $filter
     * @param LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param ProductCollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Filter $filter,
        LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        ProductCollectionFactory $collectionFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->productRepository  = $productRepository;
        $this->logger             = $logger;
        $this->_filter            = $filter;
        parent::__construct($context);
    }

    /**
     * instantiate result page object
     *
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
     *
     * @return $this
     */
    protected function _setPageData()
    {
        $resultPage = $this->getResultPage();
        $resultPage->setActiveMenu('Magenest_RentalSystem::products');
        $resultPage->getConfig()->getTitle()->prepend((__('Manage Rental Products')));

        return $this;
    }

    /**
     * Check ACL
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_RentalSystem::products');
    }
}
