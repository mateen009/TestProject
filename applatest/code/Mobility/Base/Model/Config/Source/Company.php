<?php
namespace Mobility\Base\Model\Config\Source;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;

class Company extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    public function __construct(
        StoreRepositoryInterface $storeRepository
    ) {
        $this->storeRepository = $storeRepository;
    }

    /**
    * Get all options
    *
    * @return array
    */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $stores = $this->getAllStoreList();
            $this->_options[] = ['value' => '', 'label' => __('Please select Company')];
            foreach ($stores as $store) {
                if ($store->getCode() != 'admin') {
                    $this->_options[] = ['label' => $store->getName(), 'value'=> $store->getId()];
                }
            }
        }

        return $this->_options;

    }

    /**
     * Get Store list
     *
     * @return StoreInterface[]
     */
    private function getAllStoreList(): array
    {
        $storeList = $this->storeRepository->getList();

        return $storeList;
    }
}
