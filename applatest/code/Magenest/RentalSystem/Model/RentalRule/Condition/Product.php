<?php
namespace Magenest\RentalSystem\Model\RentalRule\Condition;

use Magento\CatalogRule\Model\Rule\Condition\Product as CatalogRuleProduct;

class Product extends CatalogRuleProduct
{
    /**
     * @return string
     */
    public function getValueElementChooserUrl()
    {
        $url = false;
        switch ($this->getAttribute()) {
            case 'sku':
            case 'category_ids':
                $url = 'rentalsystem/rule_widget/chooser/attribute/' . $this->getAttribute();
                if ($this->getJsFormObject()) {
                    $url .= '/form/' . $this->getJsFormObject();
                }
                break;
            default:
                break;
        }
        return $url !== false ? $this->_backendData->getUrl($url) : '';
    }
}
