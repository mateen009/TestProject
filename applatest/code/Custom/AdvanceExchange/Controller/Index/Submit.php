<?php
 
namespace Custom\AdvanceExchange\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Custom\AdvanceExchange\Model\McgSkuFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;
 
class Submit extends Action
{
    protected $resultPageFactory;
    protected $mcgSkuFactory;

 
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        McgSkuFactory $mcgSkuFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->mcgSkuFactory = $mcgSkuFactory;
        parent::__construct($context);
    }
 
    public function execute()
    {
        try {
            //inserting dummy data into table
               $model = $this->mcgSkuFactory->create();
                $model->setProductId('2159');
                $model->setCustomerId('197');
                $model->setSku('RAM-HOL-SAM7PKL2-HARU');
                $model->save();
            //display data from table
                // $load=$this->collection;
                // // $load->addFieldToFilter('sku',21348);
                // echo "<pre>";
                // print_r($load->getData());
                // die("data display Successfully");
               //  die("data inserted");

                $this->messageManager->addSuccessMessage(__("Data Saved Successfully."));
        } catch (\Exception $e) {
            echo 'Data Can not save successfully ',  $e->getMessage(), "\n";
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
 
    }
}