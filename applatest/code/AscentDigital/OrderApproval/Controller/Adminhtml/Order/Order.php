<?php

namespace AscentDigital\OrderApproval\Controller\Adminhtml\Order;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;

class Order extends Action
{

    private $orderRepository;
    protected $_pageFactory;
    protected $searchCriteriaBuilder;
    protected $helper;
    protected $_messageManager;
    protected $resultFactory;

    protected $logger;


    public function __construct(
        Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \AscentDigital\NetsuiteConnector\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        ResultFactory $resultFactory
    ) {
        $this->helper = $helper;
        $this->_pageFactory = $pageFactory;
        $this->orderRepository = $orderRepository;
        $this->_messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderRepository->get($orderId);
        $success = false;
        for ($i = 0; $i < 2; $i++) {
            $response = $this->helper->orderToNetsuite($order);
            if ($response == 'success') {
                $success = true;
                break;
            }
        }
        if ($success) {
            $this->_messageManager->addSuccess(__("Successfully sent to netsuite"));
        } else {
            $this->_messageManager->addSuccess(__("Something went wrong, Please try again!"));
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
