<?php   
namespace Custom\AdvanceExchange\Block\Manage;

class EmptyOrder extends \Magento\Framework\View\Element\Template
{ 
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        
        $this->_messageManager = $messageManager;
        $this->request = $request;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }  
    public function getErrorMessage()   
    { 
        $this->_messageManager->addError(__("Order Not Found"));
        return ;
    }   
    public function _prepareLayout()
    {
        $id =$this->request->getParam('ae_id');
        // print_r($id);die('matee')
        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        if ($breadcrumbsBlock) {

            $breadcrumbsBlock->addCrumb(
                'Advance Exchange',
                [
                'label' => __('Advance Exchange'), //lable on breadCrumbes
                'title' => __('Advance Exchange'),
                'link' => $baseUrl.('advanceexchange/manage/detail').'?ae_id='.$id
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'Order Detail',
                [
                'label' => __('Order Detail'),
                'title' => __('Order Detail'),
                'link' => '' //set link path
                ]
            );
        }
        // $this->pageConfig->getTitle()->set(__('FAQ')); // set page name
        return parent::_prepareLayout();
    }
}   