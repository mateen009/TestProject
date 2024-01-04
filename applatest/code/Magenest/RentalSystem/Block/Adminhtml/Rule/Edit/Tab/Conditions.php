<?php
namespace Magenest\RentalSystem\Block\Adminhtml\Rule\Edit\Tab;

use Magento\CatalogRule\Block\Adminhtml\Promo\Catalog\Edit\Tab\Conditions as CatalogRule;
use Magento\Rule\Model\Condition\AbstractCondition;

class Conditions extends CatalogRule
{
    /**
     * @return $this|\Magento\Backend\Block\Widget\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('rentalsystem_rule_current');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->addTabToForm($model, 'conditions_fieldset', 'rentalsystem_rule_form');
        $form->getElement('conditions_fieldset')
            ->setData([
                'legend' => __('Conditions (don\'t add conditions if rule is applied to all Rental products)')
            ]);
        $this->setForm($form);

        return $this;
    }

    /**
     * @param \Magento\CatalogRule\Api\Data\RuleInterface $model
     * @param string $fieldsetId
     * @param string $formName
     * @return \Magento\Framework\Data\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addTabToForm($model, $fieldsetId = 'conditions_fieldset', $formName = 'rentalsystem_rule_form')
    {
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $conditionsFieldSetId = $model->getConditionsFieldSetId($formName);

        $newChildUrl = $this->getUrl(
            'rentalsystem/rule/newConditionHtml/form/' . $conditionsFieldSetId,
            ['form_namespace' => $formName]
        );

        $renderer = $this->_rendererFieldset->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($conditionsFieldSetId);

        $fieldset = $form->addFieldset(
            $fieldsetId,
            ['legend' => __('Conditions (don\'t add conditions if rule is applied to all products)')]
        )->setRenderer($renderer);

        $fieldset->addField(
            'conditions',
            'text',
            [
                'name' => 'conditions',
                'label' => __('Conditions'),
                'title' => __('Conditions'),
                'required' => true,
                'data-form-part' => $formName
            ]
        )
            ->setRule($model)
            ->setRenderer($this->_conditions);

        $form->setValues($model->getData());
        $this->setConditionFormName($model->getConditions(), $formName, $conditionsFieldSetId);
        return $form;
    }

    /**
     * @param AbstractCondition $conditions
     * @param $formName
     * @param $jsFormName
     * @return void
     */
    private function setConditionFormName(AbstractCondition $conditions, $formName, $jsFormName)
    {
        $conditions->setFormName($formName);
        $conditions->setJsFormObject($jsFormName);

        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName, $jsFormName);
            }
        }
    }
}
