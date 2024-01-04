<?php
namespace Mobility\SalesTeamManagement\Block\Customer\SalesRep;

use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session as CustomerSession;
    
class SalesRepAutoFill extends Template
{
    
    protected $_customerSession;

    
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    protected $_urlInterface;

    protected $customerFactory;
   
   
    public function __construct(
                                Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\UrlInterface $urlInterface,
        CustomerFactory $customerFactory,
        array $data = []
    ) {
        parent::__construct($context);
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
    }
    
    public function getAllBackOrders(){
        $customerId = $this->customerSession->getCustomerId();
        
        if ($this->customerSession->getCustomerType() == 3) {
            $customerData = $this->customerFactory->create()->getCollection()->addAttributeToSelect('*')
                ->addAttributeToFilter("SalesManager_ID", $customerId)->load();
            
        } else if ($this->customerSession->getCustomerType() == 4) {
            $customerData = $this->customerFactory->create()->getCollection()->addAttributeToSelect('*')
                ->addAttributeToFilter("TerritoryManager_ID", $customerId)->load();
        }
        
        return $customerData;
    }
}

