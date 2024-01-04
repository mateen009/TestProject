<?php
namespace AscentDigital\OrderApproval\Controller\Order;

class SuccessMessage extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_pageFactory = $pageFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		$resultPage = $this->_pageFactory->create();
		// $resultPage = $this->resultPageFactory->create();
        $block = $resultPage->getLayout()->createBlock('Magento\Framework\View\Element\Template')->setTemplate('AscentDigital_OrderApproval::order/approval_success_message.phtml')->toHtml();
            echo $block;
            die();
	}
}