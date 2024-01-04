<?php

namespace AscentDigital\SalesForce\Model;



class SalesForce extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('\AscentDigital\SalesForce\Model\ResourceModel\SalesForce');
    }

}
