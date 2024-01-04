<?php

namespace Mobility\SalesTeamManagement\Controller\SalesManager;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Reflection\DataObjectProcessor;

class Post extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    
    protected $resultPageFactory;
    protected $customer;
    
    protected $scopeConfig;
    protected $transportBuilder;
    protected $customerRegistry;
    protected $dataProcessor;
    protected $_customerRepository;
    protected $_mathRandom;
    protected $_accountmanagement;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param CustomerInterfaceFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerSession $customerSession
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        CustomerInterfaceFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        CustomerSession $customerSession,
        \Magento\Customer\Model\CustomerFactory $customer,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Customer\Model\AccountManagement $accountmanagement,
        \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->customer = $customer;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->dataProcessor = $dataProcessor;
        $this->_accountmanagement = $accountmanagement;
        $this->_customerRepository = $customerRepository;
        $this->_mathRandom = $mathRandom;
        $this->customerRegistry = $customerRegistry;
        $this->senderResolver = $senderResolver ?? ObjectManager::getInstance()->get(SenderResolverInterface::class);
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * Post customer Data
     *
     * @return Redirect
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        try {
            /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
            // CHM YM : check if id exist then edit customer otherwise add new
            if ($this->getRequest()->getPost('id')) {
                $add_new_sales_manager = false;
                $customer = $this->_customerRepository->getById((int)$this->getRequest()->getPost('id'));
            } else {
                $add_new_sales_manager = true;
                $customer = $this->customerFactory->create();
                // $customer->setCustomAttribute('Approval_1_ID', 1);
                // $customer->setCustomAttribute('Approval_2_ID', 1);
            }
            
            $nsInteralId = $this->getRequest()->getPost('ns_internal_id');
            $attuId = $this->getRequest()->getPost('attu_id');

            $customer->setStoreId($this->storeManager->getStore()->getStoreId());
            $customer->setWebsiteId($this->storeManager->getStore()->getWebsiteId());
            $customer->setEmail($this->getRequest()->getPost('email'));
            $customer->setFirstname($this->getRequest()->getPost('firstname'));
            $customer->setLastname($this->getRequest()->getPost('lastname'));

            $customer->setCustomAttribute('ns_internal_id', $nsInteralId);
            $customer->setCustomAttribute('attu_id', $attuId);
            // CHM YM : saving password for test
            $hashedPassword = ObjectManager::getInstance()->get('\Magento\Framework\Encryption\EncryptorInterface')->getHash('Password@123', true);

            $customer->setStoreId($this->storeManager->getStore()->getStoreId());
            $customer->setCustomAttribute('Customer_Type', 3);
            $customer->setCustomAttribute('TerritoryManager_ID', $this->customerSession->getCustomerId());
            $customer->setCustomAttribute('customers_company', $this->getRequest()->getPost('customer_company'));
            $customer->setCustomAttribute('send_customers_email', $this->getRequest()->getPost('send_email'));
            // get current customer(sales manager)
            $cust = $this->_customerRepository->getById($this->customerSession->getCustomerId());
//            // setting territory manager id
            $isTerritoryManager = $cust->getCustomAttribute('TerritoryManager_ID');
            if ($isTerritoryManager) {
                $territoryManagerId = (int)$isTerritoryManager->getValue();
                $customer->setCustomAttribute('TerritoryManager_ID', $territoryManagerId);
            }

              // setting executive manager id
              $isExecutiveManager = $cust->getCustomAttribute('Executive_ID');
              if ($isExecutiveManager) {
                  $executiveManagerId = (int)$isExecutiveManager->getValue();
                  $customer->setCustomAttribute('Executive_ID', $executiveManagerId);
              }
            
            $data =  "array of customer Data";
            $emailTemplate = "custom_reset_password";
            
            $customer = $this->_customerRepository->save($customer, $hashedPassword);
            
            // Note: The save returns the saved customer object, else throws an exception.
            if ($add_new_sales_manager) {
                $this->messageManager->addSuccessMessage(
                    __('Sales Manager added successfully!')
                );
            } else {
                $this->messageManager->addSuccessMessage(
                    __('Sales Manager updated successfully!')
                );
            }
            
            
            $emailSendStatus = $this->sendEmailToCustomer($customer, $emailTemplate);
            
            
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __('An error occurred while processing your form. Please try again later.')
            );
        }
        return $this->resultRedirectFactory->create()->setPath('customer/salesmanager/index');
    }
    
    public function getStoreId($websiteId)
    {
        return $this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
    }

    public function sendEmailToCustomer($customer, $emailTemplate)
    {
        $storeId = $this->storeManager->getStore()->getStoreId();
        $customerEmailData = $this->getFullCustomerObject($customer);
        $templateParams = [];
        
        $templateParams['email'] = $this->getRequest()->getPost('email');
        $templateParams['customer'] = $customerEmailData;
        $templateParams['store'] = $this->storeManager->getStore($storeId);

        $sender = \Magento\Customer\Model\EmailNotification::XML_PATH_FORGOT_EMAIL_IDENTITY;

        $from = $this->senderResolver->resolve(
            $this->scopeConfig->getValue($sender, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId),
            $storeId
        );

        $customerName = $customer->getFirstname() . " " . $customer->getLastname();

        try {
            $transport = $this->transportBuilder->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions(['area' => 'frontend', 'store' => $storeId])
                ->setTemplateVars($templateParams)
                ->setFrom($from)
                ->addTo($customer->getEmail(), $customerName)
                ->getTransport();
            $transport->sendMessage();
            $this->logger->info($customer->getEmail() . ' : Email Send Successfully.');
        } catch (Exception $e) {
            $this->logger->error($customer->getEmail() . ' : Something went wrong when sending email');
        }
    }

    public function getFullCustomerObject($customer)
    {
        $mergedCustomerData = $this->customerRegistry->retrieveSecureData($customer->getId());
        $customerData = $this->dataProcessor
            ->buildOutputDataArray($customer, CustomerInterface::class);
        $mergedCustomerData->addData($customerData);
        $customerName = $customer->getFirstname() . " " . $customer->getLastname();
        $mergedCustomerData->setData('name', $customerName);
        $tokenVal = $this->getToken($customer->getEmail(), $customer->getWebsiteId());
        $mergedCustomerData->setData('rp_token', $tokenVal);
        return $mergedCustomerData;
    }

    public function getToken($email, $websiteId)
    {
        $customer = $this->_customerRepository->get($email, $websiteId);
        $newPasswordToken = $this->_mathRandom->getUniqueHash();
        $this->_accountmanagement->changeResetPasswordLinkToken($customer, $newPasswordToken);
        return $newPasswordToken;
    }
}
