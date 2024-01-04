<?php

namespace AscentDigital\NetsuiteConnector\Cron\AdvanceExchange;

use AscentDigital\NetsuiteConnector\Model\NSCronFactory;

class AdvanceExchangeCron
{
    // protected$logger;
    protected $advancedExchangeFactory;
    protected $directory;

    /**
     * @var \AscentDigital\NetsuiteConnector\Model\NSCronFactory
     */
    protected $nsCron;

    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        // \Psr\Log\LoggerInterface $logger, 
        \Custom\AdvanceExchange\Model\AdvancedExchangeFactory $advancedExchangeFactory,
        \Magento\Framework\Filesystem\DirectoryList $directory,
        NSCronFactory $nsCron
    ) {
        // $this->logger = $logger;
        $this->advancedExchangeFactory = $advancedExchangeFactory;
        $this->directory = $directory;
        $this->nsCron = $nsCron;
    }

    public function execute()
    {
        // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/crontest.log');
        // $logger = new \Zend_Log();
        // $logger->addWriter($writer);
        // $logger->info('Advance Exchange Cron is running');
        // die('cron');
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/netsuite_cron.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("Advance Exchange cron is executed.");
        $root = $this->directory->getRoot();
        require_once($root . '/lib/PHPToolkit/NetSuiteService.php');
        $this->getCases($logger);
        //sending email to the customer
        $to = "yasirpayee02@gmail.com";
        $subject = "Advance Exchange cron";

        $message = "Advance Exchange cron run Successfully";


        $email = mail($to, $subject, $message);
        $logger->info("Advance Exchange cron is finished.");
        //add your cron job logic here.
    }
    public function getCases($logger)
    {
        $service = new \NetSuiteService();
        $service->setSearchPreferences(false, 50);
        $cron = $this->nsCron->create()->load('update_advanced_exchange_cron', 'title');
        if ($cron->getData()) {
            if ($cron->getIndex() > 1) {
                $SearchMoreWithIdRequest = new \SearchMoreWithIdRequest();
                // assigning search id 
                $SearchMoreWithIdRequest->searchId = $cron->getSearchId();

                //assigning next page index
                $SearchMoreWithIdRequest->pageIndex = $cron->getIndex();

                // search next page result on the basis of search id
                $searchResponse = $service->searchMoreWithId($SearchMoreWithIdRequest);
                $searchResult = $searchResponse->searchResult;
                $totalPages = $searchResult->totalPages;
                $pageIndex = $searchResponse->searchResult->pageIndex;
                if ($pageIndex < $totalPages) {
                    $cron->setIndex($pageIndex + 1);
                    $cron->setTotalPages($totalPages);
                    $cron->setTitle('update_advanced_exchange_cron');
                    $cron->setSearchId($searchResult->searchId);
                    $cron->save();
                } else {
                    $cron->setIndex(0);
                    $cron->setTotalPages('');
                    $cron->setTitle('update_advanced_exchange_cron');
                    $cron->setSearchId('');
                    $cron->save();
                }
            } else {
                $searchResponse = $this->searchAdvanceExchangeInNetsuite($service, $cron);
            }
        } else {
            $searchResponse = $this->searchAdvanceExchangeInNetsuite($service, $cron);
        }
        $this->processSearchResponse($searchResponse, $logger);
    }

    public function searchAdvanceExchangeInNetsuite($service, $cron)
    {
        $companySearchField = new \SearchStringField();
        $companySearchField->operator = "notEmpty";
        // $companySearchField->searchValue = 'tyler tech';
        $companySearch = new \SupportCaseSearchBasic();
        //applying search condition on field
        $companySearch->company = $companySearchField;

        // creating request
        $request = new \SearchRequest();
        $request->searchRecord = $companySearch;

        //make soap call of the created request
        $searchResponse = $service->search($request);
        $searchResult = $searchResponse->searchResult;
        $totalPages = $searchResult->totalPages;
        $pageIndex = $searchResponse->searchResult->pageIndex;
        if ($totalPages > 1) {
            if ($cron->getData()) {
                $cron->setIndex($pageIndex + 1);
                $cron->setTotalPages($totalPages);
                $cron->setTitle('update_advanced_exchange_cron');
                $cron->setSearchId($searchResult->searchId);
                $cron->save();
            } else {
                $cron = $this->nsCron->create();
                $cron->setIndex($pageIndex + 1);
                $cron->setTotalPages($totalPages);
                $cron->setTitle('update_advanced_exchange_cron');
                $cron->setSearchId($searchResult->searchId);
                $cron->save();
            }
        }
        return $searchResponse;
    }

    public function processSearchResponse($searchResponse, $logger)
    {
        $cases = $searchResponse->searchResult->recordList->record;
        foreach ($cases as $case) {
            try {
                $model = $this->advancedExchangeFactory->create();
                $model->setAddedby('NetSuite');
                $this->AddAdvancedExchange($model, $case);

                $logger->info('Records Saved Successfully');
            } catch (\Magento\Framework\Exception\AlreadyExistsException $ex) {
                $model = $this->advancedExchangeFactory->create()->load($case->internalId, 'recordid');
                // echo "<pre>";print_r($model->getData());die;
                $this->AddAdvancedExchange($model, $case);
                $logger->info('Already Exist' . $ex->getMessage());
            } catch (\Exception $ex) {
                $logger->critical(__('AscentDigital\NetsuiteConnector\Cron\AdvanceExchange\AdvanceExchangeCron' . $ex->getMessage()));
            }
        }
    }

    public function AddAdvancedExchange($model, $case)
    {
        $model->setRecordid($case->internalId);
        if ($case->company) {
            $model->setInternalid($case->company->internalId);
            $model->setCompanyName($case->company->name);
        }
        if ($case->status) {
            $model->setCaseStatus($case->status->name);
        }

        if ($case->caseNumber) {
            $model->setCasenumber($case->caseNumber);
        }
        if ($case->startDate) {
            $model->setStartdate($case->startDate);
        }
        if ($case->createdDate) {
            $model->setCreateddate($case->createdDate);
        }
        if ($case->lastModifiedDate) {
            $model->setLastmodifieddate($case->lastModifiedDate);
        }
        if ($case->lastMessageDate) {
            $model->setLastmessagedate($case->lastMessageDate);
        }
        if ($case->subsidiary) {
            $model->setSubsidiaryNsid($case->subsidiary->internalId);
            $model->setSubsidiaryName($case->subsidiary->name);
        }
        if ($case->phone) {
            $model->setPhone($case->phone);
        }
        if ($case->status) {
            $model->setStatusNsid($case->status->internalId);
            $model->setStatusName($case->status->name);
        }
        if ($case->assigned) {
            $model->setAssignedNsid($case->assigned->internalId);
            $model->setAssignedName($case->assigned->name);
        }
        if ($case->assigned) {
            $model->setAssignedNsid($case->assigned->internalId);
            $model->setAssignedName($case->assigned->name);
        }

        $customFields = $case->customFieldList->customField;
        foreach ($customFields as $customField) {
            if ($customField->scriptId == 'custevent_advanced_exchange_type') {
                $model->setExchangeType($customField->value->name);
            }
            if ($customField->scriptId == 'custevent_cost_center') {
                $model->setCostCenter($customField->value);
            }
            if ($customField->scriptId == 'custevent_damage_reason') {
                $model->setDamageReason($customField->value);
            }
            if ($customField->scriptId == 'custevent_request_return_shipping_label') {
                $model->setReturnShippingLabel($customField->value);
            }
            if ($customField->scriptId == 'custevent_ship_to_address_1') {
                $model->setShipToStreet($customField->value);
            }
            if ($customField->scriptId == 'custevent_ship_to_attention') {
                $model->setShipToAttention($customField->value);
            }
            if ($customField->scriptId == 'custevent_ship_to_city') {
                $model->setShipToCity($customField->value);
            }
            if ($customField->scriptId == 'custevent_ship_to_state') {
                $model->setShipToState($customField->value);
            }
            if ($customField->scriptId == 'custevent_ship_to_zip_code') {
                $model->setShipToZip($customField->value);
            }
            if ($customField->scriptId == 'custevent_shipping_account_number') {
                $model->setShippingAccountNo($customField->value);
            }
            if ($customField->scriptId == 'custevent_submitter_email') {
                $model->setSubmitterEmail($customField->value);
            }
            if ($customField->scriptId == 'custevent_submitter_first_name') {
                $model->setSubmitterFirstName($customField->value);
            }
            if ($customField->scriptId == 'custevent_submitter_last_name') {
                $model->setSubmitterLastName($customField->value);
            }
            // if ($customField->scriptId == 'custevent_submitter_last_name') {
            //     $model->setSubmitterLastName($customField->value);
            // }
            if ($customField->scriptId == 'custevent_case_item') {
                $model->setMsgSku($customField->value->name);
            }
            if ($customField->scriptId == 'custevent_email_customer_repair_details') {
                $model->setMsgDescription($customField->value);
            }
            if ($customField->scriptId == 'custevent_serial_validated') {
                $model->setImeiEnrolled($customField->value);
            }
            if ($customField->scriptId == 'custevent_serial_number') {
                $model->setDeviceImei($customField->value);
            }
            if ($customField->scriptId == 'custevent_repair_quote_approval_status') {
                $model->setApprovalStatus($customField->value->name);
            }

            if ($customField->scriptId == 'custevent_bill_me') {
                $model->setBillMe($customField->internalId);
                $model->setBillMeValue($customField->value);
            }
            if ($customField->scriptId == 'custevent_case_approved') {
                $model->setCaseApproved($customField->internalId);
                $model->setCaseApprovedValue($customField->value);
            }
            if ($customField->scriptId == 'custevent_case_inbound_tracking') {
                $model->setCaseInboundTracking($customField->internalId);
                $model->setCaseInboundTrackingValue($customField->value);
            }
            if ($customField->scriptId == 'custevent_case_invoice') {
                $model->setCaseInvoiceNsid($customField->value->internalId);
                $model->setCaseInvoice($customField->value->name);
            }
            if ($customField->scriptId == 'custevent_case_item_type') {
                $model->setCaseItemType($customField->internalId);
                $model->setCaseItemTypeValue($customField->value);
            }
            if ($customField->scriptId == 'custevent_case_non_serialized_item') {
                $model->setCaseNonSerializedItem($customField->internalId);
                $model->setCaseNonSerializedItemValue($customField->value);
            }
            if ($customField->scriptId == 'custevent_nci_email_customer') {
                $model->setNciEmailCustomer($customField->internalId);
                $model->setNciEmailCustomerValue($customField->value);
            }
            if ($customField->scriptId == 'custevent_reported_fault_reasons') {
                if ($customField->value) {
                    foreach ($customField->value as $value) {
                        $model->setReportedFaultReasonsNsid($value->internalId);
                        $model->setDamageReason($value->name);
                    }
                }
            }
            if ($customField->scriptId == 'custevent_return_authorization') {
                $model->setReturnAuthorizationNsid($customField->value->internalId);
                $model->setReturnAuthorization($customField->value->name);
            }
            if ($customField->scriptId == 'custevent_sales_order') {
                $model->setSalesOrderNsid($customField->value->internalId);
                $model->setSalesOrder($customField->value->name);
            }
            if ($customField->scriptId == 'custevent_second_repair') {
                $model->setSecondRepair($customField->internalId);
                $model->setSecondRepairValue($customField->value);
            }
            if ($customField->scriptId == 'custevent_under_warranty') {
                $model->setUnderWarranty($customField->internalId);
                $model->setUnderWarrantyValue($customField->value);
            }
        }
        $model->save();
    }
    // case search end 
    // case search end 


}
