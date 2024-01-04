<?php

namespace AscentDigital\OrderApproval\Setup;

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

        if (version_compare($context->getVersion(), "3.0.3", "<")) {


            $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);

            $salesSetup->addAttribute(

                'order',

                'demo_length',

                [

                    'type' => 'varchar',

                    'length' => 10,

                    'visible' => false,

                    'required' => false,

                    'grid' => true

                ]
            );

            $salesSetup->addAttribute(

                'order',

                'approval_1_id',

                [

                    'type' => 'varchar',

                    'length' => 10,

                    'visible' => false,

                    'required' => false,

                    'grid' => true

                ]
            );

            $salesSetup->addAttribute(

                'order',

                'approval_2_id',

                [

                    'type' => 'varchar',

                    'length' => 10,

                    'visible' => false,

                    'required' => false,

                    'grid' => true

                ]
            );

            $salesSetup->addAttribute(

                'order',

                'approval_1_status',

                [

                    'type' => 'varchar',

                    'length' => 10,

                    'visible' => false,

                    'required' => false,

                    'grid' => true

                ]
            );

            $salesSetup->addAttribute(

                'order',

                'approval_2_status',

                [

                    'type' => 'varchar',

                    'length' => 10,

                    'visible' => false,

                    'required' => false,

                    'grid' => true

                ]
            );

            $salesSetup->addAttribute(

                'order',

                'ns_internal_id',

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

                'ns_so_number',

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

                'approval_1_token',

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

                'approval_2_token',

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

                'approval_1_token_status',

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

                'approval_2_token_status',

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

                'customer_approval_status',

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

                'customer_approval_token_status',

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

                'customer_approval_token',

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
                'customer_approval_email',
                [
                    'type' => 'varchar',
                    'length' => 50,
                    'visible' => false,
                    'required' => false,
                    'grid' => true
                ]
            );

            $salesSetup->addAttribute(
                'order',
                'approval_3_id',
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
                'approval_3_status',
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
                'approval_3_token',
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
                'approval_3_token_status',
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
                'agency_email',
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
                'agency_name',
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
                'sales_force_opportunity_number',
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
                'customer_ts',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => true,
                    'required' => false,
                    'grid' => true
                ]
            );

            $salesSetup->addAttribute(
                'order',
                'sales_manager_ts',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );

            $salesSetup->addAttribute(
                'order',
                'teritory_manager_ts',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );

            $salesSetup->addAttribute(
                'order',
                'exective_manager_ts',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );

            $salesSetup->addAttribute(
                'order',
                'return_status',
                [
                    'type' => 'varchar',
                    'input' => 'select',
                    'source' => 'AscentDigital\OrderApproval\Model\Config\Source\Options',
                    'length' => 32,
                    'visible' => false,
                    'required' => false,
                    'default' => 'No',
                    'grid' => true
                ]
            );

            $salesSetup->addAttribute(
                'order',
                'sm_email',
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
                'tm_email',
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
                'em_email',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => false,
                    'required' => false,
                    'grid' => true
                ]
            );

            // remove customer attribute
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->removeAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'csm_phone'
            );
            // $eavSetup->removeAttribute(
            //     \Magento\Sales\Model\Order::ENTITY,
            //     'return_status'
            // );
        }
    }
}
