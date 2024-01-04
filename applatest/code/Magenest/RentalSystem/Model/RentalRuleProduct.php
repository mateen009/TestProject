<?php

namespace Magenest\RentalSystem\Model;

use Magenest\RentalSystem\Model\ResourceModel\RentalRuleProduct as ResourceModel;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class RentalRuleProduct extends AbstractModel implements IdentityInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'magenest_rental_rule_product_model';

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ProductRepositoryInterface $productRepository
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProductRepositoryInterface $productRepository,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getIdentities()
    {
        return $this->productRepository->getById($this->getProductId())->getIdentities();
    }
}
