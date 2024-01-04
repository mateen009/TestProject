<?php
namespace Mobility\QuoteRequest\Api\Data;

interface QuoteRequestInterface
{
    const MAIN_TABLE = 'mobility_quote_request';
    const ID = 'id';
    const QUOTE_ID = 'quote_id';
    const CUSTOMER_ID = 'customer_id';
    const APPROVAL_1_ID = 'approval_1_id';
    const APPROVAL_2_ID = 'approval_2_id';
    const STATUS = 'status';
    const OPPORTUNITY = 'opportunity';
    const QUOTE_NAME = 'quote_name';
    const ATTUID = 'attuid';
    const SALESFORCE_OPPORTUNITY_ID = 'salesforce_opportunity_id';
    const DEMO_LENGTH = 'demo_length';
    const AGENCY_NAME = 'agency_name';
    const AGENCY_STREET = 'agency_street';
    const AGENCY_CITY = 'agency_city';
    const AGENCY_STATE = 'agency_state';
    const AGENCY_ZIPCODE = 'agency_zipcode';
    const DISCIPLINE = 'discipline';
    const PRIMARY = 'primary';
    const CUSTOMER_EMAIL = 'customer_email';
    const CUSTOMER_PHONE = 'customer_phone';
    const CUSTOMER_NAME = 'customer_name';
    const OPPORTUNITY_SIZE = 'opportunity_size';
    const OPPORTUNITY_CLOSE_DATE = 'opportunity_close_date';
    const ANTICIPATED_DEMO_START_DATE = 'anticipated_demo_start_date';
    const SHIP_TO_CONTACT = 'ship_to_contact';
    const ADDRESS_LINE_1 = 'address_line_1';
    const ADDRESS_LINE_2 = 'address_line_2';
    const CITY = 'city';
    const STATE = 'state';
    const ZIPCODE = 'zipcode';
    const MAILSTOP = 'mailstop';
    const SHIPPING_PHONE = 'shipping_phone';

    public function getId();

    public function setId($id);

    public function getQuoteId();

    public function setQuoteId($quoteId);

    public function getCustomerId();

    public function setCustomerId($customerId);

    public function getApproval1Id();

    public function setApproval1Id($approval1Id);

    public function getApproval2Id();

    public function setApproval2Id($approval2Id);

    public function getStatus();

    public function setStatus($status);

    public function getOpportunity();

    public function setOpportunity($opportunity);

    public function getQuoteName();

    public function setQuoteName($quoteName);

    public function getAttuid();

    public function setAttuid($attuid);

    public function getSalesforceOpportunityId();

    public function setSalesforceOpportunityId($salesforceOpportunityId);

    public function getDemoLength();

    public function setDemoLength($demoLength);

    public function getAgencyName();

    public function setAgencyName($agencyName);

    public function getAgencyStreet();

    public function setAgencyStreet($agencyStreet);

    public function getAgencyCity();

    public function setAgencyCity($agencyCity);

    public function getAgencyState();

    public function setAgencyState($agencyState);

    public function getAgencyZipcode();

    public function setAgencyZipcode($agencyZipcode);

    public function getDiscipline();

    public function setDiscipline($discipline);

    public function getPrimary();

    public function setPrimary($primary);

    public function getCustomerEmail();

    public function setCustomerEmail($customerEmail);

    public function getCustomerPhone();

    public function setCustomerPhone($customerPhone);

    public function getCustomerName();

    public function setCustomerName($customerName);

    public function getOpportunitySize();

    public function setOpportunitySize($opportunitySize);

    public function getOpportunityCloseDate();

    public function setOpportunityCloseDate($opportunityCloseDate);

    public function getAnticipatedDemoStartDate();

    public function setAnticipatedDemoStartDate($anticipatedDemoStartDate);

    public function getShipToContact();

    public function setShipToContact($shipToContact);

    public function getAddressLine1();

    public function setAddressLine1($addressLine1);

    public function getAddressLine2();

    public function setAddressLine2($addressLine2);

    public function getCity();

    public function setCity($city);

    public function getState();

    public function setState($state);

    public function getZipcode();

    public function setZipcode($zipcode);

    public function getMailstop();

    public function setMailstop($mailstop);

    public function getShippingPhone();

    public function setShippingPhone($shippingPhone);
}
