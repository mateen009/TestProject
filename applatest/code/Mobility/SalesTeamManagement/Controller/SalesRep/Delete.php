<?php
namespace Mobility\SalesTeamManagement\Controller\SalesRep;

use Magento\Framework\App\Action\Action;

class Delete extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;
    /**
     * @var Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\ResultFactory $pageFactory,
        \Magento\Customer\Model\CustomerFactory $customerfactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->_pageFactory = $pageFactory;
        $this->customerFactory = $customerfactory;
        $this->registry = $registry;
        return parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        try {

            $id = $this->getRequest()->getParam('id');

            if (isset($id) && $id > 0) {
                $this->registry->register('isSecureArea', true);
                $customer = $this->customerFactory->create()->load($id);
                $customer->delete();
                $this->messageManager->addSuccessMessage(
                    __('Sales Rep deleted successfully!')
                );
                $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
                $redirect->setUrl('https://caad034362.nxcli.net/firstnet/customer/salesrep/index');
                return $redirect;
            } else {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong!')
                );
                $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
                $redirect->setUrl('https://caad034362.nxcli.net/firstnet/customer/salesrep/index');
                return $redirect;
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred while Deleting Sales Rep. Please try again later.')
            );
        }
    }
}
