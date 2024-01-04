<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Controller\Adminhtml\Order;

use Magenest\RentalSystem\Controller\Adminhtml\Order;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class SetStatus extends Order
{
    /**
     * @return Redirect|ResponseInterface|ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $id     = $params['id'];
        $status = $params['status'];

        try {
            if (!isset($id) || !isset($status)) {
                throw new LocalizedException(__("Request is missing required parameters."));
            }

            $model = $this->_rentalOrderFactory->create();
            $this->rentalOrderResource->load($model, $id);
            if (!$model->getId()) {
                throw new LocalizedException(__("Rental Order ID %1 is no longer exists.", $id));
            }

            $model->setStatus($status);
            $this->rentalOrderResource->save($model);

            $this->messageManager->addSuccessMessage(__('Saved status for rental with ID %1', $id));
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->messageManager->addExceptionMessage($e);
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
    }
}
