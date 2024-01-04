<?php
namespace Custom\AdvanceExchange\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;

class MsgSku implements ArrayInterface
{
  /**
     * @var ResourceConnection
     */
    private $resource;

    private $row;

    protected $request;

    public function __construct(
      ResourceConnection $resource,
      DataObject $row,
      \Magento\Framework\App\Request\Http $request
  ) {
      $this->resource = $resource;
      $this->row = $row;
      $this->request = $request;
  }

    public function toOptionArray()
    {
        $result = [];
        foreach ($this->getOptions() as $data) {
            $result[] = [
                 'value' => $data['sku'],
                 'label' => $data['sku'],
             ];
        }

        return $result;
    }

    public function getOptions()
    {
      $rowId = $this->request->getParam('advanced_exchange_id');
      if($rowId) {
        $customerId = $this->getCustomerId($rowId);
      } else {
        $customerId = 0;
      }
     // echo $this->getData('msgsku');die();
      $skus = array();

      $connection = $this->resource->getConnection();
      $table = $connection->getTableName('eligible_sku');
      
     // $query = "Select sku FROM {$table}  WHERE customer_id = {$customerId}";
      $query = "SELECT `main_table`.entity_id, `order_item`.sku FROM `sales_order` AS `main_table` 
                INNER JOIN `sales_order_item` AS `order_item` ON main_table.entity_id = order_item.order_id
                INNER JOIN `eligible_sku` AS `es` ON order_item.sku = es.sku 
                WHERE `main_table`.`customer_id` = {$customerId} GROUP BY `order_item`.sku";
      $result = $connection->fetchAll($query);
     // echo "<pre>";print_r($result);die();
      return $result;
    }

    public function getCustomerId($rowId) {
      $connection = $this->resource->getConnection();
      $table = $connection->getTableName('custom_advanceexchange_advanced_exchange');
      
     // $query = "Select sku FROM {$table}  WHERE customer_id = {$customerId}";
      $query = "SELECT cid FROM {$table} WHERE advanced_exchange_id = {$rowId}";
      $cid = $connection->fetchOne($query);
      if(!$cid) {
        $cid = 0;
      }
      return $cid;
    }
}