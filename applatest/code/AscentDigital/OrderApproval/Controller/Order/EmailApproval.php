<?php

namespace AscentDigital\OrderApproval\Controller\Order;

use Magento\Framework\DataObject;
use \AscentDigital\NetsuiteConnector\Helper\Data;
use Magento\Framework\App\ObjectManager;


/**
 * Approved Controller
 * 
 * approve order status
 */
class EmailApproval extends \Magento\Framework\App\Action\Action
{
    protected $resultFactory;
    protected $mail;
    protected $_customerRepositoryInterface;
    protected $_order;
    protected $messageManager;
    protected $helper;
    protected $configInterface;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Mobility\QuoteRequest\Model\MailInterface $mail,
        \Mobility\QuoteRequest\Model\ConfigInterface $configInterface,
        \Magento\Customer\Api\CustomerRepositoryInterface $_customerRepositoryInterface,
        \Magento\Sales\Api\OrderRepositoryInterface $_order,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Data $helper
    ) {
        return parent::__construct($context);
        $this->resultFactory = $resultFactory;
        $this->mail = $mail;
        $this->configInterface = $configInterface;
        $this->_customerRepositoryInterface = $_customerRepositoryInterface;
        $this->_order = $_order;
        $this->messageManager = $messageManager;
        $this->helper = $helper;
    }

    public function execute()
    {
        // $objectManager = ObjectManager::getInstance();
        // $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        // $this->storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        // $redirect_url = $this->storeManager->getStore()->getBaseUrl();
        // $redirect->setUrl($redirect_url);
        // return $redirect;
        if (isset($_REQUEST['cb'])) {
            return $this->calledbySalesRep();
            die('here');
        } else {
            return $this->emailApproval();
        }
    }

    public function calledbySalesRep()
    {
        $objectManager = ObjectManager::getInstance();
        $this->storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $this->_order = $objectManager->create('Magento\Sales\Api\OrderRepositoryInterface');
        $this->helper = $objectManager->create('AscentDigital\NetsuiteConnector\Helper\Data');
        $this->mailHelper = $objectManager->create('Cminds\Oapm\Helper\Data');
        $this->_customerRepositoryInterface = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $this->configInterface = $objectManager->create('Mobility\QuoteRequest\Model\ConfigInterface');
        $order_id = (int)$this->getRequest()->getParam('order_id');
        $approval = $this->getRequest()->getParam('approval');
        $token = $this->getRequest()->getParam('token');
        if (isset($_SESSION['customer_FirstNet'])) {
            $redirect_url = 'https://caad034362.nxcli.net/firstnet/'; //get id of customer
        } else {
            $redirect_url = $this->storeManager->getStore()->getBaseUrl();
        }

        $this->quoteRequestFactory = $objectManager->create('Mobility\QuoteRequest\Model\QuoteRequestFactory');
        // $redirect_url = ;
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $redirect->setUrl($redirect_url . '/sales/order/view/order_id/' . $order_id);
        $isError = false;

        if (isset($order_id) && isset($approval) && isset($token)) {
            try {
                $order = $this->_order->get($order_id);
                if ($order->getData('approval_1_status') == 'approved' && $order->getData('approval_2_status') == 'approved'  && $order->getData('approval_3_status') == 'approved' && empty($order->getNsInternalId())) {

                    $customer = $this->_customerRepositoryInterface->getById($order->getCustomerId());
                    // customer telephone number 
                    $isCustomerPhoneNumber = $customer->getCustomAttribute('customer_phone');
                    $customer_phone = '';
                    if ($isCustomerPhoneNumber) {
                        $customer_phone = $isCustomerPhoneNumber->getValue();
                    }
                    // customer approval
                    $quote_id = $order->getQuoteId();
                    $formQuoteCollection = $this->quoteRequestFactory->create()->getCollection();
                    $formQuote = $formQuoteCollection->addFieldToFilter('quote_id', $quote_id)->getFirstItem();
                    $customerToken = $token;
                    if ($formQuote->getCustomerEmail()) {
                        $recipientData = array(
                            'creator_name' => $order->getCustomerName(),
                            'order' => $order,
                            'quote' => $formQuote,
                            'customer' => $customer,
                            'customer_phone' => $customer_phone
                        );
                        $this->mailHelper->sendOrderPlacedCustomerNotification($recipientData, $customerToken, $formQuote);
                        $message = 'Order Approval email sent to customer successfully!';
                        $this->messageManager->addSuccess(__($message));
                        return $redirect;
                    }
                    $this->messageManager->addError(__('Some thing went wrong. Please try again!'));
                    return $redirect;
                } else {
                    $this->messageManager->addError(__('Some thing went wrong. Please try again!'));
                    return $redirect;
                }
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
                return $redirect;
            }
        }
    }

    public function emailApproval()
    {
        $objectManager = ObjectManager::getInstance();
        $this->storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $this->_order = $objectManager->create('Magento\Sales\Api\OrderRepositoryInterface');
        $this->helper = $objectManager->create('AscentDigital\NetsuiteConnector\Helper\Data');
        $this->mailHelper = $objectManager->create('Cminds\Oapm\Helper\Data');
        $this->_customerRepositoryInterface = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $this->configInterface = $objectManager->create('Mobility\QuoteRequest\Model\ConfigInterface');
        $order_id = (int)$this->getRequest()->getParam('order_id');
        $approval = $this->getRequest()->getParam('approval');
        $token = $this->getRequest()->getParam('token');
        if (isset($_SESSION['customer_FirstNet'])) {
            $redirect_url = 'https://caad034362.nxcli.net/firstnet/'; //get id of customer
        } else {
            $redirect_url = $this->storeManager->getStore()->getBaseUrl();
        }

        $this->quoteRequestFactory = $objectManager->create('Mobility\QuoteRequest\Model\QuoteRequestFactory');
        // $redirect_url = ;
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $isError = false;
        if (isset($order_id) && isset($approval) && isset($token)) {
            try {
                $order = $this->_order->get($order_id);

                $data = array(
                    'opportunity' => $order->getIncrementId(),
                    'approval_1_status' => $order->getData('approval_1_status'),
                    'approval_2_status' => $order->getData('approval_2_status'),
                );
                if ($approval == 'one' && $token == $order->getData('approval_1_token')) {
                    if ($order->getData('approval_1_token_status') != 'expired') {
                        $order->setData('approval_1_token_status', 'expired');
                        $order->setData('approval_1_status', 'approved');
                        $time = date('d-m-y h:i:s');
                        $order->setData('sales_manager_ts', $time);
                        $order->save();
                        $this->sendEmail('ascentsalesmanager@gmail.com', $order->getCustomerEmail(), $data, $this->configInterface->approvedEmailTemplate());
                        $this->messageManager->AddSuccess(__('Approval 1 status is approved.'));
                    } elseif ($order->getData('approval_1_status') != 'approved') {
                        $isError = true;
                        $this->messageManager->addError(__('Some thing went wrong. Please try again!'));
                    } else {
                        $isError = true;
                        $this->messageManager->AddSuccess(__('Approval 1 status already approved.'));
                    }
                } elseif ($approval == 'two' && $token == $order->getData('approval_2_token')) {
                    if ($order->getData('approval_2_token_status') != 'expired') {
                        $order->setData('approval_2_token_status', 'expired');
                        $order->setData('approval_2_status', 'approved');
                         $time = date('d-m-y h:i:s');
                        $order->setData('teritory_manager_ts', $time);
                        $order->save();
                        $this->sendEmail('ascentsalesmanager@gmail.com', $order->getCustomerEmail(), $data, $this->configInterface->approvedEmailTemplate());
                        $this->messageManager->AddSuccess(__('Approval 2 status is approved.'));
                    } elseif ($order->getData('approval_2_status') != 'approved') {
                        $isError = true;
                        $this->messageManager->addError(__('Some thing went wrong. Please try again!'));
                    } else {
                        $isError = true;
                        $this->messageManager->AddSuccess(__('Approval 2 status already approved.'));
                    }
                } elseif ($approval == 'three' && $token == $order->getData('approval_3_token')) {
                    if ($order->getData('approval_3_token_status') != 'expired') {
                        $order->setData('approval_3_token_status', 'expired');
                        $order->setData('approval_3_status', 'approved');
                        $time = date('d-m-y h:i:s');
                        $order->setData('exective_manager_ts', $time);
                        $order->save();
                        $this->sendEmail('ascentsalesmanager@gmail.com', $order->getCustomerEmail(), $data, $this->configInterface->approvedEmailTemplate());
                        $this->messageManager->AddSuccess(__('Approval 3 status is approved.'));
                    } elseif ($order->getData('approval_3_status') != 'approved') {
                        $isError = true;
                        $this->messageManager->addError(__('Some thing went wrong. Please try again!'));
                    } else {
                        $isError = true;
                        $this->messageManager->AddSuccess(__('Approval 3 status already approved.'));
                    }
                } elseif ($approval == 'customer' && $token == $order->getData('customer_approval_token')) {
                    if ($order->getData('customer_approval_status') != 'approved') {
                        $order->setData('customer_approval_token_status', 'expired');
                        $order->setData('customer_approval_status', 'approved');
                        
                        // $orderState =  \Magento\Sales\Model\Order::STATE_PROCESSING;
                        // $order->setState($orderState)->setStatus($orderState);
                        $order->save();
                        $this->sendEmail('ascentsalesmanager@gmail.com', $order->getCustomerEmail(), $data, $this->configInterface->approvedEmailTemplate());
                        // $this->messageManager->AddSuccess(__('Order approved successfully.'));
                    } else {
                        $redirect->setUrl($redirect_url . 'orderapproval/order/errormessage/');
                        return $redirect;
                        $this->messageManager->AddWarning(__('order already approved.'));
                    }
                } else {
                    $this->messageManager->addError(__('Some thing went wrong. Please try again!'));
                    $redirect->setUrl($redirect_url);
                    return $redirect;
                }
                if ($order->getData('customer_approval_status') == 'approved' && $order->getData('approval_1_status') == 'approved' && $order->getData('approval_2_status') == 'approved' && empty($order->getNsInternalId())) {
                    for ($i = 0; $i < 2; $i++) {
                        $response = $this->helper->orderToNetsuite($order);
                        if ($response == 'success') {
                            $redirect->setUrl($redirect_url . 'orderapproval/order/successmessage/');
                            return $redirect;
                            break;
                        }
                    }
                }

                if ($order->getData('customer_approval_email') != 'sent' && $order->getData('approval_1_status') == 'approved' && $order->getData('approval_2_status') == 'approved'  && $order->getData('approval_3_status') == 'approved' && empty($order->getNsInternalId())) {

                    $customer = $this->_customerRepositoryInterface->getById($order->getCustomerId());
                    // customer telephone number 
                    $isCustomerPhoneNumber = $customer->getCustomAttribute('customer_phone');
                    $customer_phone = '';
                    if ($isCustomerPhoneNumber) {
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
                                $this->mailHelper->sendOrderPlacedCustomerNotification($recipientData, $customerToken, $formQuote);
                                $order->setData('customer_approval_email', 'sent');
                                $order->save();
                                $this->sendEmail('ascentsalesmanager@gmail.com', $order->getCustomerEmail(), $data, $this->configInterface->approvedEmailTemplate());
                                $message = 'Order Approval email sent to customer successfully!';
                                $this->messageManager->addSuccess(__($message));
                            }
                        }
                    }
                }
                $redirect->setUrl($redirect_url);
                return $redirect;
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
                $redirect->setUrl($redirect_url);
                return $redirect;
            }
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
