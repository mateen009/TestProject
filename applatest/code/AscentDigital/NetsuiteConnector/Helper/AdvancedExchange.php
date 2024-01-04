<?php

/**
 * ExtendedRma Helper
 */

namespace AscentDigital\NetsuiteConnector\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Filesystem\DirectoryList as Directory;

class AdvancedExchange extends AbstractHelper
{
    protected $messageManager;


    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Directory $directory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->directory = $directory;
        $this->messageManager = $messageManager;
    }
    /*
    * create order programmatically
    */
    public function addAdvanceExchange($lastRecord)
    {

        $root = $this->directory->getRoot();

        require_once($root . '/lib/PHPToolkit/NetSuiteService.php');
        $service = new \NetSuiteService();
        $service->setPreferences(false, true);

        $supportcase = new \SupportCase();
        $supportcase->externalId = $lastRecord->getData('advanced_exchange_id');
        // $supportcase->escalationMessage = $lastRecor->getData('');
        // $supportcase->caseNumber = $lastRecor->getData('');
        // $supportcase->incomingMessage = $lastRecor->getData('');
        $company = new \RecordRef;
        $company->internalId = $lastRecord->getData('internalid');
        $supportcase->company = $company;

        $supportcase->title = $lastRecord->getData('exchangetype');
        // $supportcase->email = $lastRecord->getData('');
        $supportcase->phone = $lastRecord->getData('phone');
        // $supportcase->searchSolution = $lastRecord->getData('');

        // custom fields list

        // exchangetype custom field
        $exchangetype = new \SelectCustomFieldRef();
        $exchangetype->scriptId = 'custevent_advanced_exchange_type';
        //custom field value
        $value = new \ListOrRecordRef();
        $value->name = $lastRecord->getData('exchangetype');
        $exchangetype->value = $value;

        // // bill me custom field
        // $billme = new \BooleanCustomFieldRef();
        // $billme->scriptId = 'custevent_bill_me';
        // //custom field value
        // $billme->value = $lastRecord->getData('bill_me_value');

        // // case approved custom field
        // $caseApproved = new \BooleanCustomFieldRef();
        // $caseApproved->scriptId = 'custevent_case_approved';
        // //custom field value
        // $caseApproved->value = $lastRecord->getData('case_approved_value');

        // custevent_case_inbound_tracking custom field
        // $case_inbound_tracking = new \StringCustomFieldRef();
        // $case_inbound_tracking->scriptId = 'custevent_case_non_serialized_item';
        // //custom field value
        // $case_inbound_tracking->value = $lastRecord->getData('case_inbound_tracking_value');

        // // custevent_case_non_serialized_item custom field
        // $case_non_serialized_item = new \BooleanCustomFieldRef();
        // $case_non_serialized_item->scriptId = 'custevent_case_non_serialized_item';
        // //custom field value
        // $case_non_serialized_item->value = $lastRecord->getData('case_non_serialized_item_value');

        // // custevent_case_invoice custom field
        // $case_invoice = new \SelectCustomFieldRef();
        // $case_invoice->scriptId = 'custevent_case_invoice';
        // //custom field value
        // $value = new \ListOrRecordRef();
        // $value->name = $lastRecord->getData('case_invoice');
        // $case_invoice->value = $value;

        // custevent_case_item custom field
        $case_item = new \SelectCustomFieldRef();
        $case_item->scriptId = 'custevent_case_item';
        //custom field value
        $value = new \ListOrRecordRef();
        $value->name = $lastRecord->getMsgSku();
        $case_item->value = $value;

        // custevent_case_item_type custom field
        // $case_item_type = new \BooleanCustomFieldRef();
        // $case_item_type->scriptId = 'custevent_case_item_type';
        // //custom field value
        // $case_item_type->value = $lastRecord->getData('case_item_type_value');

        // custevent_case_item_type custom field
        $cost_center = new \StringCustomFieldRef();
        $cost_center->scriptId = 'custevent_cost_center';
        //custom field value
        $cost_center->value = $lastRecord->getData('cost_center');

        // // custevent_email_customer_repair_details custom field
        // $email_customer_repair_details = new \BooleanCustomFieldRef();
        // $email_customer_repair_details->scriptId = 'custevent_email_customer_repair_details';
        // //custom field value
        // $email_customer_repair_details->value = $lastRecord->getMsgDescription();

        // // custevent_nci_email_customer custom field
        // $nci_email_customer = new \BooleanCustomFieldRef();
        // $nci_email_customer->scriptId = 'custevent_nci_email_customer';
        // //custom field value
        // $nci_email_customer->value = $lastRecord->getData('nci_email_customer_value');

        // custevent_reported_fault_reasons custom field
        $reported_fault_reasons = new \MultiSelectCustomFieldRef();
        $reported_fault_reasons->scriptId = 'custevent_reported_fault_reasons';
        //custom field value
        $value = new \ListOrRecordRef();
        $value->name = $lastRecord->getDamageReason();
        $reported_fault_reasons->value = array($value);

        // // custevent_request_return_shipping_label custom field
        // $request_return_shipping_label = new \BooleanCustomFieldRef();
        // $request_return_shipping_label->scriptId = 'custevent_request_return_shipping_label';
        // //custom field value
        // $request_return_shipping_label->value = $lastRecord->getReturnShippingLabel();

        // custevent_return_authorization custom field
        // $return_authorization = new \SelectCustomFieldRef();
        // $return_authorization->scriptId = 'custevent_return_authorization';
        // //custom field value
        // $value = new \ListOrRecordRef();
        // $value->name = $lastRecord->getData('return_authorization');
        // $value->internalId = $lastRecord->getData('return_authorization_nsid');
        // $return_authorization->value = $value;

        // custevent_sales_order custom field
        // $sales_order = new \SelectCustomFieldRef();
        // $sales_order->scriptId = 'custevent_sales_order';
        // //custom field value
        // $value = new \ListOrRecordRef();
        // $value->name = $lastRecord->getData('sales_order');
        // $value->internalId = $lastRecord->getData('sales_order_nsid');
        // $sales_order->value = $value;

        // // custevent_second_repair custom field
        // $second_repair = new \BooleanCustomFieldRef();
        // $second_repair->scriptId = 'custevent_second_repair';
        // //custom field value
        // $second_repair->value = $lastRecord->getData('second_repair_value');

        // custevent_serial_number custom field
        $serial_number = new \StringCustomFieldRef();
        $serial_number->scriptId = 'custevent_serial_number';
        //custom field value
        $serial_number->value = $lastRecord->getDeviceImei();

        // custevent_serial_validated custom field
        $serial_validated = new \BooleanCustomFieldRef();
        $serial_validated->scriptId = 'custevent_serial_validated';
        //custom field value
        $serial_validated->value = $lastRecord->getImeiEnrolled();

        // custevent_ship_label_address_1 custom field
        $ship_to_address_1 = new \StringCustomFieldRef();
        $ship_to_address_1->scriptId = 'custevent_ship_to_address_1';
        //custom field value
        $ship_to_address_1->value = $lastRecord->getShipToStreet();

        // custevent_ship_to_attention custom field
        $ship_to_attention = new \StringCustomFieldRef();
        $ship_to_attention->scriptId = 'custevent_ship_to_attention';
        //custom field value
        $ship_to_attention->value = $lastRecord->getShipToAttention();

        // custevent_ship_to_city custom field
        $ship_to_city = new \StringCustomFieldRef();
        $ship_to_city->scriptId = 'custevent_ship_to_city';
        //custom field value
        $ship_to_city->value = $lastRecord->getShipToCity();

        // custevent_ship_to_state custom field
        $ship_to_state = new \StringCustomFieldRef();
        $ship_to_state->scriptId = 'custevent_ship_to_state';
        //custom field value
        $ship_to_state->value = $lastRecord->getShipToState();

        // custevent_ship_to_zip_code custom field
        $ship_to_zip_code = new \StringCustomFieldRef();
        $ship_to_zip_code->scriptId = 'custevent_ship_to_zip_code';
        //custom field value
        $ship_to_zip_code->value = $lastRecord->getShipToZip();

        // // custevent_shipping_account_number custom field
        // $shipping_account_number = new \StringCustomFieldRef();
        // $shipping_account_number->scriptId = 'custevent_shipping_account_number';
        // //custom field value
        // $shipping_account_number->value = $lastRecord->getShippingAccountNo();

        // custevent_submitter_email custom field
        $submitter_email = new \StringCustomFieldRef();
        $submitter_email->scriptId = 'custevent_submitter_email';
        //custom field value
        $submitter_email->value = $lastRecord->getSubmitterEmail();

        // custevent_submitter_first_name custom field
        $submitter_first_name = new \StringCustomFieldRef();
        $submitter_first_name->scriptId = 'custevent_submitter_first_name';
        //custom field value
        $submitter_first_name->value = $lastRecord->getSubmitterFirstName();

        // custevent_submitter_last_name custom field
        $submitter_last_name = new \StringCustomFieldRef();
        $submitter_last_name->scriptId = 'custevent_submitter_last_name';
        //custom field value
        $submitter_last_name->value = $lastRecord->getSubmitterLastName();

        // // custevent_under_warranty custom field
        // $under_warranty = new \StringCustomFieldRef();
        // $under_warranty->scriptId = 'custevent_under_warranty';
        // //custom field value
        // $under_warranty->value = $lastRecord->getUnderWarrantyValue();



        // custom field list (value array)
        $customFieldList = new \CustomFieldList();
        $customFieldList->customField = array(
            $exchangetype, $case_item, $cost_center,
            $reported_fault_reasons,
            $serial_number, $serial_validated, $ship_to_address_1, $ship_to_attention, $ship_to_city, $ship_to_state, $ship_to_zip_code,
            $submitter_email, $submitter_first_name, $submitter_last_name
        );

        // $customField->imeienrolled = $lastRecord->getData('imeienrolled');
        // $customField->deviceimei = $lastRecord->getData('');
        // $customField->returnshippinglabel = $lastRecord->getData('');

        // $customField->shiptostreet = $lastRecord->getData('');
        // $customField->shiptostreet2 = $lastRecord->getData('');
        // $customField->shiptocity = $lastRecord->getData('');
        // // $customField->shiptozip = $lastRecord[19]; Not entered
        // $customField->shiptozip = $lastRecord->getData('');
        // $customField->saveaddress = $lastRecord->getData('');
        // $customField->shiplabelselect = $lastRecord->getData('');
        // $customField->shiplabelattention = $lastRecord->getData('');
        // $customField->shiplabelstreet = $lastRecord->getData('');
        // // $customField->shiplabelstreet = $lastRecord[25]; Not enter
        // $customField->shiplabelcity = $lastRecord->getData('');
        // $customField->shiplabelstate = $lastRecord->getData('');
        // $customField->shiplabelzip = $lastRecord->getData('');
        // // $customField->shiplabelzip = $lastRecord[29]; Not enter
        // $customField->shippingaccountno = $lastRecord->getData('');
        // $customField->damagereason = $lastRecord->getData('');
        // // $customField->damagereason = $lastRecord[32]; Not enter
        // $customField->cid = $lastRecord->getData('');
        // $customField->internalid = $lastRecord->getData('');

        // $customField->addedby = $lastRecord->getData('');
        // $customField->recordid = $lastRecord->getData('');
        // $customField->case_status = $lastRecord->getData('');
        // $customField->approval_status = $lastRecord->getData('');


        $supportcase->customFieldList = $customFieldList;

        $request = new \AddRequest();
        $request->record = $supportcase;

        $addResponse = $service->add($request);
        $success = $addResponse->writeResponse->status->isSuccess;
        $statusDetails = $addResponse->writeResponse->status->statusDetail;
        if (!$success) {
            return false;
        }
            $lastRecord->setRecordid($addResponse->writeResponse->baseRef->internalId);
            $lastRecord->save();

            return true;
        
    }
}
