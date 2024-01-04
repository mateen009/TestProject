<?php
namespace Magenest\RentalSystem\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template;

class ColorPickerJs extends Template
{
    /**
     * Set Template
     * @var string
     */
    protected $_template = 'Magenest_RentalSystem::system/config/color-picker.phtml';

    /** @var string|null */
    private $_fieldId = null;

    /** @var string|null */
    private $_fieldValue = null;

    /**
     * @param $id
     * @return $this
     */
    public function setFieldId($id)
    {
        $this->_fieldId = $id;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setFieldValue($value)
    {
        $this->_fieldValue = $value;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFieldId()
    {
        return $this->_fieldId;
    }

    /**
     * @return string|null
     */
    public function getFieldValue()
    {
        return $this->_fieldValue;
    }
}
