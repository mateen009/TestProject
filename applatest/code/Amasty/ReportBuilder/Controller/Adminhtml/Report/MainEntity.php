<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Controller\Adminhtml\Report;

use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\Report\EntitiesDataModifierInterface;
use Amasty\ReportBuilder\Model\Report\EntityProvider;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;

class MainEntity extends Action implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'Amasty_ReportBuilder::report_edit';
    const PARAM_NAME = 'entityName';

    /**
     * @var EntityProvider
     */
    private $entityProvider;

    /**
     * @var EntitiesDataModifierInterface
     */
    private $dataModifier;

    /**
     * @var Json
     */
    private $resultJson;

    /**
     * @var Provider
     */
    private $entitySchemeProvider;

    public function __construct(
        Context $context,
        EntityProvider $entityProvider,
        EntitiesDataModifierInterface $dataModifier,
        Provider $entitySchemeProvider
    ) {
        parent::__construct($context);
        $this->entityProvider = $entityProvider;
        $this->dataModifier = $dataModifier;
        $this->entitySchemeProvider = $entitySchemeProvider;
    }

    public function execute()
    {
        $entityName = $this->getRequest()->getParam(self::PARAM_NAME);
        $this->resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        if (!$entityName) {
            return $this->resultJson->setData(['error' => __('We can\'t find entity name.')]);
        }

        try {
            $scheme = $this->entitySchemeProvider->getEntityScheme();
            $simpleRelations = $scheme->getSimpleRelations($entityName);
            $entitiesData = $this->dataModifier->modify($this->entityProvider->getEntities([$entityName]));
            foreach ($entitiesData as &$entityData) {
                $entityData[EntityInterface::USE_AGGREGATION] = !in_array(
                    $entityData[EntityInterface::NAME],
                    $simpleRelations
                );
            }
        } catch (\Exception $e) {
            return $this->resultJson->setData(['error' => $e->getMessage()]);
        }

        return $this->resultJson->setData(array_values($entitiesData));
    }
}
