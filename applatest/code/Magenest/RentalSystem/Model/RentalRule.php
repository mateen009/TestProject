<?php
namespace Magenest\RentalSystem\Model;

use Magenest\RentalSystem\Model\RentalRule\Condition\CombineFactory as ConditionCombine;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogRule\Helper\Data;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magenest\RentalSystem\Model\RentalRule\Condition\ConditionsToCollectionApplier;
use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRule\Model\Rule\Action\CollectionFactory as RuleCollectionFactory;
use Magento\CatalogRule\Model\Rule\Condition\CombineFactory;
use Magento\CatalogRule\Model\Rule\CustomerGroupsOptionsProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

class RentalRule extends Rule
{
    /**
     * @inheritDoc
     */
    protected $_eventPrefix = 'magenest_rental_rule_model';

    /** @var ConditionsToCollectionApplier */
    private $conditionsToCollectionApplier;

    /** @var CustomerGroupsOptionsProvider */
    protected $_customerGroupsProvider;

    /** @var ConditionCombine */
    protected $_conditionCombine;

    /**
     * @param ResourceModel\RentalRule $rentalRuleResource
     * @param IndexerProcessor $indexerProcessor
     * @param ConditionCombine $conditionCombine
     * @param CustomerGroupsOptionsProvider $customerGroupsOptionsProvider
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param CollectionFactory $productCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param CombineFactory $combineFactory
     * @param RuleCollectionFactory $actionCollectionFactory
     * @param ProductFactory $productFactory
     * @param Iterator $resourceIterator
     * @param Session $customerSession
     * @param Data $catalogRuleData
     * @param TypeListInterface $cacheTypesList
     * @param DateTime $dateTime
     * @param RuleProductProcessor $ruleProductProcessor
     * @param ConditionsToCollectionApplier $conditionsToCollectionApplier
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $relatedCacheTypes
     * @param array $data
     * @param ExtensionAttributesFactory|null $extensionFactory
     * @param AttributeValueFactory|null $customAttributeFactory
     * @param Json|null $serializer
     */
    public function __construct(
        ResourceModel\RentalRule $rentalRuleResource,
        IndexerProcessor $indexerProcessor,
        ConditionCombine $conditionCombine,
        CustomerGroupsOptionsProvider $customerGroupsOptionsProvider,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        CollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager,
        CombineFactory $combineFactory,
        RuleCollectionFactory $actionCollectionFactory,
        ProductFactory $productFactory,
        Iterator $resourceIterator,
        Session $customerSession,
        Data $catalogRuleData,
        TypeListInterface $cacheTypesList,
        DateTime $dateTime,
        RuleProductProcessor $ruleProductProcessor,
        ConditionsToCollectionApplier $conditionsToCollectionApplier,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $relatedCacheTypes = [],
        array $data = [],
        ExtensionAttributesFactory $extensionFactory = null,
        AttributeValueFactory $customAttributeFactory = null,
        Json $serializer = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $productCollectionFactory,
            $storeManager,
            $combineFactory,
            $actionCollectionFactory,
            $productFactory,
            $resourceIterator,
            $customerSession,
            $catalogRuleData,
            $cacheTypesList,
            $dateTime,
            $ruleProductProcessor,
            $resource,
            $resourceCollection,
            $relatedCacheTypes,
            $data,
            $extensionFactory,
            $customAttributeFactory,
            $serializer,
            null,
            null
        );
        $this->_customerGroupsProvider = $customerGroupsOptionsProvider;
        $this->_conditionCombine = $conditionCombine;
        $this->conditionsToCollectionApplier = $conditionsToCollectionApplier;
        $this->_ruleProductProcessor = $indexerProcessor;
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\RentalRule::class);
    }

    /**
     * Reservation rule support all websites
     * @return mixed|string|null
     */
    public function getWebsiteIds()
    {
        if (!$this->hasWebsiteIds()) {

            $listWebsites = $this->_storeManager->getWebsites();
            $listWebsiteIds = [];
            foreach ($listWebsites as $website) {
                $listWebsiteIds[] = $website->getId();
            }
            $this->setData('website_ids', (array)$listWebsiteIds);
        }
        return $this->_getData('website_ids');
    }

    /**
     * @return array|mixed|null
     */
    public function getCustomerGroupIds()
    {
        if (!$this->hasCustomerGroupIds()) {

            $customerGroups = $this->_customerGroupsProvider->toOptionArray();
            $customerGroupIds = [];
            foreach ($customerGroups as $customerGroup) {
                $customerGroupIds[] = $customerGroup['value'];
            }
            $this->setData('customer_group_ids', (array)$customerGroupIds);
        }
        return $this->_getData('customer_group_ids');
    }

    /**
     * Getter for rule conditions collection
     *
     * @return RentalRule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->_conditionCombine->create();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     */
    public function getMatchingProductIds()
    {
        if ($this->_productIds === null) {
            $this->_productIds = [];
            $this->setCollectedAttributes([]);

            if ($this->getWebsiteIds()) {
                $productCollection = $this->_productCollectionFactory->create()
                    ->addWebsiteFilter($this->getWebsiteIds())
                    ->addFieldToFilter('type_id', Rental::PRODUCT_TYPE);
                if ($this->_productsFilter) {
                    $productCollection->addIdFilter($this->_productsFilter);
                }
                $this->getConditions()->collectValidatedAttributes($productCollection);

                if ($this->canPreMapProducts()) {
                    $productCollection = $this->conditionsToCollectionApplier->applyConditionsToCollection(
                        $this->getConditions(),
                        $productCollection
                    );
                }

                $this->_resourceIterator->walk(
                    $productCollection->getSelect(),
                    [[$this, 'callbackValidateProduct']],
                    [
                        'attributes' => $this->getCollectedAttributes(),
                        'product' => $this->_productFactory->create()
                    ]
                );
            }
        }

        return $this->_productIds;
    }

    /**
     * Check if we can use mapping for rule conditions
     *
     * @return bool
     */
    private function canPreMapProducts()
    {
        $conditions = $this->getConditions();
        return $conditions && $conditions->getConditions();
    }
}
