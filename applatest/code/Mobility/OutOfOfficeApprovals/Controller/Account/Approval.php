<?php
namespace Mobility\OutOfOfficeApprovals\Controller\Account;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ResourceConnection;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Approval extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CustomerRepositoryInterface $customerRepository,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * Post user question
     *
     * @return Redirect
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->resultRedirectFactory->create()->setPath('customer/account/index');
        }

        $customerId = $this->customerSession->getCustomer()->getId();
        $customer = $this->customerRepository->getById($customerId);
        $data = (array) $this->getRequest()->getPost();
        // echo "<pre>"; print_r($data);die;
        
        try {
            if (isset($data['ooo_enabled']) && $data['ooo_enabled'] == 'on' && $this->validatedParams()) {
                $customer->setCustomAttribute('ooo_enabled', 1);
                $customer->setCustomAttribute('ooo_startdate', date('Y-m-d', strtotime($data['ooo_startdate'])));
                $customer->setCustomAttribute('ooo_enddate', date('Y-m-d', strtotime($data['ooo_enddate'])));
                $customer->setCustomAttribute('ooo_email', $data['ooo_email']);
            } else {
                $customer->setCustomAttribute('ooo_enabled', 0);
                $customer->setCustomAttribute('ooo_startdate', '');
                $customer->setCustomAttribute('ooo_enddate', '');
                $customer->setCustomAttribute('ooo_email', '');
            }
            
            $this->customerRepository->save($customer);

        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __('An error occurred while processing your form. Please try again later.')
            );
        }
        return $this->resultRedirectFactory->create()->setPath('customer/account/index');
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function validatedParams()
    {
        $request = $this->getRequest();
        if ($request->getParam('ooo_enabled') === 'on') {
            if (trim($request->getParam('ooo_startdate')) === '') {
                throw new LocalizedException(__('Enter the start date and try again.'));
            }
            if (trim($request->getParam('ooo_enddate')) === '') {
                throw new LocalizedException(__('Enter the end date and try again.'));
            }
            if (false === \strpos($request->getParam('ooo_email'), '@')) {
                throw new LocalizedException(__('The email address is invalid. Verify the email address and try again.'));
            }
        }
        
        return $request->getParams();
    }
}
