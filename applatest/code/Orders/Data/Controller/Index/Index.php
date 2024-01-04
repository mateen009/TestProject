<?php
namespace Orders\Data\Controller\Index;

use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session as CustomerSession;
    
class Index extends \Magento\Framework\App\Action\Action
{
    
    protected $_customerSession;

    protected $resultPageFactory;
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    protected $_urlInterface;

    protected $customerFactory;
   
   
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CustomerFactory $customerFactory,
        array $data = []
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
    }
    
    public function execute(){
        
        echo "";
        $customerId = $this->customerSession->getCustomerId();
        if ((isset($_REQUEST["search"]))){
            
            $values= $_REQUEST["search"];
            
        if ($this->customerSession->getCustomerType() == 3) {
            $customerData = $this->customerFactory->create()->getCollection()->addAttributeToSelect('*')
            ->addAttributeToFilter("SalesManager_ID", $customerId)->addAttributeToFilter('email', array('like' => $values.'%'))->load();
            foreach($customerData as $row){
               echo "<p>" . $row["email"] . "</p>";
            }
           die();
        } else if ($this->customerSession->getCustomerType() == 4) {
            $customerData = $this->customerFactory->create()->getCollection()->addAttributeToFilter('email', array('like' => $values.'%'))->addAttributeToFilter("TerritoryManager_ID", $customerId)->load();
            foreach($customerData as $row){
               echo "<p>" . $row["email"] . "</p>";
            }
            die();
        }
            
        }
        
    }
}

