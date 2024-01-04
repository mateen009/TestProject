<?php
namespace Magenest\RentalSystem\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class Holiday extends AbstractFieldArray
{
    protected function _prepareToRender()
    {
        $this->addColumn(
            'date',
            [
                'label' => __('Date (format YYYY/MM/DD)'),
                'class' => 'js-date-excluded-datepicker required-entry'
            ]
        );

        $this->_addAfter       = false;
        $this->_addButtonLabel = __('Add Date');
    }

    /**
     * Convert backend date format
     *
     * @param DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $key = 'date';
        if (!isset($row[$key])) {
            return;
        }
        $rowId = $row['_id'];
        $cellElemId = $this->_getCellInputElementId($rowId, $key);
        try {
            $sourceDate                = date_create_from_format('Y/m/d', $row[$key]);
            $renderedDate              = $sourceDate->format('Y/m/d');
            $row[$key]                 = $renderedDate;
            $columnValues              = $row['column_values'];
            $columnValues[$cellElemId] = $renderedDate;
            $row['column_values']      = $columnValues;
        } catch (\Exception $e) {
            $this->_logger->debug('Date picker error ' . $e->getMessage());
        }
    }

    /**
     * Get the grid and scripts contents
     *
     * @param AbstractElement $element
     *
     * @return string
     * @throws LocalizedException
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = parent::_getElementHtml($element);
        $js = $this->getLayout()->createBlock(Template::class)
            ->setTemplate('Magenest_RentalSystem::system/config/holidays-dynamic-rows.phtml')
            ->toHtml();

        return $html . $js;
    }
}
