<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Controller\Adminhtml\Report;

use Amasty\ReportBuilder\Model\Report\EntityProvider;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

class Relations extends Action implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'Amasty_ReportBuilder::report_edit';
    const PARAM_NAME = 'entityNames';

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var EntityProvider
     */
    private $entityProvider;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        EntityProvider $entityProvider
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->entityProvider = $entityProvider;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|Json
     */
    public function execute()
    {
        $entityNames = $this->getRequest()->getParam(self::PARAM_NAME);
        if (!$entityNames) {
            return $this->getErrorJson(__('We can\'t find entity names.')->render());
        }
        if (!is_array($entityNames)) {
            $entityNames = explode(',', $entityNames);
        }
        try {
            $entities = $this->entityProvider->getEntities($entityNames);
        } catch (\Exception $e) {
            return $this->getErrorJson($e->getMessage());
        }

        return $this->jsonFactory->create()->setData($entities);
    }

    private function getErrorJson(string $message): Json
    {
        $jsonFactory = $this->jsonFactory->create();
        $jsonFactory->setData(['error' => $message]);

        return $jsonFactory;
    }
}
