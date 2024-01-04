<?php

namespace AscentDigital\Reports\Controller\Adminhtml\Reports;

use Magento\Framework\Controller\ResultFactory;

class EmailFilter extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Action\Contex
     */
    private $context;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $_customerFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        parent::__construct($context);
        $this->_customerFactory = $customerFactory;
        $this->context = $context;
    }

    /**
     * @return json
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        // sales rep start
        $email = $this->context->getRequest()->getParam('salesrep_email');
        if (isset($email) && !empty($email)) {
            $email_dropdown = $this->getSalesRepEmailDropDown($email);
            if ($email_dropdown) {
                $resultJson->setData(["customers" => $email_dropdown, "success" => true]);
            } else {
                $resultJson->setData(["success" => false]);
            }
            return $resultJson;
        }
        // sales rep end

        // sales Manager start
        $email = $this->context->getRequest()->getParam('salesmanager_email');
        if (isset($email) && !empty($email)) {
            $email_dropdown = $this->getSalesManagerEmailDropDown($email);
            if ($email_dropdown) {
                $resultJson->setData(["customers" => $email_dropdown, "success" => true]);
            } else {
                $resultJson->setData(["success" => false]);
            }
            return $resultJson;
        }
        // sales Manager end

        // Territory Manager start
        $email = $this->context->getRequest()->getParam('territorymanager_email');
        if (isset($email) && !empty($email)) {
            $email_dropdown = $this->getTerritoryManagerEmailDropDown($email);
            if ($email_dropdown) {
                $resultJson->setData(["customers" => $email_dropdown, "success" => true]);
            } else {
                $resultJson->setData(["success" => false]);
            }
            return $resultJson;
        }
        // Territory Manager end

        // Executive Manager start
        $email = $this->context->getRequest()->getParam('executivemanager_email');
        if (isset($email) && !empty($email)) {
            $email_dropdown = $this->getExecManagerEmailDropDown($email);
            if ($email_dropdown) {
                $resultJson->setData(["customers" => $email_dropdown, "success" => true]);
            } else {
                $resultJson->setData(["success" => false]);
            }
            return $resultJson;
        }
        // Executive Manager end
    }


    // return sales rep email dropdown
    public function getSalesRepEmailDropDown($email)
    {
        $collection = $this->_customerFactory->create()->getCollection()
            ->addFieldToSelect('email') ->addFieldToFilter('Customer_Type', '1')
            ->addFieldToFilter('email', array('like' => '%' . $email . '%'));
            $collection->getSelect()->limit(10);
        $customers = array();
        foreach ($collection as $customer) {
            $customers[] = "<a onclick='setSRValueOfInput(this)'>".$customer->getEmail()."</a>";
        }
        if (count($customers) > 0) {
            $email_dropdown = implode(' ', $customers);
            return $email_dropdown;
        } else {
            return false;
        }
    }

    // return sales manager email dropdown
    public function getSalesManagerEmailDropDown($email)
    {
        $collection = $this->_customerFactory->create()->getCollection()
            ->addFieldToSelect('email') ->addFieldToFilter('Customer_Type', '3')
            ->addFieldToFilter('email', array('like' => '%' . $email . '%'));
            $collection->getSelect()->limit(10);
        $customers = array();
        foreach ($collection as $customer) {
            $customers[] = "<a onclick='setSMValueOfInput(this)'>".$customer->getEmail()."</a>";
        }
        if (count($customers) > 0) {
            $email_dropdown = implode(' ', $customers);
            return $email_dropdown;
        } else {
            return false;
        }
    }

    // return sales rep email dropdown
    public function getTerritoryManagerEmailDropDown($email)
    {
        $collection = $this->_customerFactory->create()->getCollection()
            ->addFieldToSelect('email') ->addFieldToFilter('Customer_Type', '4')
            ->addFieldToFilter('email', array('like' => '%' . $email . '%'));
            $collection->getSelect()->limit(10);
        $customers = array();
        foreach ($collection as $customer) {
            $customers[] = "<a onclick='setTMValueOfInput(this)'>".$customer->getEmail()."</a>";
        }
        if (count($customers) > 0) {
            $email_dropdown = implode(' ', $customers);
            return $email_dropdown;
        } else {
            return false;
        }
    }

    // return sales rep email dropdown
    public function getExecManagerEmailDropDown($email)
    {
        $collection = $this->_customerFactory->create()->getCollection()
            ->addFieldToSelect('email') ->addFieldToFilter('Customer_Type', '5')
            ->addFieldToFilter('email', array('like' => '%' . $email . '%'));
            $collection->getSelect()->limit(10);
        $customers = array();
        foreach ($collection as $customer) {
            $customers[] = "<a onclick='setEMValueOfInput(this)'>".$customer->getEmail()."</a>";
        }
        if (count($customers) > 0) {
            $email_dropdown = implode(' ', $customers);
            return $email_dropdown;
        } else {
            return false;
        }
    }


}
