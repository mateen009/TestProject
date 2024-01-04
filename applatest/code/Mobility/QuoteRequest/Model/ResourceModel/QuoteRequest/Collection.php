<?php
namespace Mobility\QuoteRequest\Model\ResourceModel\QuoteRequest;

use Mobility\QuoteRequest\Api\Data\QuoteRequestInterface;
use Mobility\QuoteRequest\Model\ResourceModel\QuoteRequest as QuoteRequestResource;
use Mobility\QuoteRequest\Model\QuoteRequest as QuoteRequestModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
	protected $_idFieldName = QuoteRequestInterface::ID;
	protected $_eventPrefix = 'mobility_quote_request_collection';
	protected $_eventObject = 'quote_request_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init(QuoteRequestModel::class, 
			QuoteRequestResource::class);
	}

}