<?php
namespace AscentDigital\Reports\Ui\DataProvider\Item\Listing;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{

      protected function _initSelect()
      {
          $this->addFilterToMap('entity_id', 'main_table.product_id');
        //   $this->addFilterToMap('name', 'devgridname.value');
          parent::_initSelect();
      }
}