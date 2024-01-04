<?php

namespace Cminds\Oapm\Plugin\Customer\Group\Model;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Customer\Model\GroupRegistry;

/**
 * Class Form
 *
 * @package Cminds\Oapm\Plugin\Customer\Group\Model
 */
class Form
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var GroupRegistry
     */
    private $groupRegistry;

    public function __construct(
        Registry $registry,
        GroupRegistry $groupRegistry
    ) {
        $this->coreRegistry = $registry;
        $this->groupRegistry = $groupRegistry;
    }

    /**
     * @param \Magento\Customer\Block\Adminhtml\Group\Edit\Form $form
     * @return \Magento\Customer\Block\Adminhtml\Group\Edit\Form|\Magento\Framework\Data\Form
     */
    public function afterSetForm(\Magento\Customer\Block\Adminhtml\Group\Edit\Form $form)
    {
        $form = $form->getForm();

        $fieldset = $form->getElement('base_fieldset');
        $email = '';
        $groupId = $this->coreRegistry->registry(RegistryConstants::CURRENT_GROUP_ID);
        /** @var \Magento\Customer\Api\Data\GroupInterface $customerGroup */
        if ($groupId !== null) {
            try {
                $customerGroup = $this->groupRegistry->retrieve($groupId);
            } catch (LocalizedException $exception) {
                return $form;
            }

            $email = $customerGroup->getData('group_manager_email');
        }

        $fieldset->addField('group_manager_email',
            'text',
            [
                'name' => 'group_manager_email',
                'label' => __('Group Manager Email'),
                'title' => __('Group Manager Email'),
                'required' => false,
                'value' => $email
            ]);

        return $form;
    }
}
