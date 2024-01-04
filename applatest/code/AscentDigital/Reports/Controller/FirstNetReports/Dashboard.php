<?php
/**
 *
 * @package   SK\CustomerAccountTab
 * @author    Kishan Savaliya <kishansavaliyakb@gmail.com>
 */

namespace AscentDigital\Reports\Controller\FirstNetReports;

class Dashboard extends \Magento\Framework\App\Action\Action
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory

    ) {
      

       
        $this->_customerSession = $customerSession;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
    public function execute()
    {
        $customerType = $this->_customerSession->getCustomerType();
        $resultPage = $this->resultPageFactory->create();
        //if customer type is Sales Manager or territory Manager or Excutive manager then this dashboard controller shows 
        if($customerType==3||$customerType==4||$customerType==5){
        $resultPage->getConfig()->getTitle()->set(__('Dashboard'));
        return $resultPage;
       
    }
    else{
        //if customer type is sales Rep then redirect to  my account page 
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('customer/account/');
        return $resultRedirect;
    }

        
    } 
}