<?php
namespace Magenest\RentalSystem\Model\RentalRule\Condition;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\AdvancedFilterProcessor;
use Magento\Framework\Exception\InputException;

class ConditionsToCollectionApplier
{
    /** @var ConditionsToSearchCriteriaMapper */
    private $conditionsToSearchCriteriaMapper;

    /** @var AdvancedFilterProcessor */
    private $searchCriteriaProcessor;

    /** @var MappableConditionsProcessor */
    private $mappableConditionsProcessor;

    /**
     * @param ConditionsToSearchCriteriaMapper $conditionsToSearchCriteriaMapper
     * @param AdvancedFilterProcessor $searchCriteriaProcessor
     * @param MappableConditionsProcessor $mappableConditionsProcessor
     */
    public function __construct(
        ConditionsToSearchCriteriaMapper $conditionsToSearchCriteriaMapper,
        AdvancedFilterProcessor $searchCriteriaProcessor,
        MappableConditionsProcessor $mappableConditionsProcessor
    ) {
        $this->conditionsToSearchCriteriaMapper = $conditionsToSearchCriteriaMapper;
        $this->searchCriteriaProcessor = $searchCriteriaProcessor;
        $this->mappableConditionsProcessor = $mappableConditionsProcessor;
    }

    /**
     * Transforms catalog rule conditions to search criteria
     * and applies them on product collection
     *
     * @param Combine $conditions
     * @param ProductCollection $productCollection
     * @return ProductCollection
     * @throws InputException
     */
    public function applyConditionsToCollection(
        \Magento\Rule\Model\Condition\Combine $conditions,
        ProductCollection $productCollection
    ) {
        // rebuild conditions to have only those that we know how to map them to product collection
        $mappableConditions = $this->mappableConditionsProcessor->rebuildConditionsTree($conditions);

        // transform conditions to search criteria
        $searchCriteria = $this->conditionsToSearchCriteriaMapper->mapConditionsToSearchCriteria($mappableConditions);

        $mappedProductCollection = clone $productCollection;

        // apply search criteria to new version of product collection
        $this->searchCriteriaProcessor->process($searchCriteria, $mappedProductCollection);

        return $mappedProductCollection;
    }
}
