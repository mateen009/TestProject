<?php
namespace Magenest\RentalSystem\Controller\Adminhtml\Rule;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Index extends AbstractRule implements HttpGetActionInterface
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Rental Price Rules'));
        $this->_view->renderLayout();
    }
}
