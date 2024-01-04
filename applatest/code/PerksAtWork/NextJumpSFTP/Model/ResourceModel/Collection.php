<?php

namespace PerksAtWork\NextJumpSFTP\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
class Collection extends AbstractCollection{
    
    protected function _construct(){
        $this->_init(
            'PerksAtWork\NextJumpSFTP\Model\OrderedCsid',
            'PerksAtWork\NextJumpSFTP\Model\ResourceModel\OrderedCsid'
        );
    }
}
