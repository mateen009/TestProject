<?php
namespace Cminds\Oapm\Model\ResourceModel\Order;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource
     *
     */
    protected function _construct()
    {
        $this->_init('Cminds\Oapm\Model\Order', 'Cminds\Oapm\Model\ResourceModel\Order');
    }

    /**
     * Filter collection by status.
     *
     * @param   int $status
     * @return  \Cminds\Oapm\Model\ResourceModel\Order\Collection
     */
    public function filterByStatus($status)
    {
        $this->addFieldToFilter('status', $status);

        return $this;
    }
}
