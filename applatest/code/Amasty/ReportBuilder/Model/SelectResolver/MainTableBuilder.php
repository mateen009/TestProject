<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver;

use Amasty\ReportBuilder\Api\EntityScheme\ProviderInterface;
use Amasty\ReportBuilder\Model\ReportResolver;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\Select;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data\SelectFactory;
use Amasty\ReportBuilder\Model\ResourceModel\Report\Data as DataResource;
use Amasty\ReportBuilder\Model\SelectResolver\MainTableBuilder\MainColumnProvider;
use Amasty\ReportBuilder\Model\SelectResolver\MainTableBuilder\MainEntityColumnProvider;

class MainTableBuilder implements MainTableBuilderInterface
{
    /**
     * @var ProviderInterface
     */
    private $provider;

    /**
     * @var SelectFactory
     */
    private $selectFactory;

    /**
     * @var DataResource
     */
    private $resource;

    /**
     * @var ReportResolver
     */
    private $reportResolver;

    /**
     * @var MainEntityColumnProvider
     */
    private $mainEntityColumnProvider;

    public function __construct(
        ProviderInterface $provider,
        SelectFactory $selectFactory,
        DataResource $resource,
        ReportResolver $reportResolver,
        MainEntityColumnProvider $mainEntityColumnProvider
    ) {
        $this->provider = $provider;
        $this->selectFactory = $selectFactory;
        $this->resource = $resource;
        $this->reportResolver = $reportResolver;
        $this->mainEntityColumnProvider = $mainEntityColumnProvider;
    }

    /**
     * @param int|null $interval
     * @return Select
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function build(?string $interval = null): Select
    {
        $report = $this->reportResolver->resolve();
        $scheme = $this->provider->getEntityScheme();
        $entity = $scheme->getEntityByName($report->getMainEntity());

        $alias = $entity->getName();
        $select = $this->selectFactory->create();
        $select->from([$alias => $this->resource->getTable($entity->getMainTable())], []);

        list($column, $group) = $this->mainEntityColumnProvider->getColumns($interval);
        $select->columns($column)->group($group);

        return $select;
    }
}
