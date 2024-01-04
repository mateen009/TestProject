<?php

namespace Checkout\Fields\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Class UpgradeData*/

class UpgradeData implements UpgradeDataInterface

{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    public function __construct(SalesSetupFactory $salesSetupFactory, EavSetupFactory $eavSetupFactory)

    {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function upgrade(

        ModuleDataSetupInterface $setup,

        ModuleContextInterface $context

    ) {

        if (version_compare($context->getVersion(), "1.0.0", "<")) {


            $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);

            $salesSetup->addAttribute(
                'order',
                'cost_center',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => false,
                    'required' => false,
                    'grid' => true
                ]
            );

            $salesSetup->addAttribute(
                'order',
                'client_po',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => false,
                    'required' => false,
                    'grid' => true
                ]
            );

            $salesSetup->addAttribute(
                'order',
                'customer_shipping_account',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'visible' => true,
                    'required' => false,
                    'default' => 0,
                    'comment' => 'Customer Shipping Account',
                    'grid' => true
                ]
            );



            // remove customer attribute
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            // $eavSetup->removeAttribute(
            //     \Magento\Customer\Model\Customer::ENTITY,
            //     'csm_phone'
            // );
            // $eavSetup->removeAttribute(
            //     \Magento\Sales\Model\Order::ENTITY,
            //     'return_status'
            // );
        }
    }
}
