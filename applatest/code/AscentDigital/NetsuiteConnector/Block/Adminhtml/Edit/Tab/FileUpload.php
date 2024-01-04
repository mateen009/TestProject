<?php
namespace AscentDigital\NetsuiteConnector\Block\Adminhtml\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;

/**
 * Customer account form block
 */
class FileUpload extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Information Files');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Information Files');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        if ($this->getCustomerId()) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        if ($this->getCustomerId()) {
            return false;
        }
        return true;
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    public function initForm()
    {
        // if (!$this->canShowTab()) {
        //     return $this;
        // }
        /**@var \Magento\Framework\Data\Form $form */
        // $form = $this->_formFactory->create(
        //     ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post', 'enctype' => 'multipart/form-data']]
        // );

        // $fieldset = $form->addFieldset(
        //     'base_fieldset',
        //     ['legend' => __('File Upload'), 'class' => 'fieldset-wide']
        // );

        // $fieldset->addField(
        //     'file_upload',
        //     'file',
        //     [
        //         'name' => 'file_upload',
        //         'label' => __('Upload File'),
        //         'title' => __('Upload File'),
        //         'required' => true
        //     ]
        // );
        // $form = $this->_formFactory->create();
        // $form->setHtmlIdPrefix('myform_');
        
        // $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Upload File')]);
        // $rowcom = "test";
        // $fieldset->addField(
        //     'file',
        //     'text',
        //     [
        //         'name' => 'file',
        //         'data-form-part' => $this->getData('target_form'),
        //         'label' => __('Choose File'),
        //         'title' => __('Choose File'),
        //         'value' => '',
        //     ]
        // );
        // $this->setForm($form);
        return $this;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->canShowTab()) {
            $this->initForm();
            return parent::_toHtml();
        } else {
            return '';
        }
    }

    /**
     * Prepare the layout.
     *
     * @return $this
     */
    public function getFormHtml()
    {
        return $this->getLayout()->createBlock(
            'AscentDigital\NetsuiteConnector\Block\Adminhtml\Edit\Tab\FileTable\FileTable'
        )->setCustomerId($this->getCustomerId())->toHtml();
        // return $html;
    }
}