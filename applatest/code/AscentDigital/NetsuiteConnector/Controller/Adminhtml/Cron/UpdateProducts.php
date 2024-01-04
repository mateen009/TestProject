<?php
// CHM-MA

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace AscentDigital\NetsuiteConnector\Controller\Adminhtml\Cron;

use Magento\Framework\Filesystem\DirectoryList as Directory;
use AscentDigital\NetsuiteConnector\Helper\ProductHelper;
use AscentDigital\NetsuiteConnector\Model\NSCronFactory;

/**
 * UpdateProduct Controller
 */
class UpdateProducts extends \Magento\Backend\App\Action
{
    protected $directory;

    /**
     * @var \AscentDigital\NetsuiteConnector\Model\NSCronFactory
     */
    protected $nsCron;

    /**
     * @var \AscentDigital\NetsuiteConnector\Helper\ProductHelper
     */
    protected $productHelper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        ProductHelper $productHelper,
        
        Directory $directory,
        NSCronFactory $nsCron
    ) {
        parent::__construct($context);
        $this->productHelper = $productHelper;
        
        $this->directory = $directory;
        $this->nsCron = $nsCron;
    }

    public function execute()
    {
        
        // root directory path
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/netsuite_manual_cron.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("Update Products cron is executed.");
        $root = $this->directory->getRoot();
        require_once($root . '/lib/PHPToolkit/NetSuiteService.php');
        // updated products in last 24hrs
        $this->productHelper->getUpdatedProducts($root, $logger);
        $logger->info("Update Products cron is finished");
        
    }
}
