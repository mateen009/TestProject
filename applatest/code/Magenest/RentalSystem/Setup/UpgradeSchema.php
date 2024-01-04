<?php

namespace Magenest\RentalSystem\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Ui\Api\Data\BookmarkInterfaceFactory;
use Magento\Ui\Model\ResourceModel\BookmarkRepository;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var BookmarkInterfaceFactory
     */
    protected $bookmarkFactory;

    /**
     * @var BookmarkRepository
     */
    protected $bookmarkRepository;

    /**
     * UpgradeSchema constructor.
     *
     * @param BookmarkInterfaceFactory $bookmarkFactory
     * @param BookmarkRepository $bookmarkRepository
     */
    public function __construct(
        BookmarkInterfaceFactory $bookmarkFactory,
        BookmarkRepository $bookmarkRepository
    ) {
        $this->bookmarkFactory    = $bookmarkFactory;
        $this->bookmarkRepository = $bookmarkRepository;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.3.0', '<')) {
            $this->addColDeliverValue($installer);
            $this->addColHold($installer);
            $this->addColQtyInvoice($installer);
            $this->addColQtyRented($installer);
            $this->dropColTimeRented($installer);
            $this->addFkProductEntity($installer);
            $this->deleteRentalInterfaceBookmarks();
        }

        $installer->endSetup();
    }

    /**
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function deleteRentalInterfaceBookmarks()
    {
        $collection = $this->bookmarkFactory->create()->getCollection();
        $collection->addFieldToFilter('namespace', ['eq' => 'rentalsystem_product_listing']);

        foreach ($collection->getItems() as $bookmark) {
            $this->bookmarkRepository->deleteById($bookmark->getBookmarkId());
        }
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $installer
     */
    private function addFkProductEntity($installer)
    {
        $installer->getConnection()->addForeignKey(
            $installer->getFkName(
                $installer->getTable('magenest_rental_price'),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id'
            ),
            $installer->getTable('magenest_rental_price'),
            'product_id',
            $installer->getTable('catalog_product_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        );

        $installer->getConnection()->addForeignKey(
            $installer->getFkName(
                $installer->getTable('magenest_rental_option'),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id'
            ),
            $installer->getTable('magenest_rental_option'),
            'product_id',
            $installer->getTable('catalog_product_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        );

        $installer->getConnection()->addForeignKey(
            $installer->getFkName(
                $installer->getTable('magenest_rental_optiontype'),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id'
            ),
            $installer->getTable('magenest_rental_optiontype'),
            'product_id',
            $installer->getTable('catalog_product_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        );
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $installer
     */
    private function addColDeliverValue($installer)
    {
        if (!$installer->tableExists('magenest_rental_order')) {
            return;
        }

        $installer->getConnection()->addColumn(
            $installer->getTable('magenest_rental_order'),
            'delivery_value',
            [
                'type'     => Table::TYPE_TEXT,
                'size'     => 255,
                'nullable' => true,
                'comment'  => 'Delivery Value'
            ]
        );
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $installer
     */
    private function addColQtyInvoice($installer)
    {
        if (!$installer->tableExists('magenest_rental_order')) {
            return;
        }

        $installer->getConnection()->addColumn(
            $installer->getTable('magenest_rental_order'),
            'qty_invoiced',
            [
                'type'     => Table::TYPE_INTEGER,
                'length'   => 10,
                'nullable' => true,
                'comment'  => 'Qty Invoiced'
            ]
        );
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $installer
     */
    private function addColHold($installer)
    {
        if (!$installer->tableExists('magenest_rental_product')) {
            return;
        }

        $installer->getConnection()->addColumn(
            $installer->getTable('magenest_rental_product'),
            'hold',
            [
                'type'     => Table::TYPE_INTEGER,
                'length'   => 10,
                'nullable' => true,
                'comment'  => 'Hold'
            ]
        );
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $installer
     */
    private function addColQtyRented($installer)
    {
        if (!$installer->tableExists('magenest_rental_product')) {
            return;
        }

        $installer->getConnection()->addColumn(
            $installer->getTable('magenest_rental_product'),
            'qty_rented',
            [
                'type'     => Table::TYPE_INTEGER,
                'length'   => 10,
                'nullable' => true,
                'comment'  => 'Qty Rented',
                'default'  => 0
            ]
        );
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $installer
     */
    private function dropColTimeRented($installer)
    {
        if (!$installer->tableExists('magenest_rental_product')) {
            return;
        }

        $installer->getConnection()->dropColumn(
            $installer->getTable('magenest_rental_product'),
            'time_rented'
        );
    }

}
