<?php
namespace Mobility\QuoteRequest\Model\ResourceModel;

use Mobility\QuoteRequest\Api\Data\QuoteRequestInterface;

class QuoteRequest extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	) {
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init(QuoteRequestInterface::MAIN_TABLE, QuoteRequestInterface::ID);
	}
}
