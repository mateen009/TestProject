<?php
namespace AscentDigital\SalesForce\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;

class QuoteData extends Action {
    /** @var PageFactory */
    protected $resultPageFactory;
    protected $session;
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Mobility\QuoteRequest\Model\Session $session,
        PageFactory $resultPageFactory
    ) {
        $this->session = $session;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute() {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set('Sales Force Quote');
        return $resultPage;
    }
}
