<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\RentalSystem\Controller\Adminhtml\Product;

use Magenest\RentalSystem\Controller\Adminhtml\Product as ProductController;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class MassDelete extends ProductController
{
    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $delete = 0;
        try {
            $collection = $this->_filter->getCollection($this->_collectionFactory->create());
            foreach ($collection as $item) {
                $this->productRepository->deleteById($item->getProductId());
                $delete++;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->messageManager->addExceptionMessage($e);
        }

        $this->messageManager->addNoticeMessage(__('A total of %1 product(s) have been deleted.', $delete));
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
    }
}
