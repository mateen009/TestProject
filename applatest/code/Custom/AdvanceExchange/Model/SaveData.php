<?php
namespace Custom\AdvanceExchange\Model;

use Magento\Framework\App\ResourceConnection;
use Custom\AdvanceExchange\Model\AdvancedExchange;

class SaveData
{
    const TABLE_ADVANCED_EXCHANGE = "advanced_exchange";

    /**
     * @var ResourceConnection
     */
    private $resource;
    private $advanceExchange;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource,
        AdvancedExchange $advancedExchange
    ) {
        $this->resource = $resource;
        $this->advanceExchange = $advancedExchange;
    }

    public function execute($data)
    {

      $connection = $this->resource->getConnection();

      $shiptoselect = $data['shiptoselect'];

      $exchangetype = $data['exchangetype'];
      $msgsku = $data['msgsku'];
      $msgdescription = $data['msgdescription'];
      $imeienrolled = isset($data['imeienrolled']) ?: '0';
      $deviceimei = $data['deviceimei'];
      $simofdevice = $data['simofdevice'];
      $returnshippinglabel = isset($data['returnshippinglabel']) ?: '0';
      $submitterfirstname = $data['submitterfirstname'];
      $submitterlastname = $data['submitterlastname'];
      $submitteremail = $data['submitteremail'];
      $submitterphone = $data['submitterphone'];
      $costcenter = $data['costcenter'];
      $shiptoselect = $data['shiptoselect'];
      if($shiptoselect == 'new') {
        $shiptoaddresslabel = $data['shiptoaddresslabel'];
        $shiptoattention = $data['shiptoattention'];
        $shiptostreet = $data['shiptostreet'];
        $shiptostreet2 = $data['shiptostreet2'];
        $shiptocity = $data['shiptocity'];
        $shiptostate = $data['shiptostate'];
        $shiptozip = $data['shiptozip'];
        $saveaddress = isset($data['saveaddress']) ?: '0';
      } else {
        $shiptoaddresslabel = $data['shiptoaddresslabeld'];
        $shiptoattention = $data['shiptoattentiond'];
        $shiptostreet = $data['shiptostreetd'];
        $shiptostreet2 = $data['shiptostreet2d'];
        $shiptocity = $data['shiptocityd'];
        $shiptostate = $data['shiptostated'];
        $shiptozip = $data['shiptozipd'];
        $saveaddress = isset($data['saveaddressd']) ?: '0';
      }

      // $shiplabelselect = $data['shiplabelselect'];
      // $shiplabelattention = $data['shiplabelattention'];
      // $shiplabelstreet = $data['shiplabelstreet'];
      // $shiplabelstreet2 = $data['shiplabelstreet2'];
      // $shiplabelcity = $data['shiplabelcity'];
      // $shiplabelstate = $data['shiplabelstate'];
      // $shiplabelzip = $data['shiplabelzip'];
      // $sameasship = isset($data['sameasship']) ?: '0';
      $shiplabelselect = '';
      $shiplabelattention = '';
      $shiplabelstreet = '';
      $shiplabelstreet2 = '';
      $shiplabelcity = '';
      $shiplabelstate = '';
      $shiplabelzip = '';
      $sameasship = '';
      
      $shippingaccountno = $data['shippingaccountno'];
      $cid = $data['cid'];
      $internalId = $data['internalid'];
      $addedby = 'frontend';
     // $damagereason = isset($data['damagereason']) ?: '0';
      $filePath = $data['filePath'];

      $_damagereason = 0;
      if(isset($data['damagereason'])) {
        $_damagereason = implode(",", $data['damagereason']);
      }
      
      $table = $connection->getTableName('custom_advanceexchange_advanced_exchange');
      $model = $this->advanceExchange;
      $model->setExchangetype($exchangetype);
      $model->setMsgsku($msgsku);
      $model->setMsgdescription($msgdescription);
      $model->setImeienrolled($imeienrolled);
      $model->setDeviceimei($deviceimei);
      $model->setSimofdevice($simofdevice);
      $model->setReturnshippinglabel($returnshippinglabel);
      $model->setSubmitterfirstname($submitterfirstname);
      $model->setSubmitterlastname($submitterlastname);
      $model->setSubmitteremail($submitteremail);
      $model->setSubmitterphone($submitterphone);
      $model->setCostcenter($costcenter);
      $model->setShiptoselect($shiptoselect);
      $model->setShiptoaddresslabel($shiptoaddresslabel);
      $model->setShiptoattention($shiptoattention);
      $model->setShiptostreet($shiptostreet);
      $model->setShiptostreet2($shiptostreet2);
      $model->setShiptocity($shiptocity);
      $model->setShiptostate($shiptostate);
      $model->setShiptozip($shiptozip);
      $model->setSaveaddress($saveaddress);
      $model->setShiplabelselect($shiplabelselect);
      $model->setShiplabelattention($shiplabelattention);
      $model->setShiplabelstreet($shiplabelstreet);
      $model->setShiplabelstreet2($shiplabelstreet2);
      $model->setShiplabelcity($shiplabelcity);
      $model->setShiplabelstate($shiplabelstate);
      $model->setShiplabelzip($shiplabelzip);
      $model->setSameasship('0');
      $model->setShippingaccountno($shippingaccountno);
      $model->setDamagereason($_damagereason);
      $model->settAttachedfile($filePath);
      $model->setCid($cid);
      $model->setInternalid($internalId);
      $model->setAddedby($addedby);
      $model->setCreateddate(date('Y-m-d'));
      $model->save();

      // $query = "INSERT INTO `" . $table . "`(`exchangetype`, `msgsku`, `msgdescription`, `imeienrolled`, `deviceimei`, `simofdevice`, `returnshippinglabel`, `submitterfirstname`,
      //  `submitterlastname`, `submitteremail`, `submitterphone`, `costcenter`, `shiptoselect`, `shiptoaddresslabel`, `shiptoattention`, `shiptostreet`, `shiptostreet2`, 
      //  `shiptocity`, `shiptostate`, `shiptozip`, `saveaddress`, `shiplabelselect`, `shiplabelattention`, `shiplabelstreet`, `shiplabelstreet2`, `shiplabelcity`, 
      //  `shiplabelstate`, `shiplabelzip`, `sameasship`, `shippingaccountno`, `damagereason`, `attachedfile`, `cid`, `internalid`, `addedby`) 
      //  VALUES ('$exchangetype', '$msgsku', '$msgdescription', $imeienrolled, '$deviceimei', '$simofdevice', $returnshippinglabel, '$submitterfirstname',
      //  '$submitterlastname', '$submitteremail', '$submitterphone', '$costcenter', '$shiptoselect', '$shiptoaddresslabel', '$shiptoattention', '$shiptostreet', '$shiptostreet2',
      //  '$shiptocity', '$shiptostate', '$shiptozip', $saveaddress, '$shiplabelselect', '$shiplabelattention', '$shiplabelstreet', '$shiplabelstreet2', '$shiplabelcity',
      //  '$shiplabelstate', '$shiplabelzip', 0, '$shippingaccountno', '$_damagereason', '$filePath', $cid, $internalId, '$addedby')";
      // $connection->query($query);

      return $model->getId();

    }

  }