<?php
namespace Magenest\RentalSystem\Ui\DataProvider\RentalRule;

use Magenest\RentalSystem\Model\RentalRule;
use Magenest\RentalSystem\Model\ResourceModel\RentalRule\Collection;
use Magenest\RentalSystem\Model\ResourceModel\RentalRule\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    /** @var Collection */
    protected $collection;

    /** @var DataPersistorInterface */
    protected $dataPersistor;

    /**
     * DataProvider constructor.
     * @param DataPersistorInterface $dataPersistor
     * @param CollectionFactory $rentalRuleCollection
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        DataPersistorInterface $dataPersistor,
        CollectionFactory $rentalRuleCollection,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->dataPersistor = $dataPersistor;
        $this->collection = $rentalRuleCollection->create();
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var RentalRule $rule */
        foreach ($items as $rule) {
            $this->loadedData[$rule->getId()] = $rule->getData();
        }

        $data = $this->dataPersistor->get('rentalsystem_rule');
        if (!empty($data)) {
            $rule = $this->collection->getNewEmptyItem();
            $rule->setData($data);
            $this->loadedData[$rule->getId()] = $rule->getData();
            $this->dataPersistor->clear('rentalsystem_rule');
        }

        return $this->loadedData ?? [];
    }
}
