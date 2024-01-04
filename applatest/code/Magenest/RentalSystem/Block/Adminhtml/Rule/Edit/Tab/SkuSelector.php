<?php
namespace Magenest\RentalSystem\Block\Adminhtml\Rule\Edit\Tab;

use Magenest\RentalSystem\Model\Rental;
use Magento\CatalogRule\Block\Adminhtml\Promo\Widget\Chooser\Sku;

class SkuSelector extends Sku
{
    /**
     * Prepare Catalog Product Collection for attribute SKU in Promo Conditions SKU chooser
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_getCpCollectionInstance()
            ->setStoreId(0)
            ->addAttributeToSelect('name', 'type_id', 'attribute_set_id')
            ->addFieldToFilter('type_id', Rental::PRODUCT_TYPE);

        $this->setCollection($collection);

        return $this;
    }
}
