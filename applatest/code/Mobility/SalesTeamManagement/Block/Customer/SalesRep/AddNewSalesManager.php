<?php
namespace Mobility\SalesTeamManagement\Block\Customer\SalesRep;

use Magento\Framework\View\Element\Template;

/**
 * Main AddNew block
 */
class AddNewSalesManager extends Template
{
    /**
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        array $data = [])
    {
        parent::__construct($context, $data);
    }

    public function getPostActionUrl()
    {
        return $this->getUrl('customer/salesmanager/post');
    }
}

