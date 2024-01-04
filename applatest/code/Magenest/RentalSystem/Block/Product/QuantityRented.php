<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Block\Product;

use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\DataObject\IdentityInterface;
use Magenest\RentalSystem\Model\RentalFactory;

class QuantityRented extends Template implements IdentityInterface
{
    /** Rental Settings */
    const XML_PATH_QTY_RENTED = 'rental_system/rental/qty_rented';

    /** @var string */
    protected $_template = 'catalog/product/qtyrented.phtml';

    /** @var RentalFactory */
    protected $_rentalFactory;

    /** @var Registry */
    protected $_coreRegistry;

    /**
     * QuantityRented constructor.
     *
     * @param RentalFactory $rentalFactory
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        RentalFactory $rentalFactory,
        Context $context,
        array $data = []
    ) {
        $this->_rentalFactory = $rentalFactory;
        $this->_coreRegistry  = $context->getRegistry();
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getCurrentProductId()
    {
        return $this->_coreRegistry->registry('current_product')->getId();
    }

    /**
     * Initiate rental model
     */
    public function getQtyRented()
    {
        $id     = $this->getCurrentProductId();
        $result = $this->_rentalFactory->create()->loadByProductId($id)->getData('qty_rented');

        return $result ?? 0;
    }

    /**
     * @return mixed
     */
    public function enableQtyRented()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_QTY_RENTED);
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return $this->_rentalFactory->create()
            ->loadByProductId($this->getCurrentProductId())
            ->getIdentities();
    }
}
