<?php
namespace AscentDigital\SalesForce\Model\ResourceModel\SalesForce;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('AscentDigital\SalesForce\Model\SalesForce', 'AscentDigital\SalesForce\Model\ResourceModel\SalesForce');
	}

}