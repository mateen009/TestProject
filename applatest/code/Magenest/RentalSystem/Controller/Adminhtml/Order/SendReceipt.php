<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Controller\Adminhtml\Order;

use Magenest\RentalSystem\Controller\Adminhtml\Order;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class SendReceipt extends Order
{
    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        try {
            $id = $this->getRequest()->getParam('id');
            $model  = $this->_rentalOrderFactory->create();
            $this->rentalOrderResource->load($model, $id);
            if (!$model->getId()) {
                throw new LocalizedException(__("ID not found."));
            }

            $this->_rentalHelper->sendReceipt($model->getData());

            $this->messageManager->addSuccessMessage(__('Resent receipt for rental order ID: %1', $id));
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->messageManager->addExceptionMessage($e);
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
    }
}
