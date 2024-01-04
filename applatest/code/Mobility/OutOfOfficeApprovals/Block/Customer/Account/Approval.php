<?php
namespace Mobility\OutOfOfficeApprovals\Block\Customer\Account;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Main OOO Approval block
 */
class Approval extends Template
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @param Template\Context $context
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerSession $customerSession
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CustomerRepositoryInterface $customerRepository,
        CustomerSession $customerSession,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
    }

    /**
     * Returns action url for quote form
     *
     * @return string
     */
    public function getStatusAction($id, $status)
    {
        return $this->getUrl('quote/status/update', ['_secure' => true, 'id' => $id, 'status' => $status]);
    }

    /**
     * Returns customer data
     *
     * @return mixed
     */
    public function getCustomer()
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        return $this->customerRepository->getById($customerId);
    }
}
