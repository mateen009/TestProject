<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * ProductReport Reviews collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magenest\RentalSystem\Model\ResourceModel\Report\ProductReport\Collection;

/**
 * @api
 * @since 100.0.2
 */
class Initial extends \Magento\Reports\Model\ResourceModel\Report\Collection
{
    /**
     * ProductReport sub-collection class name
     *
     * @var string
     */
    protected $_reportCollection = \Magenest\RentalSystem\Model\ResourceModel\Report\ProductReport\Collection::class;
}
