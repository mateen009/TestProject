<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Ui\Component\Listing\Columns\Report;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Store\Model\Store as MagentoStore;

class Store extends Column
{
    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var SystemStore
     */
    private $systemStore;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SystemStore $systemStore,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->escaper = $escaper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->prepareItemData($item);
            }
        }

        return $dataSource;
    }

    private function prepareItemData(array $item): string
    {
        $content = '';

        if (isset($item[ReportInterface::STORE_IDS])) {
            $storeIds = is_array($item[ReportInterface::STORE_IDS])
                ? $item[ReportInterface::STORE_IDS] : explode(',', $item[ReportInterface::STORE_IDS]);

            if (in_array(MagentoStore::DEFAULT_STORE_ID, $storeIds)) {
                return __('All Store Views')->render();
            }

            $data = $this->systemStore->getStoresStructure(false, $storeIds);

            foreach ($data as $website) {
                $content .= $website['label'] . "<br/>";
                if (isset($website['children'])) {
                    foreach ($website['children'] as $group) {
                        $content .= str_repeat('&nbsp;', 3)
                            . $this->escaper->escapeHtml($group['label']) . "<br/>";
                        foreach ($group['children'] as $store) {
                            $content .= str_repeat('&nbsp;', 6)
                                . $this->escaper->escapeHtml($store['label']) . "<br/>";
                        }
                    }
                }
            }

        }

        return $content;
    }
}
