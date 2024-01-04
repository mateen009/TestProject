<?php
namespace AscentDigital\OrderApproval\Controller\Order;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Framework\Controller\ResultFactory;
    
class NetsuiteOrder extends \Magento\Framework\App\Action\Action
{
    private $orderRepository;
    protected $_pageFactory;
    protected $searchCriteriaBuilder;
    protected $helper;
    protected $_messageManager;
    protected $resultFactory;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,\Magento\Framework\View\Result\PageFactory $pageFactory,
        OrderRepositoryInterface $orderRepository,SearchCriteriaBuilder $searchCriteriaBuilder,
        \AscentDigital\NetsuiteConnector\Helper\Data $helper,\Magento\Framework\Message\ManagerInterface $messageManager,ResultFactory $resultFactory)
    {
        $this->helper = $helper;
        $this->_pageFactory = $pageFactory;
        $this->orderRepository = $orderRepository;
        $this->_messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderRepository->get($orderId);
        $successfull = $this->helper->orderToNetsuite($order);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }
}

