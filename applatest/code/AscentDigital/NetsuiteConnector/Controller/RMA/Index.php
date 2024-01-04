<?php

namespace AscentDigital\NetsuiteConnector\Controller\RMA;

use AscentDigital\NetsuiteConnector\Helper\RMA;
Class Index extends \Magento\Framework\App\Action\Action
{
    protected $helper;
    protected $_pageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
        RMA $helper
    ) {
        
        $this->helper = $helper;
        $this->_pageFactory = $pageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        return $this->_pageFactory->create();
    }

}