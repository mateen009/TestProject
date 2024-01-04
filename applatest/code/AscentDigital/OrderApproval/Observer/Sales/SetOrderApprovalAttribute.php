<?php

namespace AscentDigital\OrderApproval\Observer\Sales;

use Magento\Framework\DataObject;
use \AscentDigital\NetsuiteConnector\Helper\Data;

class SetOrderApprovalAttribute implements \Magento\Framework\Event\ObserverInterface

{
    protected $_customerRepositoryInterface;
    protected $logger;
    protected $helper;
    protected $addresss;
    protected $_checkoutSession;


    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \PerksAtWork\NextJumpSFTP\Model\OrderedCsid $orderedCsid,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,

        \Magento\Customer\Model\AddressFactory $addresss
    ) {
        $this->orderedCsid = $orderedCsid;
        $this->logger = $logger;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->helper = $helper;

        $this->_checkoutSession = $checkoutSession;
        $this->addresss = $addresss;
    }


    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $orderIds = $observer->getEvent()->getOrderIds();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $_storeId = $storeManager->getStore()->getId();
        $configInterface = $objectManager->create('Mobility\QuoteRequest\Model\ConfigInterface');
        $quoteRequest = $objectManager->create('Mobility\QuoteRequest\Model\QuoteRequestFactory');
        $mail = $objectManager->create('Mobility\QuoteRequest\Model\MailInterface');
        $_order = $objectManager->create('Magento\Sales\Api\OrderRepositoryInterface');
        $order = $_order->get($orderIds[0]);
        $quoteId = $order->getQuoteId();
        $currentQuote = $objectManager->create('\Magento\Quote\Model\QuoteFactory')->create()->load($quoteId);
        // $currentQuote = $quote->load($quoteId);
        $approval1req = 0;
        $approval2req = 0;
        $approval3req = 0;
        $customerId = $order->getCustomerId();
        if ($customerId != '') {
            $customer = $this->_customerRepositoryInterface->getById($customerId);
            $approval1required = $customer->getCustomAttribute('Approval_1_ID');
            $approval2required = $customer->getCustomAttribute('Approval_2_ID');
            $approval3required = $customer->getCustomAttribute('customer_approval');

            if ($approval1required) {
                $approval1req = $approval1required->getValue();
            }

            if ($approval2required) {
                $approval2req = $approval2required->getValue();
            }

            if ($approval3required) {
                $approval3req = $approval3required->getValue();
            }


            $order->setData('order_by', 'Customer');
        }
        // Approval logic starts
        /* Get Current Website ID */
        $websiteId = $storeManager->getStore()->getWebsiteId();
        if ($websiteId == 3) {
            // set sales manager in order
            $approval1Id = $customer->getCustomAttribute('SalesManager_ID');
            $approval1 = false;
            if ($approval1Id) {
                $appr1Id = (int)$approval1Id->getValue();
                $approval1 = $this->_customerRepositoryInterface->getById($appr1Id);
                $order->setData('approval_1_id', $approval1->getId());
                $order->setData('sm_email', $approval1->getEmail());
            }

            // set territory manager in order
            $approval2Id = $customer->getCustomAttribute('TerritoryManager_ID');
            $approval2 = false;
            if ($approval2Id) {
                $appr2Id = (int)$approval2Id->getValue();
                $approval2 = $this->_customerRepositoryInterface->getById($appr2Id);
                $order->setData('approval_2_id', $approval2->getId());
                $order->setData('tm_email', $approval2->getEmail());
            }

            // set executive manager in order
            $approval3Id = $customer->getCustomAttribute('Executive_ID');
            $approval3 = false;
            if ($approval3Id) {
                $appr3Id = (int)$approval3Id->getValue();
                $approval3 = $this->_customerRepositoryInterface->getById($appr3Id);
                $order->setData('approval_3_id', $approval3->getId());
                $order->setData('em_email', $approval3->getEmail());
            }
            $formQuoteId = $order->getFormQuoteId();
            $formQuote = $quoteRequest->create()->load($formQuoteId);
            $demoLength = $formQuote->getDemoLength();
            $agencyEmail = $formQuote->getCustomerEmail();
            $agencyName = $formQuote->getAgencyName();
            $salesForceOpportunityNumber = $formQuote->getSalesforceOpportunityId();
            $qtyOrdered = $order->getTotalQtyOrdered();
            $storeId = $storeManager->getStore()->getId();
            $order->setDemoLength($demoLength);
            $order->setAgencyEmail($agencyEmail);
            $order->setAgencyName($agencyName);
            $order->setSalesForceOpportunityNumber($salesForceOpportunityNumber);
            $order->save();
            // reverting default address
            $shippingAddressId = (int)$formQuote->getPreviousDefaultShipping();
            if (!empty($shippingAddressId) && $shippingAddressId > 0) {
                $shippingAddress = $this->addresss->create()->load($shippingAddressId);
                $shippingAddress->setCustomerId($customerId);
                $shippingAddress->setIsDefaultShipping('1');
                $shippingAddress->save();
                $formQuote->setPreviousDefaultShipping('');
                $formQuote->save();
            }

            $billingAddressId = (int)$formQuote->getPreviousDefaultBilling();
            if (!empty($billingAddressId) && $billingAddressId > 0) {
                $billingAddress = $this->addresss->create()->load($billingAddressId);
                $billingAddress->setCustomerId($customerId);
                $billingAddress->setIsDefaultBilling('1');
                $billingAddress->save();
                $formQuote->setPreviousDefaultBilling('');
                $formQuote->save();
            }
            // end reverting default address

            if ($approval1req == 0 && $approval2req == 0 && $approval3req == 0) {
                $order->setData('customer_approval_status', 'approved');
                $order->setData('approval_1_status', 'approved');
                $order->setData('approval_2_status', 'approved');
                $order->setData('approval_3_status', 'approved');
                $order->save();
            } elseif ($demoLength == '30') {
                if ($qtyOrdered <= 5) {
                    // Flow is FN Manager (Sales Manager) only and then the email out to the End Customer
                    $isApproval2NotRequired = true;
                    $isApproval3NotRequired = true;
                    // send email to sales manager
                    $this->salesManagerEmail($approval1, $order, $mail, $storeId, $isApproval2NotRequired, $isApproval3NotRequired, $demoLength);
                    $order->setCustomerApprovalEmail('notSent');
                    $order->save();
                } elseif ($qtyOrdered <= 10 && $qtyOrdered > 5) {
                    // Flow is FN Manager (Sales Manager), then FN Director (Territory Manager) and then the email out to the End Customer
                    $isApproval2NotRequired = false;
                    $isApproval3NotRequired = true;
                    // send email to sales manager
                    $this->salesManagerEmail($approval1, $order, $mail, $storeId, $isApproval2NotRequired, $isApproval3NotRequired, $demoLength);
                    // send email to territory manager
                    $this->territoryManagerEmail($approval2, $order, $mail, $storeId, $demoLength);
                    $order->setCustomerApprovalEmail('notSent');
                    $order->save();
                } elseif ($qtyOrdered > 10) {
                    // Flow is FN Manager (Sales Manager), and then to the SVP (Iain Lamb) (Third Approver / always the same approver) and then the email out to the End Customer
                    $isApproval2NotRequired = true;
                    $isApproval3NotRequired = false;
                    // send email to sales manager
                    $this->salesManagerEmail($approval1, $order, $mail, $storeId, $isApproval2NotRequired, $isApproval3NotRequired, $demoLength);
                    // send email to executive manager
                    $this->executiveManagerEmail($approval3, $order, $mail, $storeId, $isApproval2NotRequired, $demoLength);
                    $order->setCustomerApprovalEmail('notSent');
                    $order->save();
                }
            } elseif ($demoLength == '45') {
                // Flow is FN Manager (Sales Manager), then FN Director (Territory Manager) and then the email out to the End Customer
                $isApproval2NotRequired = false;
                $isApproval3NotRequired = true;
                // send email to sales manager
                $this->salesManagerEmail($approval1, $order, $mail, $storeId, $isApproval2NotRequired, $isApproval3NotRequired, $demoLength);
                // send email to territory manager
                $this->territoryManagerEmail($approval2, $order, $mail, $storeId, $demoLength);
                $order->setCustomerApprovalEmail('notSent');
                $order->save();
            } elseif ($demoLength == '60') {
                // Flow is FN Manager (Sales Manager), and then to the SVP (Iain Lamb) (Third Approver / always the same approver) and then the email out to the End Customer
                $isApproval2NotRequired = true;
                $isApproval3NotRequired = false;
                // send email to sales manager
                $this->salesManagerEmail($approval1, $order, $mail, $storeId, $isApproval2NotRequired, $isApproval3NotRequired, $demoLength);
                // send email to executive manager
                $this->executiveManagerEmail($approval3, $order, $mail, $storeId, $isApproval2NotRequired, $demoLength);
                $order->setCustomerApprovalEmail('notSent');
                $order->save();
            } elseif ($demoLength == '61') {
                // Flow is FN Manager (Sales Manager), and then to the SVP (Iain Lamb) (Third Approver / always the same approver) and then the email out to the End Customer
                $isApproval2NotRequired = true;
                $isApproval3NotRequired = false;
                // send email to sales manager
                $this->salesManagerEmail($approval1, $order, $mail, $storeId, $isApproval2NotRequired, $isApproval3NotRequired, $demoLength);
                // send email to executive manager
                $this->executiveManagerEmail($approval3, $order, $mail, $storeId, $isApproval2NotRequired, $demoLength);
                $order->setCustomerApprovalEmail('notSent');
                $order->save();
            }
        } else {
            if ($websiteId == 5) {
                
                // $this->orderedCsid->setCsid($_SESSION["csid"]);
                // we have created table which save csid, through this code we save the csid id in the table
                // we have commented this code because client told us to comment this code in 20 nov 2023 meeting.
                // $this->orderedCsid->setCsid($this->getCheckoutSession()->getCsid());
                // $this->orderedCsid->setOrderId($order->getId());
                // $this->orderedCsid->save();
                // $order->setData('csid', $_SESSION["csid"]);
                $order->setData('csid', $this->getCheckoutSession()->getCsid());
                $order->setData('is_csv_generated', 'no');
                $order->setData('pixel_carrier_service',$currentQuote->getPixelCarrierService());
            }

            $order->setData('customer_approval_status', 'approved');
            $order->setData('approval_1_status', 'approved');
            $order->setData('approval_2_status', 'approved');
            $order->setData('approval_3_status', 'approved');
            $order->setData('order_type', 'new order');
            $order->save();
        }

        if ($order->getData('customer_approval_status') == 'approved' && $order->getData('approval_1_status') == 'approved' && $order->getData('approval_2_status') == 'approved'  && $order->getData('approval_3_status') == 'approved' && empty($order->getNsInternalId())) {
            for ($i = 0; $i < 2; $i++) {
                $response = $this->helper->orderToNetsuite($order);
                if ($response == 'success') {
                    break;
                }
            }
        }
    }

    private function sendEmail($replyTo, $recipientEmail, $data, $emailTemplate, $mail)
    {
        $mail->send(
            $replyTo,
            $recipientEmail,
            ['data' => new DataObject($data)],
            $emailTemplate
        );
    }

    /**
     * salesManagerEmail
     */
    public function salesManagerEmail($approval1, $order, $mail, $storeId, $isApproval2NotRequired, $isApproval3NotRequired, $demoLength)
    {
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $approval2status = 'requested';
        $approval3status = 'requested';

        if ($isApproval2NotRequired) {
            $order->setData('approval_2_status', 'approved');
            $approval2status = 'not required';
        }

        if ($isApproval3NotRequired) {
            $order->setData('approval_3_status', 'approved');
            $approval3status = 'not required';
        }
        if ($approval1) {
            $order->setData('approval_1_status', 'requested');
            $token1 = substr(str_shuffle($permitted_chars), 0, 20) . substr(str_shuffle($permitted_chars), 0, 20);
            $order->setData('approval_1_token', $token1);
            $order->setData('approval_1_token_status', 'not_expired');
            // data to email

            $data = array();
            $data['store_id'] = $storeId == 2 ? $storeId : '';
            $data['name'] = $order->getCustomerName();
            $data['order'] = $order; //order Data
            $data['demoLength'] = $demoLength . " days";
            $data['increment_id'] = $order->getIncrementId();
            $data['approval_1_status'] = 'requested';
            $data['approval_2_status'] = $approval2status;
            $data['approval_3_status'] = $approval3status;
            // $data['customer_approval_status'] = $customerapprovalstatus;
            $data['order_id'] = $order->getId();
            $data['opportunity'] = "Order Approval Request!";
            $data['approval'] = 'one';
            $data['token'] = $token1;
            $emailTemplate = 4;
            $this->sendEmail($order->getCustomerEmail(), $approval1->getEmail(), $data, $emailTemplate, $mail);
            // out of office email
            $outOfOffice = $approval1->getCustomAttribute('ooo_enabled');
            if ($outOfOffice && $outOfOffice->getValue()) {
                $outOfOfficeStart = $approval1->getCustomAttribute('ooo_startdate');
                $outOfOfficeEnd = $approval1->getCustomAttribute('ooo_enddate');
                if ($outOfOfficeStart && $outOfOfficeEnd && !empty($outOfOfficeStart->getValue()) && !empty($outOfOfficeEnd->getValue())) {
                    if (date('Y-m-d') >= date('Y-m-d', strtotime($outOfOfficeStart->getValue()) && date('Y-m-d') <= date('Y-m-d', strtotime($outOfOfficeEnd->getValue())))) {
                        $outOfOfficeEmail = $approval1->getCustomAttribute('ooo_email');
                        if ($outOfOfficeEmail && !empty($outOfOfficeEmail->getValue())) {
                            $this->sendEmail($order->getCustomerEmail(), $outOfOfficeEmail->getValue(), $data, $emailTemplate, $mail);
                        }
                    }
                }
            }
        } else {
            $order->setData('approval_1_status', 'approved');
        }


        $order->save();
    }

    /**
     * territoryManagerEmail
     */
    public function territoryManagerEmail($approval2, $order, $mail, $storeId, $demoLength)
    {
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $order->setData('approval_3_status', 'approved');
        if ($approval2) {
            $order->setData('approval_2_status', 'requested');
            $token1 = substr(str_shuffle($permitted_chars), 0, 20) . substr(str_shuffle($permitted_chars), 0, 20);
            $order->setData('approval_2_token', $token1);
            $order->setData('approval_2_token_status', 'not_expired');
            // data to email

            $data = array();
            $data['store_id'] = $storeId == 2 ? $storeId : '';
            $data['name'] = $order->getCustomerName();
            $data['order'] = $order; //order Data
            $data['demoLength'] = $demoLength . " days";
            $data['increment_id'] = $order->getIncrementId();
            $data['approval_1_status'] = 'requested';
            $data['approval_2_status'] = 'requested';
            $data['approval_3_status'] = 'not required';
            // $data['customer_approval_status'] = $customerapprovalstatus;
            $data['order_id'] = $order->getId();
            $data['opportunity'] = "Order Approval Request!";
            $data['approval'] = 'two';
            $data['token'] = $token1;
            $emailTemplate = 4;
            $this->sendEmail($order->getCustomerEmail(), $approval2->getEmail(), $data, $emailTemplate, $mail);
            // out of office email
            $outOfOffice = $approval2->getCustomAttribute('ooo_enabled');
            if ($outOfOffice && $outOfOffice->getValue()) {
                $outOfOfficeStart = $approval2->getCustomAttribute('ooo_startdate');
                $outOfOfficeEnd = $approval2->getCustomAttribute('ooo_enddate');
                if ($outOfOfficeStart && $outOfOfficeEnd && !empty($outOfOfficeStart->getValue()) && !empty($outOfOfficeEnd->getValue())) {
                    if (date('Y-m-d') >= date('Y-m-d', strtotime($outOfOfficeStart->getValue()) && date('Y-m-d') <= date('Y-m-d', strtotime($outOfOfficeEnd->getValue())))) {
                        $outOfOfficeEmail = $approval2->getCustomAttribute('ooo_email');
                        if ($outOfOfficeEmail && !empty($outOfOfficeEmail->getValue())) {
                            $this->sendEmail($order->getCustomerEmail(), $outOfOfficeEmail->getValue(), $data, $emailTemplate, $mail);
                        }
                    }
                }
            }
        } else {
            $order->setData('approval_2_status', 'approved');
        }


        $order->save();
    }

    /**
     * executiveManagerEmail
     */
    public function executiveManagerEmail($approval3, $order, $mail, $storeId, $isApproval2NotRequired, $demoLength)
    {
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $approval2status = 'requested';
        if ($isApproval2NotRequired) {
            $order->setData('approval_2_status', 'approved');
            $approval2status = 'not required';
        }
        if ($approval3) {
            $order->setData('approval_3_id', $approval3->getId());
            $order->setData('approval_3_status', 'requested');
            $token1 = substr(str_shuffle($permitted_chars), 0, 20) . substr(str_shuffle($permitted_chars), 0, 20);
            $order->setData('approval_3_token', $token1);
            $order->setData('approval_3_token_status', 'not_expired');
            // data to email

            $data = array();
            $data['store_id'] = $storeId == 2 ? $storeId : '';
            $data['name'] = $order->getCustomerName();
            $data['order'] = $order; //order Data
            $data['demoLength'] = $demoLength . " days";
            $data['increment_id'] = $order->getIncrementId();
            $data['approval_1_status'] = 'requested';
            $data['approval_2_status'] = $approval2status;
            $data['approval_3_status'] = 'requested';
            // $data['customer_approval_status'] = $customerapprovalstatus;
            $data['order_id'] = $order->getId();
            $data['opportunity'] = "Order Approval Request!";
            $data['approval'] = 'three';
            $data['token'] = $token1;
            $emailTemplate = 4;
            $this->sendEmail($order->getCustomerEmail(), $approval3->getEmail(), $data, $emailTemplate, $mail);
            // out of office email
            $outOfOffice = $approval3->getCustomAttribute('ooo_enabled');
            if ($outOfOffice && $outOfOffice->getValue()) {
                $outOfOfficeStart = $approval3->getCustomAttribute('ooo_startdate');
                $outOfOfficeEnd = $approval3->getCustomAttribute('ooo_enddate');
                if ($outOfOfficeStart && $outOfOfficeEnd && !empty($outOfOfficeStart->getValue()) && !empty($outOfOfficeEnd->getValue())) {
                    if (date('Y-m-d') >= date('Y-m-d', strtotime($outOfOfficeStart->getValue()) && date('Y-m-d') <= date('Y-m-d', strtotime($outOfOfficeEnd->getValue())))) {
                        $outOfOfficeEmail = $approval3->getCustomAttribute('ooo_email');
                        if ($outOfOfficeEmail && !empty($outOfOfficeEmail->getValue())) {
                            $this->sendEmail($order->getCustomerEmail(), $outOfOfficeEmail->getValue(), $data, $emailTemplate, $mail);
                        }
                    }
                }
            }
        } else {
            $order->setData('approval_3_status', 'approved');
        }


        $order->save();
    }
    public function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }
}
