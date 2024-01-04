<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 */

namespace Magenest\RentalSystem\Model\Import\Product\Type;

use Magenest\RentalSystem\Helper\ImportExport as ImportHelper;
use Magento\Catalog\Model\Product;
use Magento\CatalogImportExport\Model\Import\Product as ProductImport;
use Magento\CatalogImportExport\Model\Import\Product\Type\Virtual;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory as RentalEmailTemplateCollection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;

class Rental extends Virtual
{
    /** @var array  */
    protected $_productData = [];

    /** @var array  */
    protected $_rentalEmailTemplate = [];

    /** @var array  */
    protected $_table = [];

    private $_rentalPriceData = [];

    private $_rentalOptions = [];

    private $_rentalOptionType = [];

    private $_rentalOptionTypeData = [];

    const TYPE = 'rental';
    const EMAIL_TEMPLATE = 'email_template';
    const PRODUCT_ID = 'product_id';
    const RENTAL_ID = 'rental_id';
    const DELIVERY_TYPE = 'type';
    const COL_DELIVERY_TYPE = 'delivery_type';
    const LEAD_TIME = 'lead_time';
    const MAX_DURATION = 'max_duration';
    const INITIAL_QTY = 'initial_qty';
    const AVAILABLE_QTY = 'available_qty';
    const PICKUP_ADDRESS = 'pickup_address';
    const HOLD = 'hold';
    const QTY_RENTED = 'qty_rented';
    const RENTAL_PRICE_TYPE = 'type';
    const COL_RENTAL_PRICE_TYPE = 'rental_price_type';
    const RENTAL_PRICE_BASE = 'base_price';
    const RENTAL_PRICE_BASE_PERIOD = 'base_period';
    const RENTAL_PRICE_ADDITIONAL_PRICE = 'additional_price';
    const RENTAL_PRICE_ADDITIONAL_PERIOD = 'additional_period';
    const OPTIONS = 'rental_options';

    const OPTIONS_SEPARATOR = '|';

    /**
     * Basic rental data column
     */
    const COL_RENTAL = [
        self::EMAIL_TEMPLATE,
        self::COL_DELIVERY_TYPE,
        self::LEAD_TIME,
        self::MAX_DURATION,
        self::PICKUP_ADDRESS,
        self::HOLD,
        self::COL_RENTAL_PRICE_TYPE,
        self::RENTAL_PRICE_BASE,
        self::RENTAL_PRICE_BASE_PERIOD,
        self::RENTAL_PRICE_ADDITIONAL_PRICE,
        self::RENTAL_PRICE_ADDITIONAL_PERIOD,
    ];

    /**
     * Rental price & duration column
     */
    const COL_RENTAL_PRICE = [
        self::RENTAL_PRICE_BASE,
        self::RENTAL_PRICE_ADDITIONAL_PRICE
    ];

    /**
     * Rental options column
     */
    const COL_RENTAL_OPTION = 'rental_option';

    const RENTAL_ATTRIBUTES = [
        self::EMAIL_TEMPLATE,
        self::COL_DELIVERY_TYPE,
        self::LEAD_TIME,
        self::MAX_DURATION,
        self::PICKUP_ADDRESS,
    ];

    const RENTAL_ATTRIBUTES_NUMERIC = [
        self::LEAD_TIME,
        self::MAX_DURATION,
        self::HOLD,
        self::RENTAL_PRICE_BASE,
        self::RENTAL_PRICE_ADDITIONAL_PRICE
    ];

    const RENTAL_PRICE_ATTRIBUTES = [
        'base_price',
        'base_period',
        'additional_price',
        'additional_period'
    ];

    const RENTAL_PRICE_ATTRIBUTES_NUMERIC = [
        'base_price',
        'additional_price'
    ];

    const RENTAL_OPTION_ATTRIBUTES = [
        'name',
        'type',
        'is_required',
        'option',
        'price'
    ];

    const RENTAL_OPTION_ATTRIBUTES_NUMERIC = [
        'is_required',
        'price'
    ];

    const RENTAL_OPTION_TYPES = [
        'fixed',
        'per_day',
        'per_hour'
    ];

    const RENTAL_DELIVERY_TYPE = [
        '0',
        '1',
        '2'
    ];

    const RENTAL_PERIOD_UNIT = [
        'h',
        'd',
        'w'
    ];

    const ERROR_INCORRECT_BASIC_DATA = 'rentalIncorrect';

    const ERROR_MISSING_BASIC_DATA = 'rentalMissing';

    const ERROR_INCORRECT_DURATION_FORMAT = 'durationIncorrect';

    const ERROR_INCORRECT_PRICE_FORMAT = 'priceIncorrect';

    const ERROR_PRICE_MISSING = 'priceMissing';

    const ERROR_DURATION_MISSING = 'durationMissing';

    const ERROR_RENTAL_VALUE_MISSING = 'rentalOptionNoValue';

    protected $_messageTemplates = [
        self::ERROR_INCORRECT_BASIC_DATA      => 'Basic rental data is in incorrect format',
        self::ERROR_MISSING_BASIC_DATA        => 'Basic rental data is missing',
        self::ERROR_INCORRECT_DURATION_FORMAT => 'Rental duration is in incorrect format',
        self::ERROR_INCORRECT_PRICE_FORMAT    => 'Rental price is in incorrect format',
        self::ERROR_PRICE_MISSING             => 'Base rental price is missing',
        self::ERROR_DURATION_MISSING          => 'Base rental duration is missing',
        self::ERROR_RENTAL_VALUE_MISSING      => 'Rental config value is missing'
    ];

    protected $helper;

    /**
     * Entity model parameters.
     * @var array
     */
    protected $parameters = [];

    /**
     * Ids products
     * @var array
     */
    protected $productIds = [];

    /**
     * Default values for magenest_rental_product model
     * @var array
     */
    protected $rentalProductDefault = [
        'email_template' => 'rental_email_template',
        'type'           => 2,
        'lead_time'      => 0
    ];

    /** @var AttributeRepositoryInterface  */
    protected $attributeRepository;

    /** @var RentalEmailTemplateCollection  */
    protected $_rentalEmailTemplateCollection;

    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetColFac,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttrColFac,
        ResourceConnection $resource,
        AttributeRepositoryInterface $attributeRepository,
        RentalEmailTemplateCollection $rentalEmailTemplateCollection,
        ImportHelper $helper,
        array $params,
        MetadataPool $metadataPool = null
    ) {
        parent::__construct($attrSetColFac, $prodAttrColFac, $resource, $params, $metadataPool);
        $this->helper = $helper;
        $this->attributeRepository = $attributeRepository;
        $this->_rentalEmailTemplateCollection = $rentalEmailTemplateCollection;
        $this->getAllEmailTemplate();
    }

    /**
     * Validate row attributes. Pass VALID row data ONLY as argument.
     *
     * @param array $rowData
     * @param int $rowNum
     * @param bool $isNewProduct Optional
     *
     * @return bool
     */
    public function isRowValid(array $rowData, $rowNum, $isNewProduct = true)
    {
        $this->rowNum = $rowNum;
        $error        = false;
        if (!$this->helper->isRowRentalCorrectFormat($rowData)) {
            $this->_entityModel->addRowError(self::ERROR_INCORRECT_BASIC_DATA, $this->rowNum);
            $error = true;
        }

        return !$error;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function saveData()
    {
        $this->_productSuperData = [];
        $this->getProductData();
        $connection = $this->_resource->getConnection();
        while ($bunch = $this->_entityModel->getNextBunch()) {
            $rentalProduct = [];
            $rentalPrice = [];
            $rentalOptions = [];
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->_entityModel->isRowAllowedToImport($rowData, $rowNum) ||
                    isset($rowData[ProductImport::COL_TYPE]) && $rowData[ProductImport::COL_TYPE] !== self::TYPE) {
                    continue;
                }
                $sku = $rowData[ProductImport::COL_SKU];
                if (isset($this->_productData[$sku])) {
                    $productData = $this->_productData[$sku];
                    unset($productData['sku']);
                    $productId = $productData[self::PRODUCT_ID];
                    $productData[self::EMAIL_TEMPLATE] = $this->validEmailTemplate(
                        $rowData[self::EMAIL_TEMPLATE] ?? ''
                    );
                    $productData[self::DELIVERY_TYPE] = $this->validDeliveryType(
                        $rowData[self::COL_DELIVERY_TYPE] ?? ''
                    );
                    $productData[self::LEAD_TIME] = $rowData[self::LEAD_TIME] ?? '';
                    $productData[self::MAX_DURATION] = $rowData[self::MAX_DURATION] ?? '';
                    $productData[self::INITIAL_QTY] = $rowData['qty'] ?? 0;
                    $productData[self::AVAILABLE_QTY] = $rowData['qty'] ?? 0;
                    $productData[self::PICKUP_ADDRESS] = $rowData[self::PICKUP_ADDRESS] ?? '';
                    $productData[self::HOLD] = $rowData[self::HOLD] ?? 0;
                    $productData[self::QTY_RENTED] = 0;
                    $rentalProduct[] = $productData;
                    $rentalPrice[$productId] = [
                        self::PRODUCT_ID => $productId,
                        self::RENTAL_PRICE_TYPE => 0,
                        self::RENTAL_PRICE_BASE => $rowData[self::RENTAL_PRICE_BASE] ?? 0.00,
                        self::RENTAL_PRICE_BASE_PERIOD => $this->validRentalPricePeriod(
                            $rowData[self::RENTAL_PRICE_BASE_PERIOD] ?? '0h'
                        ),
                        self::RENTAL_PRICE_ADDITIONAL_PRICE => $rowData[self::RENTAL_PRICE_ADDITIONAL_PRICE] ?? 0.00,
                        self::RENTAL_PRICE_ADDITIONAL_PERIOD => $this->validRentalPricePeriod(
                            $rowData[self::RENTAL_PRICE_ADDITIONAL_PERIOD] ?? '0h'
                        )
                    ];
                    $rentalOptions[$productId] = $rowData[self::OPTIONS] ?? '';
                }
            }
            $this->insertRentalProduct($rentalProduct);
            $this->formatRentalProductPriceData($rentalPrice, $rentalOptions, $connection);
            $this->insertRentalProductPrice();
            $this->insertRentalOptions();
            $this->formatRentalOptionTypeData();
            $this->insertRentalOptionType();
        }
    }

    /**
     * @param $dataRaw
     * @throws \Exception
     */
    private function insertRentalProduct($dataRaw)
    {
        $connection = $this->_resource->getConnection();
        try {
            if (is_array($dataRaw) && !empty($dataRaw)) {
                $rentalProductTbl = $this->getTableName('magenest_rental_product');
                $fields = $this->getCols($dataRaw);
                $connection->beginTransaction();
                $connection->insertOnDuplicate($rentalProductTbl, $dataRaw, $fields);
                $connection->commit();
            }
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * @throws \Exception
     */
    private function insertRentalProductPrice()
    {
        $connection = $this->_resource->getConnection();
        try {
            if (is_array($this->_rentalPriceData) && !empty($this->_rentalPriceData)) {
                $dataRaw = $this->_rentalPriceData;
                $this->_rentalPriceData = [];
                $rentalPriceTbl = $this->getTableName('magenest_rental_price');
                $fields = $this->getCols($dataRaw);
                $connection->beginTransaction();
                $connection->insertOnDuplicate($rentalPriceTbl, $dataRaw, $fields);
                $connection->commit();
            }
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * @throws \Exception
     */
    private function insertRentalOptions()
    {
        $connection = $this->_resource->getConnection();
        try {
            if (is_array($this->_rentalOptions) && !empty($this->_rentalOptions)) {
                $rentalOption = $this->_rentalOptions;
                $this->_rentalOptions = [];
                $rentalProductOptionTbl = $this->getTableName('magenest_rental_option');
                $fields = $this->getCols($rentalOption);
                $connection->beginTransaction();
                $connection->insertOnDuplicate($rentalProductOptionTbl, $rentalOption, $fields);
                $connection->commit();
            }
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * @throws \Exception
     */
    private function insertRentalOptionType()
    {
        $connection = $this->_resource->getConnection();
        try {
            if (!empty($this->_rentalOptionTypeData)) {
                $rentalOptionTypeData = $this->_rentalOptionTypeData;
                $this->_rentalOptionTypeData = [];
                $rentalOptionTypeTbl = $this->getTableName('magenest_rental_optiontype');
                $fields = $this->getCols($rentalOptionTypeData);
                $connection->beginTransaction();
                $connection->insertOnDuplicate($rentalOptionTypeTbl, $rentalOptionTypeData, $fields);
                $connection->commit();
            }
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * @param $dataRaw
     * @param $rentalOptions
     * @param $connection
     * @throws \Exception
     */
    private function formatRentalProductPriceData($dataRaw, $rentalOptions, $connection)
    {
        $productIds = array_keys($dataRaw);
        $rentalProductTbl = $this->getTableName('magenest_rental_product');
        $select = $connection->select()->from(
            $rentalProductTbl,
            [
                self::PRODUCT_ID,
                'id'
            ]
        )->where(
            'product_id IN ( ? )',
            $productIds
        );
        $results = $connection->query($select)->fetchAll();
        if (is_array($results) && !empty($results)) {
            foreach ($results as $result) {
                $rentalId = $result['id'];
                $productId = $result[self::PRODUCT_ID];
                if (isset($dataRaw[$productId])) {
                    $dataRaw[$productId][self::RENTAL_ID] = $rentalId;
                    $this->_rentalPriceData[$productId] = $dataRaw[$productId];
                }
                if (isset($rentalOptions[$productId])) {
                    $options = $rentalOptions[$productId];
                    $this->formatRentalOptions($options, $productId, $rentalId);
                }
            }
        }
    }

    /**
     * @param $options
     * @param $productId
     * @param $rentalId
     * @throws \Exception
     */
    protected function formatRentalOptions($options, $productId, $rentalId)
    {
        $options = explode(self::OPTIONS_SEPARATOR, $options);
        $index = [];
        foreach ($options as $option) {
            $optionType = explode(",", $option);
            if (count($optionType) == 5) {
                $optionTitle = $optionType[0];
                $type = $this->validOptionType($optionType[1]);
                $isRequired = $this->validOptionRequired($optionType[2]);
                $uniqueKey = $optionTitle . $type . $isRequired;
                if (!isset($this->_rentalOptions[$uniqueKey])) {
                    if (!isset($index[$productId])) {
                        $index[$productId] = 0;
                    }
                    $optionId = $index[$productId];
                    $this->_rentalOptions[$uniqueKey] = [
                        self::RENTAL_ID => $rentalId,
                        self::PRODUCT_ID => $productId,
                        'option_id' => $optionId,
                        'option_title' => $optionTitle,
                        'type' => $type,
                        'is_required' => $isRequired
                    ];
                    $index[$productId] = $optionId + 1;
                }
                $this->_rentalOptionType[$productId][$uniqueKey][] = [
                    self::PRODUCT_ID => $productId,
                    'option_title' => $optionType[3],
                    'price' => $optionType[4]
                ];
            }
        }
    }

    /**
     * @throws \Exception
     */
    private function formatRentalOptionTypeData()
    {
        if (!empty($this->_rentalOptionType)) {
            $productIds = array_keys($this->_rentalOptionType);
            $connection = $this->_resource->getConnection();
            $rentalProductOptionTbl = $this->getTableName('magenest_rental_option');
            $select = $connection->select()->from(
                $rentalProductOptionTbl,
                [
                    'id',
                    self::PRODUCT_ID,
                    'option_title',
                    'type',
                    'is_required'
                ]
            )->where(
                'product_id IN ( ? )',
                $productIds
            );
            $results = $connection->query($select)->fetchAll();
            if (is_array($results) && !empty($results)) {
                foreach ($results as $result) {
                    $optionId = $result['id'];
                    $productId = $result[self::PRODUCT_ID];
                    $optionTitle = $result['option_title'];
                    $type = $result['type'];
                    $isRequired = $result['is_required'];
                    if (isset($this->_rentalOptionType[$productId])) {
                        $optionType = $this->_rentalOptionType[$productId];
                        $uniqueKey = $optionTitle . $type . $isRequired;
                        if (isset($optionType[$uniqueKey])) {
                            $types = $optionType[$uniqueKey];
                            $i = 0;
                            foreach ($types as $type) {
                                $type['option_id'] = $optionId;
                                $type['option_number'] = $i;
                                $this->_rentalOptionTypeData[] = $type;
                                $i++;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $data
     * @return array
     */
    private function getCols($data)
    {
        $row = reset($data);
        return array_keys($row);
    }

    /**
     * @param $emailName
     * @return string
     */
    protected function validEmailTemplate($emailName)
    {
        return in_array($emailName, $this->_rentalEmailTemplate) ? $emailName : 'rental_email_template';
    }

    /**
     * @param $type
     * @return int
     */
    protected function validDeliveryType($type)
    {
        return isset(self::RENTAL_DELIVERY_TYPE[$type]) ? $type : 0;
    }

    /**
     * @param $period
     * @return string
     */
    protected function validRentalPricePeriod($period)
    {
        if (in_array(substr($period, -1), self::RENTAL_PERIOD_UNIT)) {
            return $period;
        }
        return '0h';
    }

    /**
     * @param $type
     * @return false|string
     */
    protected function validOptionType($type)
    {
        $optionType = self::RENTAL_OPTION_TYPES;
        if (in_array($type, $optionType)) {
            return $type;
        }
        return reset($optionType);
    }

    /**
     * @param $isRequired
     * @return int
     */
    protected function validOptionRequired($isRequired)
    {
        return $isRequired ? 1 : 0;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getProductData()
    {
        $attributeId = $this->getAttributeId('name');
        $connection = $this->_resource->getConnection();
        //catalog_product_entity
        $catalogProductEntityTbl = $this->_resource->getTableName('catalog_product_entity');
        $catalogProductEntityVarcharTbl = $this->_resource->getTableName('catalog_product_entity_varchar');
        $select = $connection->select()->from(
            ['main_table' => $catalogProductEntityTbl],
            [
                'product_id' => 'main_table.entity_id',
                'sku' => 'main_table.sku',
                'product_name' => 'cpevarchar.value'
            ]
        )->joinLeft(
            ['cpevarchar' => $catalogProductEntityVarcharTbl],
            'cpevarchar.entity_id = main_table.entity_id',
        )->where(
            'cpevarchar.attribute_id = :attribute_id'
        );
        $rawData = $connection->fetchAll($select, [':attribute_id' => $attributeId]);
        if (!empty($rawData)) {
            foreach ($rawData as $data) {
                $this->_productData[$data['sku']] = [
                    'sku' => $data['sku'],
                    'product_id' => $data['product_id'],
                    'product_name' => $data['product_name']
                ];
            }
        }
    }

    /**
     * @param string $code
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getAttributeId($code)
    {
        return $this->attributeRepository->get(Product::ENTITY, $code)->getAttributeId();
    }

    /**
     * @return void
     */
    private function getAllEmailTemplate()
    {
        if (empty($this->_rentalEmailTemplate)) {
            $collection = $this->_rentalEmailTemplateCollection->create()->toOptionArray();
            $this->_rentalEmailTemplate = array_column($collection, 'value');
        }
    }

    /**
     * @param string $tableName
     * @return mixed|string
     */
    private function getTableName($tableName)
    {
        if (empty($this->_table) || !in_array($tableName, $this->_table)) {
            $this->_table[$tableName] = $this->_resource->getTableName($tableName);
        }
        return $this->_table[$tableName];
    }
}
