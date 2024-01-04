<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Controller\Adminhtml\Order;

use Magenest\RentalSystem\Controller\Adminhtml\Order as OrderController;
use Magenest\RentalSystem\Model\RentalOrder;
use Magenest\RentalSystem\Model\Status;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class MassStatus extends OrderController
{
    const MAPPING = [
        Status::DELIVERING => 'delivering',
        Status::DELIVERED => 'delivered',
        Status::RETURNING => 'returning',
        Status::COMPLETE => 'complete'

    ];

    /**
     * @return Redirect|ResponseInterface|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $status = (int)$this->getRequest()->getParam('status');
        if (isset(self::MAPPING[$status])) {
            $collection = $this->_filter->getCollection($this->_collectionFactory->create());
            try {
                $collection->setDataToAll('status', $status)->save();
                $this->messageManager->addSuccessMessage(__(
                    'A total of %1 rental(s) have been set as %2.',
                    $collection->getSize(),
                    self::MAPPING[$status]
                ));
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
    }
}
