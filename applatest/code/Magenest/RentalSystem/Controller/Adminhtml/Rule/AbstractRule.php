<?php
namespace Magenest\RentalSystem\Controller\Adminhtml\Rule;

use Magenest\RentalSystem\Model\IndexerProcessor;
use Magenest\RentalSystem\Model\RentalRule\Condition\CombineFactory;
use Magenest\RentalSystem\Model\RentalRule\Condition\ProductFactory;
use Magenest\RentalSystem\Model\RentalRuleFactory;
use Magenest\RentalSystem\Model\ResourceModel\RentalRule as RentalRuleResource;
use Magenest\RentalSystem\Model\ResourceModel\RentalRule\CollectionFactory as RentalRuleCollection;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\CatalogRule\Model\Rule\Condition\ProductFactory as CategoryCondition;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

abstract class AbstractRule extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_RentalSystem::rental_rule';

    /** @var RentalRuleFactory */
    protected $rentalRule;

    /** @var RentalRuleResource */
    protected $rentalRuleResource;

    /** @var RentalRuleCollection */
    protected $rentalRuleCollection;

    /** @var Registry */
    protected $_coreRegistry = null;

    /** @var DataPersistorInterface */
    protected $dataPersistor;

    /** @var Json */
    protected $json;

    /** @var LoggerInterface */
    protected $logger;

    /** @var IndexerProcessor */
    protected $rentalRuleIndexer;

    /** @var CombineFactory */
    protected $combineCondition;

    /** @var CategoryCondition */
    protected $categoryCondition;

    /** @var ProductFactory */
    protected $productCondition;

    /**
     * @param RentalRuleFactory $rentalRuleFactory
     * @param RentalRuleCollection $rentalRuleCollection
     * @param RentalRuleResource $rentalRuleResource
     * @param DataPersistorInterface $dataPersistor
     * @param IndexerProcessor $rentalRuleIndexer
     * @param CombineFactory $combineCondition
     * @param CategoryCondition $categoryCondition
     * @param ProductFactory $productCondition
     * @param LoggerInterface $logger
     * @param Registry $coreRegistry
     * @param Json $json
     * @param Context $context
     */
    public function __construct(
        RentalRuleFactory $rentalRuleFactory,
        RentalRuleCollection $rentalRuleCollection,
        RentalRuleResource $rentalRuleResource,
        DataPersistorInterface $dataPersistor,
        IndexerProcessor $rentalRuleIndexer,
        CombineFactory $combineCondition,
        CategoryCondition $categoryCondition,
        ProductFactory $productCondition,
        LoggerInterface $logger,
        Registry $coreRegistry,
        Json $json,
        Context $context
    ) {
        parent::__construct($context);
        $this->rentalRule = $rentalRuleFactory;
        $this->rentalRuleResource = $rentalRuleResource;
        $this->rentalRuleCollection = $rentalRuleCollection;
        $this->rentalRuleIndexer = $rentalRuleIndexer;
        $this->combineCondition = $combineCondition;
        $this->categoryCondition = $categoryCondition;
        $this->productCondition = $productCondition;
        $this->_coreRegistry = $coreRegistry;
        $this->dataPersistor = $dataPersistor;
        $this->logger = $logger;
        $this->json = $json;
    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magenest_RentalSystem::rental_rule')
            ->_addBreadcrumb(__('Rental Price Rules'), __('Rental Price Rules'));

        return $this;
    }
}
