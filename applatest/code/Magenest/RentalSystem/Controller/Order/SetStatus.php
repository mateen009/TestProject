<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\RentalSystem\Controller\Order;

use Magenest\RentalSystem\Model\ResourceModel\RentalOrder;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;
use Magenest\RentalSystem\Model\RentalOrderFactory;
use Magento\Framework\Webapi\Exception;
use Psr\Log\LoggerInterface;

class SetStatus extends Action
{
    /** @var PageFactory */
    protected $resultPageFactory;

    /** @var RentalOrderFactory */
    protected $rentalOrderFactory;

    /** @var RentalOrder */
    protected $rentalOrderResource;

    /** @var LoggerInterface */
    private $logger;

    /**
     * SetStatus constructor.
     * @param RentalOrderFactory $rentalOrderFactory
     * @param RentalOrder $rentalOrderResource
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        RentalOrderFactory $rentalOrderFactory,
        RentalOrder $rentalOrderResource,
        Context $context,
        PageFactory $resultPageFactory,
        LoggerInterface $logger
    ) {
        $this->rentalOrderFactory = $rentalOrderFactory;
        $this->rentalOrderResource = $rentalOrderResource;
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * View my rental orders
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            if (!$this->getRequest()->isAjax()) {
                throw new LocalizedException(__("Wrong request type."));
            }

            $params = $this->getRequest()->getParams();
            if (!isset($params['order_item_id']) || !isset($params['status'])) {
                throw new LocalizedException(__("Missing required parameters."));
            }

            $model = $this->rentalOrderFactory->create()->loadByOrderItemId($params['order_item_id']);
            $model->setStatus($params['status']);
            $this->rentalOrderResource->save($model);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $resultJson->setHttpResponseCode(Exception::HTTP_BAD_REQUEST);
        }

        return $resultJson;
    }
}
