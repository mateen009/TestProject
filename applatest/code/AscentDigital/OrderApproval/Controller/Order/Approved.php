<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace AscentDigital\OrderApproval\Controller\Order;

use Magento\Framework\Filesystem\DirectoryList as Directory;
use Magento\Framework\DataObject;
use Magento\Framework\App\ObjectManager;

/**
 * Approved Controller
 * 
 * approve order status
 */
class Approved extends \Magento\Framework\App\Action\Action
{
    protected $customerSession;
    protected $resultFactory;
    protected $configInterface;
    protected $mail;
    protected $countryFactory;
    protected $productRepository;
    protected $addressCollection;
    protected $directory;
    protected $_customerRepositoryInterface;
    protected $connection;
    protected $_order;
    protected $storeManager;
    protected $messageManager;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Mobility\QuoteRequest\Model\ConfigInterface $configInterface,
        \Mobility\QuoteRequest\Model\MailInterface $mail,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory $addressCollection,
        \Magento\Framework\Filesystem\DirectoryList $directory,
        \Magento\Customer\Api\CustomerRepositoryInterface $_customerRepositoryInterface,
        \Magento\Framework\App\ResourceConnection $connection,
        \Magento\Sales\Api\OrderRepositoryInterface $_order,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager

    ) {
        return parent::__construct($context);
        $this->countryFactory = $countryFactory;
        $this->addressCollection = $addressCollection;
        $this->productRepository = $productRepository;
        $this->directory = $directory;
        $this->_customerRepositoryInterface = $_customerRepositoryInterface;
        $this->connection = $connection;
        $this->_order = $_order;
        $this->customerSession = $customerSession;
        $this->resultFactory = $resultFactory;
        $this->configInterface = $configInterface;
        $this->mail = $mail;
        $this->messageManager = $messageManager;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $objectManager = ObjectManager::getInstance();
        $this->storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $this->helper = $objectManager->create('AscentDigital\NetsuiteConnector\Helper\Data');
        $this->maiHelper = $objectManager->create('Cminds\Oapm\Helper\Data');
        $this->directory = $objectManager->get('Magento\Framework\Filesystem\DirectoryList');
        $this->connection = $objectManager->create('Magento\Framework\App\ResourceConnection')->getConnection();
        $this->_order = $objectManager->create('Magento\Sales\Api\OrderRepositoryInterface');
        $this->customerSession = $objectManager->get('Magento\Customer\Model\SessionFactory')->create();
        $this->_customerRepositoryInterface = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $this->configInterface = $objectManager->create('Mobility\QuoteRequest\Model\ConfigInterface');
        $this->quoteRequestFactory = $objectManager->create('Mobility\QuoteRequest\Model\QuoteRequestFactory');
        // $this->productRepository = 

        if (isset($_SESSION['customer_FirstNet'])) {
            $baseUrl = 'https://caad034362.nxcli.net/firstnet/'; //get id of customer
        } else {
            $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        }
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $root = $this->directory->getRoot();
        $tableName = $this->connection->getTableName('sales_order_grid');
        $id = (int)$this->getRequest()->getParam('order_id');
        $status = $this->getRequest()->getParam('status');
        if (isset($id) && isset($status)) {
            try {
                $order = $this->_order->get($id);
                $salesManagerId = (int)$order->getData('approval_1_id');
                $territoryManagerId = (int)$order->getData('approval_2_id');
                $executiveManagerId = (int)$order->getData('approval_3_id');

                if (isset($_SESSION['customer_FirstNet']['customer_id'])) {
                    $customerId = $_SESSION['customer_FirstNet']['customer_id']; //get id of customer
                } else {
                    $customerId = $this->customerSession->getCustomerId(); //get id of customer
                }
                $customer = $this->_customerRepositoryInterface->getById($customerId);
                $isCustomerType = $customer->getCustomAttribute('Customer_Type');
                if ($isCustomerType) {
                    $customer_type = (int)$isCustomerType->getValue();
                    // SALES MANAGER TYPE = 3
                    if ($customer_type == '3' && $salesManagerId == $customerId) {
                        $order->setData('approval_1_status', $status);
                        $order->save();
                        $message = 'Order ' . $status . ' by sales manager.';
                        $this->messageManager->addSuccess(__($message));
                        $data = array();
                        $data['approval_1_status'] = $status;
                        $this->connection->update(
                            $tableName,
                            $data,
                            ['entity_id = ?' => (int)$id]
                        );
                        // TERRITORY MANAGER TYPE = 4
                    } elseif ($customer_type == '4' && $territoryManagerId == $customerId) {
                        $order->setData('approval_2_status', $status);
                        $order->save();
                        $message = 'Order ' . $status . ' by territory manager.';
                        $this->messageManager->addSuccess(__($message));
                        $data = array();
                        $data['approval_2_status'] = $status;
                        $this->connection->update(
                            $tableName,
                            $data,
                            ['entity_id = ?' => (int)$id]
                        );
                    } elseif ($customer_type == '5' && $executiveManagerId == $customerId) {
                        $order->setData('approval_3_status', $status);
                        $order->save();
                        $message = 'Order ' . $status . ' by executive manager.';
                        $this->messageManager->addSuccess(__($message));
                        $data = array();
                        $data['approval_3_status'] = $status;
                        $this->connection->update(
                            $tableName,
                            $data,
                            ['entity_id = ?' => (int)$id]
                        );
                    }
                    $data = array(
                        'opportunity' => $order->getIncrementId(),
                        'approval_1_status' => $order->getData('approval_1_status'),
                        'approval_2_status' => $order->getData('approval_2_status'),
                    );
                    if ($status == 'approved') {
                        $this->sendEmail('ascentsalesmanager@gmail.com', $order->getCustomerEmail(), $data, $this->configInterface->approvedEmailTemplate());
                    } elseif ($status == 'rejected') {
                        $this->sendEmail('ascentsalesmanager@gmail.com', $order->getCustomerEmail(), $data, $this->configInterface->rejectedEmailTemplate());
                    }
                }

                if ($order->getData('customer_approval_status') == 'approved' && $order->getData('approval_1_status') == 'approved' && $order->getData('approval_2_status') == 'approved' && empty($order->getNsInternalId())) {
                    for ($i = 0; $i < 2; $i++) {
                        $response = $this->helper->orderToNetsuite($order);
                        if ($response == 'success') {
                            break;
                        }
                    }
                }
                // 
                if ($order->getData('customer_approval_email') != 'sent' && $order->getData('approval_1_status') == 'approved' && $order->getData('approval_2_status') == 'approved'  && $order->getData('approval_3_status') == 'approved' && empty($order->getNsInternalId())) {
                    $customer = $this->_customerRepositoryInterface->getById($order->getCustomerId());
                    // customer telephone number 
                    $isCustomerPhoneNumber = $customer->getCustomAttribute('customer_phone');
                    $customer_phone = '';
                    if ($isCustomerPhoneNumber){
                        $customer_phone = $isCustomerPhoneNumber->getValue();
                    }
                    // customer approval
                    $isCustomerApproval = $customer->getCustomAttribute('customer_approval');
                    if ($isCustomerApproval) {
                        $isCustomerApprovalRequired = (int)$isCustomerApproval->getValue();
                        if ($isCustomerApprovalRequired) {
                            $quote_id = $order->getQuoteId();
                            $formQuoteCollection = $this->quoteRequestFactory->create()->getCollection();
                            $formQuote = $formQuoteCollection->addFieldToFilter('quote_id', $quote_id)->getFirstItem();
                            if ($formQuote->getCustomerEmail()) {
                                $order->setData('customer_approval_status', 'requested');
                                $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                                $customerToken = substr(str_shuffle($permitted_chars), 0, 20) . substr(str_shuffle($permitted_chars), 0, 20);
                                $order->setData('customer_approval_token', $customerToken);
                                $order->setData('customer_approval_token_status', 'not_expired');
                                $recipientData = array(
                                    'creator_name' => $order->getCustomerName(),
                                    'order' => $order,
                                    'quote' => $formQuote,
                                    'customer' => $customer,
                                    'customer_phone' => $customer_phone
                                );
                                $this->maiHelper->sendOrderPlacedCustomerNotification($recipientData, $customerToken, $formQuote);
                                $order->setData('customer_approval_email', 'sent');
                                $order->save();
                                $message = 'Order Approval email sent to customer successfully!';
                                $this->messageManager->addSuccess(__($message));
                            }
                        }
                    }
                }
                $redirect->setUrl($baseUrl . 'quote/account/orderapprovals/');
                return $redirect;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->messageManager->addError(__("No such entity exist!"));
                $redirect->setUrl($baseUrl . 'quote/account/orderapprovals/');
                return $redirect;
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
                $redirect->setUrl($baseUrl . 'quote/account/orderapprovals/');
                return $redirect;
            }
            $redirect->setUrl($baseUrl . 'quote/account/orderapprovals/');
            return $redirect;
        } else {
            $this->messageManager->addError(__("Error"));
            $redirect->setUrl($baseUrl . 'quote/account/orderapprovals/');
            return $redirect;
        }
    }

    private function sendEmail($replyTo, $recipientEmail, $data, $emailTemplate)
    {
        $this->mail = ObjectManager::getInstance()->create('Mobility\QuoteRequest\Model\MailInterface');
        $this->mail->send(
            $replyTo,
            $recipientEmail,
            ['data' => new DataObject($data)],
            $emailTemplate
        );
    }
}
