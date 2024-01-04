<?php

namespace Mobility\QuoteRequest\Block\Quote\Account;

use Magento\Framework\View\Element\Template;
use Mobility\QuoteRequest\Api\Data\QuoteRequestInterface;
use Mobility\QuoteRequest\Api\QuoteRequestRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\CustomerFactory;

/**
 * Main quote Request block
 */
class Request extends Template
{
    /**
     * @var QuoteRequestRepositoryInterface
     */
    private $quoteRequestRepository;

    protected $_customerSession;

    protected $customerFactory;
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @param Template\Context $context
     * @param QuoteRequestRepositoryInterface $quoteRequestRepository
     * @param array $data
     */
    public function __construct(Template\Context $context, QuoteRequestRepositoryInterface $quoteRequestRepository, \Magento\Customer\Model\Session $customerSession, CheckoutSession $checkoutSession, CustomerFactory $customerFactory, array $data = [])
    {
        parent::__construct($context, $data);
        $this->quoteRequestRepository = $quoteRequestRepository;
        $this->_customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->customerFactory = $customerFactory;
    }

    /**
     * Fetch Customer Quote Request List
     *
     * @return mixed
     */
    public function getCustomerQuoteRequestList()
    {
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
                   $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest(
                       
                   )->getParam('limit') : 10;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseUrl = $storeManager->getStore()->getBaseUrl();
        $request = $objectManager->get('Magento\Framework\App\Action\Context')->getRequest();
        $orderFrom = $request->getParam('from');
        $orderTo = $request->getParam('to');
        $orderSearch = $request->getParam('search');
        $Orderfrom = date('Y-m-d H:i:s', strtotime($orderFrom));
        $orderTo = date('Y-m-d H:i:s', strtotime($orderTo . ' +1 day'));
        $status = $request->getParam('status');
        if (strpos($request->getParam('status'), $baseUrl . 'quote/account/request/') !== false) {
            $status = trim(str_replace($baseUrl . "quote/account/request/?status=", "", $request->getParam('status')));
        }

        $requestQuoteCollection = null;

        // $quote = $this->checkoutSession->getQuote();
        if (isset($orderSearch)) {
            if (isset($status) && !empty($status)) {
                if ($status == 'all') {
                    $status = null;
                } elseif ($status == 'converted') {
                    $status = $status;
                } elseif ($status == 'notconverted') {
                    $status = $status;
                }
            } else {
                $status = null;
            }
            $requestQuoteCollection = $this->quoteRequestRepository->getCustomerQuoteRequestList($this->getManagerReps($this->_customerSession->getCustomerId()), null, "", $Orderfrom, $orderTo, $orderSearch, $status);
        } elseif (isset($status) && !empty($status)) {
            if ($status == 'all') {
                $status = null;
            } elseif ($status == 'converted') {
                $status = $status;
            } elseif ($status == 'notconverted') {
                $status = $status;
            }
            $requestQuoteCollection = $this->quoteRequestRepository->getCustomerQuoteRequestList($this->getManagerReps($this->_customerSession->getCustomerId()), null, "", null, null, null, $status);
        } else {
            $requestQuoteCollection = $this->quoteRequestRepository->getCustomerQuoteRequestList($this->getManagerReps($this->_customerSession->getCustomerId()), null, "", null, null, null);
        }
        $requestQuoteCollection = [
            'quoteCollection' =>$requestQuoteCollection,
            'quoteStatus' => $status
        ];
        
        
        
        return $requestQuoteCollection;
    }

    //get reps data
    public function getManagerReps($customerId)
    {
        $customer = $this->customerFactory->create()->load($customerId);
        $customerType = $customer->getData('Customer_Type');
        //get all reps data
        $customers = array();
        if ($customerType == 1) {
            return $customerId;
        } else if ($customerType == 3) {
            //get sales manager reps
            $customerData = $this->customerFactory->create()->getCollection()->addAttributeToSelect("entity_id")
                ->addAttributeToFilter("SalesManager_ID", $customerId)->load();
            foreach ($customerData->getData() as $data) {
                $customers[] = $data['entity_id'];
            }
            return $customers;
        } else if ($customerType == 4) {
            // get tertory manager reps
            $customerData = $this->customerFactory->create()->getCollection()->addAttributeToSelect("entity_id")
                ->addAttributeToFilter("TerritoryManager_ID", $customerId)->load();
            foreach ($customerData->getData() as $data) {
                $customers[] = $data['entity_id'];
            }
            return $customers;
        }
    }
    
    
}
