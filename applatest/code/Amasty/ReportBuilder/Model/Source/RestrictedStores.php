<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Source;

use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class RestrictedStores
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $nbsp;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $this->nbsp = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');
    }

    /**
     * @param array $allowedStoreIds
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getFormStoreValues(array $allowedStoreIds = []): array
    {
        $options = [];

        if (empty($allowedStoreIds) || in_array(Store::DEFAULT_STORE_ID, $allowedStoreIds)) {
            $options[] = ['label' => __('All Store Views'), 'value' => 0];
            $allowedStoreIds = array_keys($this->storeManager->getStores());
        }

        foreach ($this->storeManager->getWebsites() as $website) {
            $websiteShow = false;
            foreach ($this->storeManager->getGroups(true) as $group) {
                if ($website->getId() != $group->getWebsiteId()) {
                    continue;
                }
                $values = [];
                foreach ($this->storeManager->getStores(true) as $store) {
                    if ($group->getId() != $store->getGroupId()
                        || (!empty($allowedStoreIds) && !in_array($store->getId(), $allowedStoreIds))
                    ) {
                        continue;
                    }
                    if (!$websiteShow) {
                        $options[] = ['label' => $website->getName(), 'value' => []];
                        $websiteShow = true;
                    }
                    $values[] = [
                        'label' => str_repeat($this->nbsp, 4) . $store->getName(),
                        'value' => $store->getId(),
                    ];
                }
                if (!empty($values)) {
                    $options[] = [
                        'label' => str_repeat($this->nbsp, 4) . $group->getName(),
                        'value' => $values,
                    ];
                }
            }
        }
        array_walk(
            $options,
            function (&$item) {
                $item['__disableTmpl'] = true;
            }
        );
        return $options;
    }
}
