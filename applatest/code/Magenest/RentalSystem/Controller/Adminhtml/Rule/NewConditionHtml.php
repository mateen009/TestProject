<?php
namespace Magenest\RentalSystem\Controller\Adminhtml\Rule;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Rule\Model\Condition\AbstractCondition;

class NewConditionHtml extends AbstractRule implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $formName = $this->getRequest()->getParam('form_namespace');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        switch ($type) {
            case \Magento\CatalogRule\Model\Rule\Condition\Product::class:
                $model = $this->categoryCondition->create();
                break;
            case \Magenest\RentalSystem\Model\RentalRule\Condition\Product::class:
                $model = $this->productCondition->create();
                break;
            default:
                $model = $this->combineCondition->create();
        }
        $model->setId($id)
            ->setType($type)
            ->setRule($this->rentalRule->create())
            ->setPrefix('conditions');

        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $model->setFormName($formName);
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
}
