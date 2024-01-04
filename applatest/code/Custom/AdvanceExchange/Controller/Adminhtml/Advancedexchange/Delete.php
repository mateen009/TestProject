<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Custom\AdvanceExchange\Controller\Adminhtml\Advancedexchange;

class Delete extends \Custom\AdvanceExchange\Controller\Adminhtml\Advancedexchange
{

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('advanced_exchange_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create(\Custom\AdvanceExchange\Model\AdvancedExchange::class);
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('Deleted'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['advanced_exchange_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a record to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}

