<?php
namespace Magenest\RentalSystem\Observer\Category;

use Magenest\RentalSystem\Model\IndexerProcessor;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magenest\RentalSystem\Model\ResourceModel\RentalRule\CollectionFactory as RentalRuleCollection;

class ChangeProducts implements ObserverInterface
{
    /** @var RentalRuleCollection */
    private $rentalRuleCollectionFactory;

    /** @var IndexerProcessor */
    private $rentalRuleIndexer;

    /**
     * @param IndexerProcessor $rentalRuleIndexer
     * @param RentalRuleCollection $rentalRuleCollectionFactory
     */
    public function __construct(
        IndexerProcessor $rentalRuleIndexer,
        RentalRuleCollection $rentalRuleCollectionFactory
    ) {
        $this->rentalRuleIndexer           = $rentalRuleIndexer;
        $this->rentalRuleCollectionFactory = $rentalRuleCollectionFactory;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $observer->getEvent()->getCategory();
        $affectedRule = $this->rentalRuleCollectionFactory->create()
            ->addFieldToFilter('category_ids', ['finset' => $category->getId()])
            ->getAllIds();
        if (!$this->rentalRuleIndexer->isIndexerScheduled()) {
            $this->rentalRuleIndexer->reindexList($affectedRule);
        } else {
            $this->rentalRuleIndexer->markIndexerAsInvalid();
        }
    }
}
