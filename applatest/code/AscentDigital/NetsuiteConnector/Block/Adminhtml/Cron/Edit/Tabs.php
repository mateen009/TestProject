<?php

namespace AscentDigital\NetsuiteConnector\Block\Adminhtml\Cron\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

class Tabs extends WidgetTabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('cron_job_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Cron Job Tabs'));
    }
    protected function _beforeToHtml()
    {
        $this->addTab(
            'order_cron',
            [
                'label' => __('Order Crons'),
                'title' => __('Order Crons'),
                'content' => $this->getLayout()->createBlock(
                    'AscentDigital\NetsuiteConnector\Block\Adminhtml\Cron\OrderCronTab'
                )->toHtml(),//Change this to your block layout as per your need.
                'active' => true
            ]
        );
        $this->addTab(
            'shipping_cron',
            [
                'label' => __('Shipping Crons'),
                'title' => __('Shipping Crons'),
                'content' => $this->getLayout()->createBlock(
                    'AscentDigital\NetsuiteConnector\Block\Adminhtml\Cron\ShippingCronTab'
                )->toHtml(),//Change this to your block layout as per your need.
                'active' => false
            ]
        );
        $this->addTab(
            'customer_specific_prices_item_cron',
            [
                'label' => __('Customer Specific Prices Item Cron'),
                'title' => __('Customer Specific Prices Item Cron'),
                'content' => $this->getLayout()->createBlock(
                    'AscentDigital\NetsuiteConnector\Block\Adminhtml\Cron\CustomerSpecificPricingTab'
                )->toHtml(),//Change this to your block layout as per your need.
                'active' => false
            ]
        );
        $this->addTab(
            'advance_exchange_cron',
            [
                'label' => __('Advance Exchange Cron'),
                'title' => __('Advance Exchange Cron'),
                'content' => $this->getLayout()->createBlock(
                    'AscentDigital\NetsuiteConnector\Block\Adminhtml\Cron\AdvanceExchangeTab'
                )->toHtml(),//Change this to your block layout as per your need.
                'active' => false
            ]
        );
        $this->addTab(
            'customer_specific_terms_cron',
            [
                'label' => __('Customer Specific Terms Cron'),
                'title' => __('Customer Specific Terms Cron'),
                'content' => $this->getLayout()->createBlock(
                    'AscentDigital\NetsuiteConnector\Block\Adminhtml\Cron\CustomerSpecificTermsTab'
                )->toHtml(),//Change this to your block layout as per your need.
                'active' => false
            ]
        );
        $this->addTab(
            'deleted_product_cron',
            [
                'label' => __('Deleted Product Cron'),
                'title' => __('Deleted Product Cron'),
                'content' => $this->getLayout()->createBlock(
                    'AscentDigital\NetsuiteConnector\Block\Adminhtml\Cron\DeletedProductTab'
                )->toHtml(),//Change this to your block layout as per your need.
                'active' => false
            ]
        );
        $this->addTab(
            'update_products_cron',
            [
                'label' => __('Update Products Cron'),
                'title' => __('Update Products Cron'),
                'content' => $this->getLayout()->createBlock(
                    'AscentDigital\NetsuiteConnector\Block\Adminhtml\Cron\UpdateProductsTab'
                )->toHtml(),//Change this to your block layout as per your need.
                'active' => false
            ]
        );
      
        $this->addTab(
            'locations_cron',
            [
                'label' => __('Locations Cron'),
                'title' => __('Locations Cron'),
                'content' => $this->getLayout()->createBlock(
                    'AscentDigital\NetsuiteConnector\Block\Adminhtml\Cron\LocationsTab'
                )->toHtml(),//Change this to your block layout as per your need.
                'active' => false
            ]
        );
        return parent::_beforeToHtml();
    }
}