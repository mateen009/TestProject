<?php
namespace Magenest\RentalSystem\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Indexer\ActionInterface as IndexerInterface;
use Magento\Framework\Mview\ActionInterface as MviewInterface;

class Indexer implements IndexerInterface, MviewInterface
{
    const INDEXER_ID = "magenest_rental_rule";

    /** @var array */
    private $cachedRule;

    /** @var RentalRuleFactory */
    private $rentalRuleFactory;

    /** @var ResourceModel\RentalRule */
    private $rentalRuleResources;

    /** @var ResourceModel\RentalRule\CollectionFactory */
    private $rentalRuleCollection;

    /** @var RentalRuleProductFactory */
    private $rentalRuleProductFactory;

    /** @var ResourceModel\RentalRuleProduct */
    private $rentalRuleProductResources;

    /** @var ResourceModel\RentalRuleProduct\CollectionFactory */
    private $rentalRuleProductCollection;

    /**
     * @param RentalRuleFactory $rentalRuleFactory
     * @param ResourceModel\RentalRule $rentalRuleResources
     * @param ResourceModel\RentalRule\CollectionFactory $rentalRuleCollection
     * @param RentalRuleProductFactory $rentalRuleProductFactory
     * @param ResourceModel\RentalRuleProduct $rentalRuleProductResources
     * @param ResourceModel\RentalRuleProduct\CollectionFactory $rentalRuleProductCollection
     * @param array $data
     */
    public function __construct(
        RentalRuleFactory $rentalRuleFactory,
        ResourceModel\RentalRule $rentalRuleResources,
        ResourceModel\RentalRule\CollectionFactory $rentalRuleCollection,
        RentalRuleProductFactory $rentalRuleProductFactory,
        ResourceModel\RentalRuleProduct $rentalRuleProductResources,
        ResourceModel\RentalRuleProduct\CollectionFactory $rentalRuleProductCollection,
        array $data = []
    ) {
        $this->rentalRuleFactory = $rentalRuleFactory;
        $this->rentalRuleResources = $rentalRuleResources;
        $this->rentalRuleCollection = $rentalRuleCollection;
        $this->rentalRuleProductFactory = $rentalRuleProductFactory;
        $this->rentalRuleProductResources = $rentalRuleProductResources;
        $this->rentalRuleProductCollection = $rentalRuleProductCollection;
        $this->cachedRule = $data;
    }

    /**
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function executeFull()
    {
        $this->rentalRuleProductCollection->create()->walk('delete');
        $this->cachedRule = $this->rentalRuleCollection->create()->getItems();
        $rentalRuleProductModel = $this->rentalRuleProductFactory->create();
        foreach ($this->cachedRule as $id => $rule) {
            $this->saveRentalRuleProduct($id, $rentalRuleProductModel);
        }
    }

    /**
     * @param array $ids
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function executeList(array $ids)
    {
        $this->rentalRuleProductCollection->create()->addFieldToFilter('rule_id', ['in' => $ids])->walk('delete');
        $rentalRuleProductModel = $this->rentalRuleProductFactory->create();
        foreach ($ids as $id) {
            $this->saveRentalRuleProduct($id, $rentalRuleProductModel);
        }
    }

    /**
     * @param int $id
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function executeRow($id)
    {
        $this->rentalRuleProductCollection->create()->addFieldToFilter('rule_id', $id)->walk('delete');
        $rentalRuleProductModel = $this->rentalRuleProductFactory->create();
        $this->saveRentalRuleProduct($id, $rentalRuleProductModel);
    }

    /**
     * @param int[] $ids
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function execute($ids)
    {
        $this->executeList($ids);
    }

    /**
     * @param $id
     * @param $rentalRuleProductModel
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\InputException
     * @throws NoSuchEntityException
     */
    private function saveRentalRuleProduct($id, $rentalRuleProductModel)
    {
        if (isset($this->cachedRule[$id])) {
            $rentalRuleModel = $this->cachedRule[$id];
        } else {
            $rentalRuleModel = $this->rentalRuleFactory->create();
            $this->rentalRuleResources->load($rentalRuleModel, $id);
            if (!$rentalRuleModel->getId()) {
                throw new NoSuchEntityException(__("Rental Rule ID %1 no longer exists.", $id));
            }

            $this->cachedRule[$id] = $rentalRuleModel;
        }

        $productIds = array_keys($rentalRuleModel->getMatchingProductIds());
        foreach ($productIds as $productId) {
            $rentalRuleProductModel->unsetData();
            $rentalRuleProductModel->setData([
                'rule_id' => $id,
                'product_id' => $productId
            ]);
            $this->rentalRuleProductResources->save($rentalRuleProductModel);
        }
    }
}
