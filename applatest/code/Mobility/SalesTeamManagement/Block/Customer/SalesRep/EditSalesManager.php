<?php
namespace Mobility\SalesTeamManagement\Block\Customer\SalesRep;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;

/**
 * Main Edit block
 */
class EditSalesManager extends Template
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    protected $customer;

    /**
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CustomerRepositoryInterface $customerRepository,
        Customer $customer,
        array $data = []
        )
    {
        parent::__construct($context, $data);
        $this->customerRepository = $customerRepository;
        $this->customer = $customer;
    }

    public function getPostActionUrl()
    {
        return $this->getUrl('customer/salesmanager/post');
    }

    public function getCustomer($customerId)
    {
       // return $this->customerRepository->getById($customerId);
        $cData = $this->customer->load($customerId);
        return $cData;
    }
}

