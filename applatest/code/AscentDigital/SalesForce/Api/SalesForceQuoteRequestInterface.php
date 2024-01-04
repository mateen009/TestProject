<?php

namespace AscentDigital\SalesForce\Api;

interface SalesForceQuoteRequestInterface
{
    /**
     * return salesforcequote token
     * @api
     * @param string $ATTUID__c
     * @param string $Name
     * @param string $Opp_Tracking_No__c
     * @param string $Length_of_Demo__c
     * @param string $Account_Name__c
     * @param string $FirstNet_Type__c
     * @param string $Discipline__c
     * @param string $Customer_Email_Address__c
     * @param string $Customer_Contact_Phone_Number__c
     * @param string $Customer_Name__c
     * @param string $Agency_Address_Street__c
     * @param string $Agency_Address_City__c
     * @param string $Agency_Address_State__c
     * @param string $Agency_Zip_Code__c
     * @param string $All_Rate_Plan_Quantities2__c
     * @param string $CloseDate
     * @param string $Anticipated_Demo_Start_Date__c
     * @param string $Mtel_ID__c
     * @param string $Id
     * @return array
     */

    public function getPost(
        $ATTUID__c,
        $Name,
        $Opp_Tracking_No__c,
        $Length_of_Demo__c,
        $Account_Name__c,
        $FirstNet_Type__c,
        $Discipline__c,
        $Customer_Email_Address__c,
        $Customer_Contact_Phone_Number__c,
        $Customer_Name__c,
        $Agency_Address_Street__c,
        $Agency_Address_City__c,
        $Agency_Address_State__c,
        $Agency_Zip_Code__c,
        $All_Rate_Plan_Quantities2__c,
        $CloseDate,
        $Anticipated_Demo_Start_Date__c,
        $Mtel_ID__c,
        $Id
    );
}
