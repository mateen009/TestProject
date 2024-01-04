<?php
namespace AscentDigital\OrderApproval\Controller\Order;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;

class ViewOrder extends Action {
    /** @var PageFactory */
    protected $resultPageFactory;
    protected $session;
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute() {

        $resultPage = $this->resultPageFactory->create();
        $block = $resultPage->getLayout()->createBlock('Magento\Framework\View\Element\Template')->setTemplate('AscentDigital_OrderApproval::order/view.phtml')->toHtml();
            echo $block;
            die();
    }
}

