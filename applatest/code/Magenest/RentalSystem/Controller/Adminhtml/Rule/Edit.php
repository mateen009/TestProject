<?php
namespace Magenest\RentalSystem\Controller\Adminhtml\Rule;

use Magento\Framework\App\Action\HttpGetActionInterface;

class Edit extends AbstractRule implements HttpGetActionInterface
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->rentalRule->create();

        if ($id) {
            $this->rentalRuleResource->load($model, $id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
                $this->_redirect('rule/*');
                return;
            }
        }

        // set entered data if was error when we do save
        $pageData = $this->_session->getPageData(true);
        if (!empty($pageData)) {
            $model->addData($pageData);
        }
        $model->getConditions()->setFormName('rentalsystem_rule_form');
        $model->getConditions()->setJsFormObject(
            $model->getConditionsFieldSetId($model->getConditions()->getFormName())
        );
        $this->_coreRegistry->register('rentalsystem_rule_current', $model);

        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Rental Rule'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getRuleId() ? $model->getName() : __('Rental Rule')
        );
        $breadcrumb = $id ? __('Edit Rental Rule') : __('New Rental Rule');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb);

        $this->_view->renderLayout();
    }
}
