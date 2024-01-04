<?php
namespace PerksAtWork\NextJumpSFTP\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Sales\Setup\SalesSetupFactory;


class UpgradeData implements UpgradeDataInterface
{
    private $salesSetupFactory;

    /**
     * Constructor
     *
     * @param \Magento\Sales\Setup\SalesSetupFactory $salesSetupFactory
     */
    public function __construct(SalesSetupFactory $salesSetupFactory)
    {
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        if (version_compare($context->getVersion(), "3.0.1", "<")) {
            $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
            $salesSetup->addAttribute(
                'order',
                'csid',
                [
                    'type' => 'varchar',
                    'length' => 100,
                    'visible' => false,
                    'required' => false,
                    'grid' => true
                ]
            );
            $salesSetup->addAttribute(
                'order',
                'is_csv_generated',
                [
                    'type' => 'varchar',
                    'input' => 'text',
                    'length' => 5,
                    'visible' => false,
                    'required' => false,
                    'grid' => true
                ]
            );
        }
    }
}
