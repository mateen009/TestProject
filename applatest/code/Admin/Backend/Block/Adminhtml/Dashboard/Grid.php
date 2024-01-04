<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Admin\Backend\Block\Adminhtml\Dashboard;

/**
 * Adminhtml dashboard grid
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @api
 * @since 100.0.2
 */
class Grid extends \Magento\Backend\Block\Dashboard\Grid
{
    /**
     * @var string
     */
    protected $_template = 'Admin_Backend::dashboard/grid.phtml';

    /**
     * Setting default for every grid on dashboard
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setDefaultLimit(5);
    }
}

