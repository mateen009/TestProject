<?php
namespace Mobility\SalesTeamManagement\Block\Customer\SalesRep;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
/**
 * Main Index block
 */
class SalesManagerIndex extends Template
{
    private $customerFactory;
    private $customerSession;

    /**
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CustomerFactory $customerFactory,
        CustomerSession $customerSession,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
    }

    public function getCustomerCollection()
    {
        
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest(
            
        )->getParam('limit') : 10;
        
        return $this->customerFactory->create()
                    ->getCollection()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('TerritoryManager_ID', $this->customerSession->getCustomerId())->addAttributeToFilter('Customer_Type', 3)->setPageSize($pageSize)->setCurPage($page);
    }

    /**
     * @inheritDoc
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCustomerCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'customer.salesmanager.history.pager'
            )->setCollection(
                $this->getCustomerCollection()
            );
            $this->setChild('pager', $pager);
            $this->getCustomerCollection()->load();
        }
        return $this;
    }

    /**
     * Get Pager child block output
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getAddNewUrl()
    {
        return $this->getUrl('customer/salesmanager/addnew');
    }

    public function getEditUrl($id)
    {
        return $this->getUrl('customer/salesmanager/edit', ['id' => $id]);
    }

    public function getDeleteUrl($id)
    {
        return $this->getUrl('customer/salesmanager/delete', ['id' => $id]);
    }
}

