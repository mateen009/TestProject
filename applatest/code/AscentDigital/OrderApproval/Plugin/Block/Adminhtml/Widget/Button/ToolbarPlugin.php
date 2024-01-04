<?php declare(strict_types=1);

namespace AscentDigital\OrderApproval\Plugin\Block\Adminhtml\Widget\Button;

use Magento\Sales\Block\Adminhtml\Order\Create;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\Toolbar as ToolbarContext;

class ToolbarPlugin
{
    /**
     * @param ToolbarContext $toolbar
     * @param AbstractBlock $context
     * @param ButtonList $buttonList
     * @return array
     */
    
    public function beforePushButtons(
        ToolbarContext $toolbar,
        AbstractBlock $context,
        ButtonList $buttonList
    ): array {
        $order = false;
        $nameInLayout = $context->getNameInLayout();
        if ('sales_order_edit' == $nameInLayout) {
            $order = $context->getOrder();
        }
        
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
         
        $urlBuilder = $objectManager->get('\Magento\Framework\UrlInterface');
        $message="Are you sure you want to sent order to netsuite?";
        if ($order) {
            if($order->getNsInternalId()==''){
                
                $url =
                $urlBuilder->getUrl('orderapprovaladmin/order/order');
        
        
                
                
                $finalUrl = $url.'order_id/'.$order->getId();
                
                $buttonList->add(
                                 'my_button',
                                 [
                                 'label' => __('Send To Netsuite'),
                                 'onclick' => "confirmSetLocation('{$message}', '{$finalUrl}')",
                                 'id' => 'netsuite_orders'
                                 ]
                                 );
            }
            
        }

        return [$context, $buttonList];
    }
}
