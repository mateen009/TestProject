<?php
namespace Magenest\RentalSystem\Setup\Patch\Schema;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Psr\Log\LoggerInterface;

/**
 * Class AddIndexProductIdRentalProduct
 * @package Magenest\RentalSystem\Setup\Patch\Schema
 */
class AddIndexProductIdRentalProduct implements SchemaPatchInterface
{
    /** @var SchemaSetupInterface  */
    protected $_schemaSetup;

    /** @var LoggerInterface  */
    protected $_logger;

    /**
     * AddIndexProductIdRentalProduct constructor.
     * @param SchemaSetupInterface $schemaSetup
     * @param LoggerInterface $logger
     */
    public function __construct(
        SchemaSetupInterface $schemaSetup,
        LoggerInterface $logger
    ) {
        $this->_schemaSetup = $schemaSetup;
        $this->_logger = $logger;
    }

    /**
     * @return AddIndexProductIdRentalProduct|void
     */
    public function apply()
    {
        try {
            $connection = $this->_schemaSetup->getConnection();
            //Add UNIQUE
            $rentalProductTbl = $this->_schemaSetup->getTable('magenest_rental_product');
            if ($connection->isTableExists($rentalProductTbl)) {
                $connection->addIndex(
                    $rentalProductTbl,
                    $this->_schemaSetup->getIdxName(
                        $rentalProductTbl,
                        ['product_id'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['product_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                );
            }

            $rentalPriceTbl = $this->_schemaSetup->getTable('magenest_rental_price');
            if ($connection->isTableExists($rentalProductTbl)) {
                $connection->addIndex(
                    $rentalPriceTbl,
                    $this->_schemaSetup->getIdxName(
                        $rentalPriceTbl,
                        ['rental_id', 'product_id'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['rental_id', 'product_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                );
            }

            $rentalOptionTbl = $this->_schemaSetup->getTable('magenest_rental_option');
            if ($connection->isTableExists($rentalProductTbl)) {
                $connection->addIndex(
                    $rentalOptionTbl,
                    $this->_schemaSetup->getIdxName(
                        $rentalOptionTbl,
                        ['rental_id', 'product_id', 'option_id'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['rental_id', 'product_id', 'option_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                );
            }

            $rentalOptionTypeTbl = $this->_schemaSetup->getTable('magenest_rental_optiontype');
            if ($connection->isTableExists($rentalOptionTypeTbl)) {
                $connection->addIndex(
                    $rentalOptionTbl,
                    $this->_schemaSetup->getIdxName(
                        $rentalOptionTbl,
                        ['option_id', 'product_id', 'option_number'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['option_id', 'product_id', 'option_number'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                );
            }
        } catch (\Exception $exception) {
            $this->_logger->debug($exception->getMessage());
        }
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }
}
