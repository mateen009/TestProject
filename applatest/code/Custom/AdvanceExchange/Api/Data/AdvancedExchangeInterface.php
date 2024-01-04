<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Custom\AdvanceExchange\Api\Data;

interface AdvancedExchangeInterface
{

    const SUBMITTERPHONE = 'submitterphone';
    const DAMAGEREASON = 'damagereason';
    const SHIPLABELSTATE = 'shiplabelstate';
    const SHIPLABELSELECT = 'shiplabelselect';
    const SHIPTOZIP = 'shiptozip';
    const SHIPTOSTREET = 'shiptostreet';
    const SHIPTOSTREET2 = 'shiptostreet2';
    const SHIPTOSTATE = 'shiptostate';
    const RETURNSHIPPINGLABEL = 'returnshippinglabel';
    const SHIPTOATTENTION = 'shiptoattention';
    const SUBMITTERLASTNAME = 'submitterlastname';
    const SHIPLABELSTREET2 = 'shiplabelstreet2';
    const EXCHANGETYPE = 'exchangetype';
    const ADVANCED_EXCHANGE_ID = 'advanced_exchange_id';
    const COSTCENTER = 'costcenter';
    const DEVICEIMEI = 'deviceimei';
    const SHIPLABELCITY = 'shiplabelcity';
    const SUBMITTERFIRSTNAME = 'submitterfirstname';
    const SHIPLABELZIP = 'shiplabelzip';
    const SHIPLABELSTREET = 'shiplabelstreet';
    const MSGDESCRIPTION = 'msgdescription';
    const SAMEASSHIP = 'sameasship';
    const SIMOFDEVICE = 'simofdevice';
    const SAVEADDRESS = 'saveaddress';
    const IMEIENROLLED = 'imeienrolled';
    const SHIPLABELATTENTION = 'shiplabelattention';
    const MSGSKU = 'msgsku';
    const SUBMITTEREMAIL = 'submitteremail';
    const SHIPTOADDRESSLABEL = 'shiptoaddresslabel';
    const SHIPPINGACCOUNTNO = 'shippingaccountno';
    const ATTACHEDFILE = 'attachedfile';
    const SHIPTOCITY = 'shiptocity';
    const SHIPTOSELECT = 'shiptoselect';

    /**
     * Get advanced_exchange_id
     * @return string|null
     */
    public function getAdvancedExchangeId();

    /**
     * Set advanced_exchange_id
     * @param string $advancedExchangeId
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setAdvancedExchangeId($advancedExchangeId);

    /**
     * Get exchangetype
     * @return string|null
     */
    public function getExchangetype();

    /**
     * Set exchangetype
     * @param string $exchangetype
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setExchangetype($exchangetype);

    /**
     * Get msgsku
     * @return string|null
     */
    public function getMsgsku();

    /**
     * Set msgsku
     * @param string $msgsku
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setMsgsku($msgsku);

    /**
     * Get msgdescription
     * @return string|null
     */
    public function getMsgdescription();

    /**
     * Set msgdescription
     * @param string $msgdescription
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setMsgdescription($msgdescription);

    /**
     * Get imeienrolled
     * @return string|null
     */
    public function getImeienrolled();

    /**
     * Set imeienrolled
     * @param string $imeienrolled
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setImeienrolled($imeienrolled);

    /**
     * Get deviceimei
     * @return string|null
     */
    public function getDeviceimei();

    /**
     * Set deviceimei
     * @param string $deviceimei
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setDeviceimei($deviceimei);

    /**
     * Get simofdevice
     * @return string|null
     */
    public function getSimofdevice();

    /**
     * Set simofdevice
     * @param string $simofdevice
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setSimofdevice($simofdevice);

    /**
     * Get returnshippinglabel
     * @return string|null
     */
    public function getReturnshippinglabel();

    /**
     * Set returnshippinglabel
     * @param string $returnshippinglabel
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setReturnshippinglabel($returnshippinglabel);

    /**
     * Get submitterfirstname
     * @return string|null
     */
    public function getSubmitterfirstname();

    /**
     * Set submitterfirstname
     * @param string $submitterfirstname
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setSubmitterfirstname($submitterfirstname);

    /**
     * Get submitterlastname
     * @return string|null
     */
    public function getSubmitterlastname();

    /**
     * Set submitterlastname
     * @param string $submitterlastname
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setSubmitterlastname($submitterlastname);

    /**
     * Get submitteremail
     * @return string|null
     */
    public function getSubmitteremail();

    /**
     * Set submitteremail
     * @param string $submitteremail
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setSubmitteremail($submitteremail);

    /**
     * Get submitterphone
     * @return string|null
     */
    public function getSubmitterphone();

    /**
     * Set submitterphone
     * @param string $submitterphone
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setSubmitterphone($submitterphone);

    /**
     * Get costcenter
     * @return string|null
     */
    public function getCostcenter();

    /**
     * Set costcenter
     * @param string $costcenter
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setCostcenter($costcenter);

    /**
     * Get shiptoselect
     * @return string|null
     */
    public function getShiptoselect();

    /**
     * Set shiptoselect
     * @param string $shiptoselect
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setShiptoselect($shiptoselect);

    /**
     * Get shiptoaddresslabel
     * @return string|null
     */
    public function getShiptoaddresslabel();

    /**
     * Set shiptoaddresslabel
     * @param string $shiptoaddresslabel
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setShiptoaddresslabel($shiptoaddresslabel);

    /**
     * Get shiptoattention
     * @return string|null
     */
    public function getShiptoattention();

    /**
     * Set shiptoattention
     * @param string $shiptoattention
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setShiptoattention($shiptoattention);

    /**
     * Get shiptostreet
     * @return string|null
     */
    public function getShiptostreet();

    /**
     * Set shiptostreet
     * @param string $shiptostreet
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setShiptostreet($shiptostreet);

    /**
     * Get shiptostreet2
     * @return string|null
     */
    public function getShiptostreet2();

    /**
     * Set shiptostreet2
     * @param string $shiptostreet2
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setShiptostreet2($shiptostreet2);

    /**
     * Get shiptocity
     * @return string|null
     */
    public function getShiptocity();

    /**
     * Set shiptocity
     * @param string $shiptocity
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setShiptocity($shiptocity);

    /**
     * Get shiptostate
     * @return string|null
     */
    public function getShiptostate();

    /**
     * Set shiptostate
     * @param string $shiptostate
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setShiptostate($shiptostate);

    /**
     * Get shiptozip
     * @return string|null
     */
    public function getShiptozip();

    /**
     * Set shiptozip
     * @param string $shiptozip
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setShiptozip($shiptozip);

    /**
     * Get saveaddress
     * @return string|null
     */
    public function getSaveaddress();

    /**
     * Set saveaddress
     * @param string $saveaddress
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setSaveaddress($saveaddress);

    /**
     * Get shiplabelselect
     * @return string|null
     */
    public function getShiplabelselect();

    /**
     * Set shiplabelselect
     * @param string $shiplabelselect
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setShiplabelselect($shiplabelselect);

    /**
     * Get shiplabelattention
     * @return string|null
     */
    public function getShiplabelattention();

    /**
     * Set shiplabelattention
     * @param string $shiplabelattention
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setShiplabelattention($shiplabelattention);

    /**
     * Get shiplabelstreet
     * @return string|null
     */
    public function getShiplabelstreet();

    /**
     * Set shiplabelstreet
     * @param string $shiplabelstreet
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setShiplabelstreet($shiplabelstreet);

    /**
     * Get shiplabelstreet2
     * @return string|null
     */
    public function getShiplabelstreet2();

    /**
     * Set shiplabelstreet2
     * @param string $shiplabelstreet2
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setShiplabelstreet2($shiplabelstreet2);

    /**
     * Get shiplabelcity
     * @return string|null
     */
    public function getShiplabelcity();

    /**
     * Set shiplabelcity
     * @param string $shiplabelcity
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setShiplabelcity($shiplabelcity);

    /**
     * Get shiplabelstate
     * @return string|null
     */
    public function getShiplabelstate();

    /**
     * Set shiplabelstate
     * @param string $shiplabelstate
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setShiplabelstate($shiplabelstate);

    /**
     * Get shiplabelzip
     * @return string|null
     */
    public function getShiplabelzip();

    /**
     * Set shiplabelzip
     * @param string $shiplabelzip
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setShiplabelzip($shiplabelzip);

    /**
     * Get sameasship
     * @return string|null
     */
    public function getSameasship();

    /**
     * Set sameasship
     * @param string $sameasship
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setSameasship($sameasship);

    /**
     * Get shippingaccountno
     * @return string|null
     */
    public function getShippingaccountno();

    /**
     * Set shippingaccountno
     * @param string $shippingaccountno
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setShippingaccountno($shippingaccountno);

    /**
     * Get damagereason
     * @return string|null
     */
    public function getDamagereason();

    /**
     * Set damagereason
     * @param string $damagereason
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setDamagereason($damagereason);

    /**
     * Get attachedfile
     * @return string|null
     */
    public function getAttachedfile();

    /**
     * Set attachedfile
     * @param string $attachedfile
     * @return \Custom\AdvanceExchange\AdvancedExchange\Api\Data\AdvancedExchangeInterface
     */
    public function setAttachedfile($attachedfile);
}

