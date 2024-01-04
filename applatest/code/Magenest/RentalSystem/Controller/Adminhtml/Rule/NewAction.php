<?php
namespace Magenest\RentalSystem\Controller\Adminhtml\Rule;

use Magento\Framework\App\Action\HttpGetActionInterface;

class NewAction extends AbstractRule implements HttpGetActionInterface
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
