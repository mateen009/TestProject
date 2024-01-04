<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\RentalSystem\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * @method getPickupAddress()
 * @method getLeadTime()
 * @method getProductId()
 * @method getQtyRented()
 * @method getMaxDuration()
 * @method getHold()
 */
class Rental extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'magenest_rentalsystem_rental';

    /**
     * Product Type Code
     */
    const PRODUCT_TYPE = 'rental';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magenest\RentalSystem\Model\ResourceModel\Rental::class);
    }

    /**
     * @param $id
     *
     * @return Rental
     */
    public function loadByProductId($id)
    {
        return $this->load($id, 'product_id');
    }

    /**
     * @param $id
     *
     * @return string
     */
    public function getEmailTemplate($id)
    {
        return $this->load($id)->getData('email_template');
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        $identities = [];
        if ($id = $this->getId()) {
            $identities[] = self::CACHE_TAG . '_' . $id;
        }
        return $identities;
    }
}
