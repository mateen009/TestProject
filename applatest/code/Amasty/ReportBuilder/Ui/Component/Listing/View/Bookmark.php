<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Ui\Component\Listing\View;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Model\ReportRegistry;
use Amasty\ReportBuilder\Model\View\Ui\Component\Listing\DefaultConfigurationProvider;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Magento\Ui\Component\AbstractComponent;

class Bookmark extends AbstractComponent
{
    const NAME = 'bookmark';

    /**
     * @var ReportRegistry
     */
    private $reportRegistry;

    /**
     * @var BookmarkRepositoryInterface
     */
    private $bookmarkRepository;

    /**
     * @var BookmarkManagementInterface
     */
    private $bookmarkManagement;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var DefaultConfigurationProvider
     */
    private $defaultConfigurationProvider;

    public function __construct(
        ContextInterface $context,
        BookmarkRepositoryInterface $bookmarkRepository,
        BookmarkManagementInterface $bookmarkManagement,
        ReportRegistry $reportRegistry,
        UrlInterface $urlBuilder,
        DefaultConfigurationProvider $defaultConfigurationProvider,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->reportRegistry = $reportRegistry;
        $this->context = $context;
        $this->bookmarkRepository = $bookmarkRepository;
        $this->bookmarkManagement = $bookmarkManagement;
        $this->urlBuilder = $urlBuilder;
        $this->defaultConfigurationProvider = $defaultConfigurationProvider;
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    public function prepare()
    {
        $namespace = $this->getContext()->getRequestParam('namespace', $this->getContext()->getNamespace());
        $report = $this->reportRegistry->getReport();

        $namespace = sprintf('%s_%s', $namespace, $report->getReportId());
        $config = [];
        if (!empty($namespace)) {
            $bookmarks = $this->bookmarkManagement->loadByNamespace($namespace);
            /** @var \Magento\Ui\Api\Data\BookmarkInterface $bookmark */
            foreach ($bookmarks->getItems() as $bookmark) {
                if ($bookmark->isCurrent()) {
                    $config['activeIndex'] = $bookmark->getIdentifier();
                }

                $config = array_merge_recursive($config, $bookmark->getConfig());
            }
        }

        $this->setData('config', array_replace_recursive(
            $config ?: $this->defaultConfigurationProvider->execute(),
            $this->getConfiguration()
        ));

        parent::prepare();

        $jsConfig = $this->getConfiguration();
        $this->getContext()->addComponentDefinition($this->getComponentName(), $jsConfig);
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        $config = parent::getConfiguration();
        $report = $this->reportRegistry->getReport();

        $config['storageConfig']['saveUrl'] = $this->urlBuilder->getUrl(
            'mui/bookmark/save',
            [ReportInterface::REPORT_ID => $report->getReportId()]
        );

        return $config;
    }
}
