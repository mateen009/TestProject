<?php
namespace Mobility\QuoteRequest\Model;

use Mobility\QuoteRequest\Api\Data\QuoteRequestInterface;
use Mobility\QuoteRequest\Model\ResourceModel\QuoteRequest as QuoteRequestResource;
use Magento\Framework\DB\Select;

class QuoteRequest extends \Magento\Framework\Model\AbstractModel implements QuoteRequestInterface
{
    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(QuoteRequestResource::class);
    }

    public function getId()
    {
        return (int) $this->getData(QuoteRequestInterface::ID);
    }

    public function setId($id)
    {
        return $this->setData(QuoteRequestInterface::ID, $id);
    }

    public function getQuoteId()
    {
        return (int) $this->getData(QuoteRequestInterface::QUOTE_ID);
    }

    public function setQuoteId($quoteId)
    {
        $this->setData(QuoteRequestInterface::QUOTE_ID, $quoteId);
    }

    public function getCustomerId()
    {
        return (int) $this->getData(QuoteRequestInterface::CUSTOMER_ID);
    }

    public function setCustomerId($customerId)
    {
        $this->setData(QuoteRequestInterface::CUSTOMER_ID, $customerId);
    }

    public function getApproval1Id()
    {
        return (int) $this->getData(QuoteRequestInterface::APPROVAL_1_ID);
    }

    public function setApproval1Id($approval1Id)
    {
        $this->setData(QuoteRequestInterface::APPROVAL_1_ID, $approval1Id);
    }

    public function getApproval2Id()
    {
        return (int) $this->getData(QuoteRequestInterface::APPROVAL_2_ID);
    }

    public function setApproval2Id($approval2Id)
    {
        $this->setData(QuoteRequestInterface::APPROVAL_2_ID, $approval2Id);
    }

    public function getStatus()
    {
        return $this->getData(QuoteRequestInterface::STATUS);
    }

    public function setStatus($status)
    {
        $this->setData(QuoteRequestInterface::STATUS, $status);
    }

    public function getOpportunity()
    {
        return $this->getData(QuoteRequestInterface::OPPORTUNITY);
    }

    public function setOpportunity($opportunity)
    {
        $this->setData(QuoteRequestInterface::OPPORTUNITY, $opportunity);
    }

    public function getQuoteName()
    {
        return $this->getData(QuoteRequestInterface::QUOTE_NAME);
    }

    public function setQuoteName($quoteName)
    {
        $this->setData(QuoteRequestInterface::QUOTE_NAME, $quoteName);
    }

    public function getAttuid()
    {
        return $this->getData(QuoteRequestInterface::ATTUID);
    }

    public function setAttuid($attuid)
    {
        $this->setData(QuoteRequestInterface::ATTUID, $attuid);
    }

    public function getSalesforceOpportunityId()
    {
        return $this->getData(QuoteRequestInterface::SALESFORCE_OPPORTUNITY_ID);
    }

    public function setSalesforceOpportunityId($salesforceOpportunityId)
    {
        $this->setData(QuoteRequestInterface::SALESFORCE_OPPORTUNITY_ID, $salesforceOpportunityId);
    }

    public function getDemoLength()
    {
        return $this->getData(QuoteRequestInterface::DEMO_LENGTH);
    }

    public function setDemoLength($demoLength)
    {
        $this->setData(QuoteRequestInterface::DEMO_LENGTH, $demoLength);
    }

    public function getAgencyName()
    {
        return $this->getData(QuoteRequestInterface::AGENCY_NAME);
    }

    public function setAgencyName($agencyName)
    {
        $this->setData(QuoteRequestInterface::AGENCY_NAME, $agencyName);
    }

    public function getAgencyStreet()
    {
        return $this->getData(QuoteRequestInterface::AGENCY_STREET);
    }

    public function setAgencyStreet($agencyStreet)
    {
        $this->setData(QuoteRequestInterface::AGENCY_STREET, $agencyStreet);
    }

    public function getAgencyCity()
    {
        return $this->getData(QuoteRequestInterface::AGENCY_CITY);
    }

    public function setAgencyCity($agencyCity)
    {
        $this->setData(QuoteRequestInterface::AGENCY_CITY, $agencyCity);
    }

    public function getAgencyState()
    {
        return $this->getData(QuoteRequestInterface::AGENCY_STATE);
    }

    public function setAgencyState($agencyState)
    {
        $this->setData(QuoteRequestInterface::AGENCY_STATE, $agencyState);
    }

    public function getAgencyZipcode()
    {
        return $this->getData(QuoteRequestInterface::AGENCY_ZIPCODE);
    }

    public function setAgencyZipcode($agencyZipcode)
    {
        $this->setData(QuoteRequestInterface::AGENCY_ZIPCODE, $agencyZipcode);
    }

    public function getDiscipline()
    {
        return $this->getData(QuoteRequestInterface::DISCIPLINE);
    }

    public function setDiscipline($discipline)
    {
        $this->setData(QuoteRequestInterface::DISCIPLINE, $discipline);
    }

    public function getPrimary()
    {
        return $this->getData(QuoteRequestInterface::PRIMARY);
    }

    public function setPrimary($primary)
    {
        $this->setData(QuoteRequestInterface::PRIMARY, $primary);
    }

    public function getCustomerEmail()
    {
        return $this->getData(QuoteRequestInterface::CUSTOMER_EMAIL);
    }

    public function setCustomerEmail($customerEmail)
    {
        $this->setData(QuoteRequestInterface::CUSTOMER_EMAIL, $customerEmail);
    }

    public function getCustomerPhone()
    {
        return $this->getData(QuoteRequestInterface::CUSTOMER_PHONE);
    }

    public function setCustomerPhone($customerPhone)
    {
        $this->setData(QuoteRequestInterface::CUSTOMER_PHONE, $customerPhone);
    }

    public function getCustomerName()
    {
        return $this->getData(QuoteRequestInterface::CUSTOMER_NAME);
    }

    public function setCustomerName($customerName)
    {
        $this->setData(QuoteRequestInterface::CUSTOMER_NAME, $customerName);
    }

    public function getOpportunitySize()
    {
        return $this->getData(QuoteRequestInterface::OPPORTUNITY_SIZE);
    }

    public function setOpportunitySize($opportunitySize)
    {
        $this->setData(QuoteRequestInterface::OPPORTUNITY_SIZE, $opportunitySize);
    }

    public function getOpportunityCloseDate()
    {
        return $this->getData(QuoteRequestInterface::OPPORTUNITY_CLOSE_DATE);
    }

    public function setOpportunityCloseDate($opportunityCloseDate)
    {
        $this->setData(QuoteRequestInterface::OPPORTUNITY_CLOSE_DATE, $opportunityCloseDate);
    }

    public function getAnticipatedDemoStartDate()
    {
        return $this->getData(QuoteRequestInterface::ANTICIPATED_DEMO_START_DATE);
    }

    public function setAnticipatedDemoStartDate($anticipatedDemoStartDate)
    {
        $this->setData(QuoteRequestInterface::ANTICIPATED_DEMO_START_DATE, $anticipatedDemoStartDate);
    }

    public function getShipToContact()
    {
        return $this->getData(QuoteRequestInterface::SHIP_TO_CONTACT);
    }

    public function setShipToContact($shipToContact)
    {
        $this->setData(QuoteRequestInterface::SHIP_TO_CONTACT, $shipToContact);
    }

    public function getAddressLine1()
    {
        return $this->getData(QuoteRequestInterface::ADDRESS_LINE_1);
    }

    public function setAddressLine1($addressLine1)
    {
        $this->setData(QuoteRequestInterface::ADDRESS_LINE_1, $addressLine1);
    }

    public function getAddressLine2()
    {
        return $this->getData(QuoteRequestInterface::ADDRESS_LINE_2);
    }

    public function setAddressLine2($addressLine2)
    {
        $this->setData(QuoteRequestInterface::ADDRESS_LINE_2, $addressLine2);
    }

    public function getCity()
    {
        return $this->getData(QuoteRequestInterface::CITY);
    }

    public function setCity($city)
    {
        $this->setData(QuoteRequestInterface::CITY, $city);
    }

    public function getState()
    {
        return $this->getData(QuoteRequestInterface::STATE);
    }

    public function setState($state)
    {
        $this->setData(QuoteRequestInterface::STATE, $state);
    }

    public function getZipcode()
    {
        return $this->getData(QuoteRequestInterface::ZIPCODE);
    }

    public function setZipcode($zipcode)
    {
        $this->setData(QuoteRequestInterface::ZIPCODE, $zipcode);
    }

    public function getMailstop()
    {
        return $this->getData(QuoteRequestInterface::MAILSTOP);
    }

    public function setMailstop($mailstop)
    {
        $this->setData(QuoteRequestInterface::MAILSTOP, $mailstop);
    }

    public function getShippingPhone()
    {
        return $this->getData(QuoteRequestInterface::SHIPPING_PHONE);
    }

    public function setShippingPhone($shippingPhone)
    {
        $this->setData(QuoteRequestInterface::SHIPPING_PHONE, $shippingPhone);
    }
}
