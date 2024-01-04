<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Custom\AdvanceExchange\Model;

use Custom\AdvanceExchange\Api\Data\AdvancedExchangeInterface;
use Magento\Framework\Model\AbstractModel;

class AdvancedExchange extends AbstractModel implements AdvancedExchangeInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Custom\AdvanceExchange\Model\ResourceModel\AdvancedExchange::class);
    }

    /**
     * @inheritDoc
     */
    public function getAdvancedExchangeId()
    {
        return $this->getData(self::ADVANCED_EXCHANGE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setAdvancedExchangeId($advancedExchangeId)
    {
        return $this->setData(self::ADVANCED_EXCHANGE_ID, $advancedExchangeId);
    }

    /**
     * @inheritDoc
     */
    public function getExchangetype()
    {
        return $this->getData(self::EXCHANGETYPE);
    }

    /**
     * @inheritDoc
     */
    public function setExchangetype($exchangetype)
    {
        return $this->setData(self::EXCHANGETYPE, $exchangetype);
    }

    /**
     * @inheritDoc
     */
    public function getMsgsku()
    {
        return $this->getData(self::MSGSKU);
    }

    /**
     * @inheritDoc
     */
    public function setMsgsku($msgsku)
    {
        return $this->setData(self::MSGSKU, $msgsku);
    }

    /**
     * @inheritDoc
     */
    public function getMsgdescription()
    {
        return $this->getData(self::MSGDESCRIPTION);
    }

    /**
     * @inheritDoc
     */
    public function setMsgdescription($msgdescription)
    {
        return $this->setData(self::MSGDESCRIPTION, $msgdescription);
    }

    /**
     * @inheritDoc
     */
    public function getImeienrolled()
    {
        return $this->getData(self::IMEIENROLLED);
    }

    /**
     * @inheritDoc
     */
    public function setImeienrolled($imeienrolled)
    {
        return $this->setData(self::IMEIENROLLED, $imeienrolled);
    }

    /**
     * @inheritDoc
     */
    public function getDeviceimei()
    {
        return $this->getData(self::DEVICEIMEI);
    }

    /**
     * @inheritDoc
     */
    public function setDeviceimei($deviceimei)
    {
        return $this->setData(self::DEVICEIMEI, $deviceimei);
    }

    /**
     * @inheritDoc
     */
    public function getSimofdevice()
    {
        return $this->getData(self::SIMOFDEVICE);
    }

    /**
     * @inheritDoc
     */
    public function setSimofdevice($simofdevice)
    {
        return $this->setData(self::SIMOFDEVICE, $simofdevice);
    }

    /**
     * @inheritDoc
     */
    public function getReturnshippinglabel()
    {
        return $this->getData(self::RETURNSHIPPINGLABEL);
    }

    /**
     * @inheritDoc
     */
    public function setReturnshippinglabel($returnshippinglabel)
    {
        return $this->setData(self::RETURNSHIPPINGLABEL, $returnshippinglabel);
    }

    /**
     * @inheritDoc
     */
    public function getSubmitterfirstname()
    {
        return $this->getData(self::SUBMITTERFIRSTNAME);
    }

    /**
     * @inheritDoc
     */
    public function setSubmitterfirstname($submitterfirstname)
    {
        return $this->setData(self::SUBMITTERFIRSTNAME, $submitterfirstname);
    }

    /**
     * @inheritDoc
     */
    public function getSubmitterlastname()
    {
        return $this->getData(self::SUBMITTERLASTNAME);
    }

    /**
     * @inheritDoc
     */
    public function setSubmitterlastname($submitterlastname)
    {
        return $this->setData(self::SUBMITTERLASTNAME, $submitterlastname);
    }

    /**
     * @inheritDoc
     */
    public function getSubmitteremail()
    {
        return $this->getData(self::SUBMITTEREMAIL);
    }

    /**
     * @inheritDoc
     */
    public function setSubmitteremail($submitteremail)
    {
        return $this->setData(self::SUBMITTEREMAIL, $submitteremail);
    }

    /**
     * @inheritDoc
     */
    public function getSubmitterphone()
    {
        return $this->getData(self::SUBMITTERPHONE);
    }

    /**
     * @inheritDoc
     */
    public function setSubmitterphone($submitterphone)
    {
        return $this->setData(self::SUBMITTERPHONE, $submitterphone);
    }

    /**
     * @inheritDoc
     */
    public function getCostcenter()
    {
        return $this->getData(self::COSTCENTER);
    }

    /**
     * @inheritDoc
     */
    public function setCostcenter($costcenter)
    {
        return $this->setData(self::COSTCENTER, $costcenter);
    }

    /**
     * @inheritDoc
     */
    public function getShiptoselect()
    {
        return $this->getData(self::SHIPTOSELECT);
    }

    /**
     * @inheritDoc
     */
    public function setShiptoselect($shiptoselect)
    {
        return $this->setData(self::SHIPTOSELECT, $shiptoselect);
    }

    /**
     * @inheritDoc
     */
    public function getShiptoaddresslabel()
    {
        return $this->getData(self::SHIPTOADDRESSLABEL);
    }

    /**
     * @inheritDoc
     */
    public function setShiptoaddresslabel($shiptoaddresslabel)
    {
        return $this->setData(self::SHIPTOADDRESSLABEL, $shiptoaddresslabel);
    }

    /**
     * @inheritDoc
     */
    public function getShiptoattention()
    {
        return $this->getData(self::SHIPTOATTENTION);
    }

    /**
     * @inheritDoc
     */
    public function setShiptoattention($shiptoattention)
    {
        return $this->setData(self::SHIPTOATTENTION, $shiptoattention);
    }

    /**
     * @inheritDoc
     */
    public function getShiptostreet()
    {
        return $this->getData(self::SHIPTOSTREET);
    }

    /**
     * @inheritDoc
     */
    public function setShiptostreet($shiptostreet)
    {
        return $this->setData(self::SHIPTOSTREET, $shiptostreet);
    }

    /**
     * @inheritDoc
     */
    public function getShiptostreet2()
    {
        return $this->getData(self::SHIPTOSTREET2);
    }

    /**
     * @inheritDoc
     */
    public function setShiptostreet2($shiptostreet2)
    {
        return $this->setData(self::SHIPTOSTREET2, $shiptostreet2);
    }

    /**
     * @inheritDoc
     */
    public function getShiptocity()
    {
        return $this->getData(self::SHIPTOCITY);
    }

    /**
     * @inheritDoc
     */
    public function setShiptocity($shiptocity)
    {
        return $this->setData(self::SHIPTOCITY, $shiptocity);
    }

    /**
     * @inheritDoc
     */
    public function getShiptostate()
    {
        return $this->getData(self::SHIPTOSTATE);
    }

    /**
     * @inheritDoc
     */
    public function setShiptostate($shiptostate)
    {
        return $this->setData(self::SHIPTOSTATE, $shiptostate);
    }

    /**
     * @inheritDoc
     */
    public function getShiptozip()
    {
        return $this->getData(self::SHIPTOZIP);
    }

    /**
     * @inheritDoc
     */
    public function setShiptozip($shiptozip)
    {
        return $this->setData(self::SHIPTOZIP, $shiptozip);
    }

    /**
     * @inheritDoc
     */
    public function getSaveaddress()
    {
        return $this->getData(self::SAVEADDRESS);
    }

    /**
     * @inheritDoc
     */
    public function setSaveaddress($saveaddress)
    {
        return $this->setData(self::SAVEADDRESS, $saveaddress);
    }

    /**
     * @inheritDoc
     */
    public function getShiplabelselect()
    {
        return $this->getData(self::SHIPLABELSELECT);
    }

    /**
     * @inheritDoc
     */
    public function setShiplabelselect($shiplabelselect)
    {
        return $this->setData(self::SHIPLABELSELECT, $shiplabelselect);
    }

    /**
     * @inheritDoc
     */
    public function getShiplabelattention()
    {
        return $this->getData(self::SHIPLABELATTENTION);
    }

    /**
     * @inheritDoc
     */
    public function setShiplabelattention($shiplabelattention)
    {
        return $this->setData(self::SHIPLABELATTENTION, $shiplabelattention);
    }

    /**
     * @inheritDoc
     */
    public function getShiplabelstreet()
    {
        return $this->getData(self::SHIPLABELSTREET);
    }

    /**
     * @inheritDoc
     */
    public function setShiplabelstreet($shiplabelstreet)
    {
        return $this->setData(self::SHIPLABELSTREET, $shiplabelstreet);
    }

    /**
     * @inheritDoc
     */
    public function getShiplabelstreet2()
    {
        return $this->getData(self::SHIPLABELSTREET2);
    }

    /**
     * @inheritDoc
     */
    public function setShiplabelstreet2($shiplabelstreet2)
    {
        return $this->setData(self::SHIPLABELSTREET2, $shiplabelstreet2);
    }

    /**
     * @inheritDoc
     */
    public function getShiplabelcity()
    {
        return $this->getData(self::SHIPLABELCITY);
    }

    /**
     * @inheritDoc
     */
    public function setShiplabelcity($shiplabelcity)
    {
        return $this->setData(self::SHIPLABELCITY, $shiplabelcity);
    }

    /**
     * @inheritDoc
     */
    public function getShiplabelstate()
    {
        return $this->getData(self::SHIPLABELSTATE);
    }

    /**
     * @inheritDoc
     */
    public function setShiplabelstate($shiplabelstate)
    {
        return $this->setData(self::SHIPLABELSTATE, $shiplabelstate);
    }

    /**
     * @inheritDoc
     */
    public function getShiplabelzip()
    {
        return $this->getData(self::SHIPLABELZIP);
    }

    /**
     * @inheritDoc
     */
    public function setShiplabelzip($shiplabelzip)
    {
        return $this->setData(self::SHIPLABELZIP, $shiplabelzip);
    }

    /**
     * @inheritDoc
     */
    public function getSameasship()
    {
        return $this->getData(self::SAMEASSHIP);
    }

    /**
     * @inheritDoc
     */
    public function setSameasship($sameasship)
    {
        return $this->setData(self::SAMEASSHIP, $sameasship);
    }

    /**
     * @inheritDoc
     */
    public function getShippingaccountno()
    {
        return $this->getData(self::SHIPPINGACCOUNTNO);
    }

    /**
     * @inheritDoc
     */
    public function setShippingaccountno($shippingaccountno)
    {
        return $this->setData(self::SHIPPINGACCOUNTNO, $shippingaccountno);
    }

    /**
     * @inheritDoc
     */
    public function getDamagereason()
    {
        return $this->getData(self::DAMAGEREASON);
    }

    /**
     * @inheritDoc
     */
    public function setDamagereason($damagereason)
    {
        return $this->setData(self::DAMAGEREASON, $damagereason);
    }

    /**
     * @inheritDoc
     */
    public function getAttachedfile()
    {
        return $this->getData(self::ATTACHEDFILE);
    }

    /**
     * @inheritDoc
     */
    public function setAttachedfile($attachedfile)
    {
        return $this->setData(self::ATTACHEDFILE, $attachedfile);
    }
}

