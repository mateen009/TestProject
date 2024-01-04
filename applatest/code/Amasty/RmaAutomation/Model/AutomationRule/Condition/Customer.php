<?php

namespace Amasty\RmaAutomation\Model\AutomationRule\Condition;

use Magento\Rule\Model\Condition as Condition;
use Magento\Rule\Model\Condition\AbstractCondition;

/**
 * @method string getAttribute() customer attribute code
 */
class Customer extends AbstractCondition
{
    const INPUT_TYPES = [
        'string',
        'numeric',
        'date',
        'select',
        'multiselect',
        'grid',
        'boolean'
    ];

    const ELEMENT_TYPES = [
        'checkbox',
        'checkboxes',
        'date',
        'editablemultiselect',
        'editor',
        'fieldset',
        'file',
        'gallery',
        'image',
        'imagefile',
        'multiline',
        'multiselect',
        'radio',
        'radios',
        'select',
        'text',
        'textarea',
        'time'
    ];

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    private $resource;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $yesnoOptions;

    public function __construct(
        Condition\Context $context,
        \Magento\Customer\Model\ResourceModel\Customer $resource,
        \Magento\Config\Model\Config\Source\Yesno $yesnoOptions,
        array $data = []
    ) {
        $this->yesnoOptions = $yesnoOptions;
        $this->resource = $resource;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve attribute object
     *
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    public function getAttributeObject()
    {
        return $this->resource->getAttribute($this->getAttribute());
    }

    /**
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $customerAttributes = $this->resource
            ->loadAllAttributes()
            ->getAttributesByCode();
        $attributes = [];

        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
        foreach ($customerAttributes as $attribute) {
            if (!($attribute->getFrontendLabel()) || !($attribute->getAttributeCode())) {
                continue;
            }

            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }
        $this->_addSpecialAttributes($attributes);
        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Add special attributes
     *
     * @param array &$attributes
     * @return void
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        $attributes['entity_id'] = __('Customer ID');
    }

    /**
     * @return AbstractCondition
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();

        if ($element->getValue() == 'lock_expires') {
            $element->setValueName('Lock Expire');
        }
        $element->setShowAsText(true);

        return $element;
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        if ($this->getAttribute() === 'entity_id') {
            return 'grid';
        }
        $customerAttribute = $this->getAttributeObject();

        if (!$customerAttribute) {
            return parent::getInputType();
        }

        return $this->getInputTypeFromAttribute($customerAttribute);
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $customerAttribute
     *
     * @return string
     */
    protected function getInputTypeFromAttribute($customerAttribute)
    {
        if (!is_object($customerAttribute)) {
            $customerAttribute = $this->getAttributeObject();
        }

        if (in_array($customerAttribute->getFrontendInput(), self::INPUT_TYPES)) {
            return $customerAttribute->getFrontendInput();
        }

        switch ($customerAttribute->getFrontendInput()) {
            case 'gallery':
            case 'media_image':
            case 'selectimg': // amasty customer attribute
                return 'select';
            case 'multiselectimg': // amasty customer attribute
                return 'multiselect';
        }

        return 'string';
    }

    /**
     * Value element type will define renderer for condition value element
     *
     * @see \Magento\Framework\Data\Form\Element
     * @return string
     */
    public function getValueElementType()
    {
        $customerAttribute = $this->getAttributeObject();

        if ($this->getAttribute() === 'entity_id') {
            return 'text';
        }

        if (!is_object($customerAttribute)) {
            return parent::getValueElementType();
        }

        if (in_array($customerAttribute->getFrontendInput(), self::ELEMENT_TYPES)) {
            return $customerAttribute->getFrontendInput();
        }

        switch ($customerAttribute->getFrontendInput()) {
            case 'selectimg':
            case 'boolean':
                return 'select';
            case 'multiselectimg':
                return 'multiselect';
        }

        return parent::getValueElementType();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getValueSelectOptions()
    {
        $selectOptions = [];
        $attributeObject = $this->getAttributeObject();

        if (is_object($attributeObject) && $attributeObject->usesSource()) {
            $addEmptyOption = true;

            if ($attributeObject->getFrontendInput() == 'multiselect') {
                $addEmptyOption = false;
            }
            $selectOptions = $attributeObject->getSource()->getAllOptions($addEmptyOption);
        }

        if ($this->getInputType() == 'boolean' && count($selectOptions) == 0) {
            $selectOptions = $this->yesnoOptions->toOptionArray();
        }
        $key = 'value_select_options';

        if (!$this->hasData($key)) {
            $this->setData($key, $selectOptions);
        }

        return $this->getData($key);
    }

    /**
     * Collect validated attributes
     *
     * @param \Magento\Customer\Model\ResourceModel\Customer\Collection $productCollection
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function collectValidatedAttributes($productCollection)
    {
        $attribute = $this->getAttribute();
        $productCollection->addAttributeToSelect($attribute, 'left');
        $attributes = $this->getRule()->getCollectedAttributes();
        $attributes[$attribute] = true;
        $this->getRule()->setCollectedAttributes($attributes);

        return $this;
    }
}
