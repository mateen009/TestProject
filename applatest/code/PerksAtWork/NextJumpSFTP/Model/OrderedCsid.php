<?php

namespace PerksAtWork\NextJumpSFTP\Model;

use Magento\Framework\Model\AbstractModel;

class OrderedCsid extends AbstractModel{
    protected function _construct(){
        $this->_init('PerksAtWork\NextJumpSFTP\Model\ResourceModel\OrderedCsid');
    }
}
