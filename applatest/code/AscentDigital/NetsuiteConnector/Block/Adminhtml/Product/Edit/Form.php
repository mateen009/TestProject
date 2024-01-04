<?php


namespace AscentDigital\NetsuiteConnector\Block\Adminhtml\Product\Edit;
use Magento\Backend\Block\Widget\Form\Generic;

class Form extends Generic
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    )
    {
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
//        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $model = $this->_coreRegistry->registry('row_data');
        $form = $this->_formFactory->create(
            ['data' => [
                'id' => 'edit_form',
                'enctype' => 'multipart/form-data',
                'action' => $this->getData('action'),
                'method' => 'post'
            ]
            ]
        );

        $form->setHtmlIdPrefix('netsuiteProduct_');
        if (0) {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Edit Row Data'), 'class' => 'fieldset-wide']
            );
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        } else {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('NetSuite Products'), 'class' => 'fieldset-wide']
            );
        }

        $fieldset->addField(
            'product_sku',
            'text',
            [
                'name' => 'product_sku',
                'label' => __('Product SKU'),
                'id' => 'product_sku',
                'title' => __('Product SKU'),
                'required' => false,
            ]
        );


        // $wysiwygConfig = $this->_wysiwygConfig->getConfig(['tab_id' => $this->getTabId()]);

        // $fieldset->addField(
        //     'phone_number',
        //     'editor',
        //     [
        //         'name' => 'phone_number',
        //         'label' => __('Phone Number'),
        //         'style' => 'height:36em;',
        //         'required' => true,
        //         'config' => $wysiwygConfig
        //     ]
        // );

//        $fieldset->addField(
//            'publish_date',
//            'date',
//            [
//                'name' => 'publish_date',
//                'label' => __('Publish Date'),
////                'date_format' => $dateFormat,
////                'time_format' => 'HH:mm:ss',
//                'class' => 'validate-date validate-date-range date-range-custom_theme-from',
//                'class' => 'required-entry',
//                'style' => 'width:200px',
//            ]
//        );
//        $fieldset->addField(
//            'is_active',
//            'select',
//            [
//                'name' => 'is_active',
//                'label' => __('Status'),
//                'id' => 'is_active',
//                'title' => __('Status'),
//                'values' => $this->_options->getOptionArray(),
//                'class' => 'status',
//                'required' => true,
//            ]
//        );
        // $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
