<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Ui\DataProvider\Form\Report;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Amasty\ReportBuilder\Model\ReportRegistry;
use Amasty\ReportBuilder\Model\ResourceModel\Report\CollectionFactory;
use Magento\Framework\UrlInterface;
use Amasty\Base\Plugin\Backend\Model\Menu\Item;

class DataProvider extends AbstractDataProvider
{
    const RELATIONS_URL = 'relations_url';
    const MAIN_ENTITY_URL = 'main_entity_url';
    const HOW_TO_LINK_URL = 'howto_link';

    const USER_GUIDE_LINK = 'https://amasty.com/docs/doku.php?id=magento_2:custom_reports';
    const SEO_PARAMS = '?utm_source=extension&utm_medium=backend&utm_campaign=userguide_Amasty_ReportBuilder';

    const RELATIONS_ROUTE = 'amreportbuilder/report/relations';
    const MAIN_ENTITY_ROUTE = 'amreportbuilder/report/mainEntity';

    /**
     * @var ReportRegistry
     */
    private $reportRegistry;

    /**
     * @var PoolInterface
     */
    private $pool;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportRegistry $reportRegistry,
        PoolInterface $pool,
        CollectionFactory $collectionFactory,
        UrlInterface $urlBuilder,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->reportRegistry = $reportRegistry;
        $this->pool = $pool;
        $this->collection = $collectionFactory->create();
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = [];

        /** @var \Amasty\ReportBuilder\Model\Report $report */
        $report = $this->reportRegistry->getReport();
        $reportId = $report->getReportId();
        if (!$report->isObjectNew()) {
            $data[$reportId] = $report->getData();
            $data[$reportId]['display_chart'] = $report->getDisplayChart();
        }

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $data = $modifier->modifyData($data);
        }

        return $data;
    }

    public function getConfigData()
    {
        $config = [
            self::MAIN_ENTITY_URL => $this->urlBuilder->getUrl(self::MAIN_ENTITY_ROUTE),
            self::RELATIONS_URL => $this->urlBuilder->getUrl(self::RELATIONS_ROUTE),
            self::HOW_TO_LINK_URL => self::USER_GUIDE_LINK . self::SEO_PARAMS
        ];

        return array_merge(parent::getConfigData(), $config);
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }
}
