<?php


namespace Mobility\QuoteRequest\Helper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;

class Mail extends AbstractHelper
{
    protected $transportBuilder;
    protected $storeManager;
    protected $inlineTranslation;
    protected $scopeConfig;

    public function __construct(
        Context $context,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        StateInterface $state,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $state;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function sendEmail($from, $to, $data, $templateId)
    {

            $name='name';

            /* email sending code */
            $templateId = $templateId; // template id
            // $fromEmail = $this->scopeConfig->getValue('trans_email/ident_sales/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);  // sender Email id
            // $fromName = $this->scopeConfig->getValue('trans_email/ident_sales/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);             // store and sender Name
            $fromEmail = 'yasirminhaj@yahoo.com';
            $fromName = 'name';
            $toEmail = 'yasirminhaj989@gmail.com'; // receiver email id
//        $customerName=$customerName;


                try {
                    // template variables pass here
                    $templateVars = array(
                        'customer_name'=>$name,
                        'order'=>'order'
                    );

                    $storeId = $this->storeManager->getStore()->getId();

                    $from = ['email' => $fromEmail, 'name' => $fromName];
                    $this->inlineTranslation->suspend();

                    $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                    $templateOptions = [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $storeId
                    ];
                    $transport = $this->transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                        ->setTemplateOptions($templateOptions)
                        ->setTemplateVars($templateVars)
                        ->setFrom($from)
                        ->addTo($toEmail)
                        ->addBcc('yasir_webhive@yahoo.com')
                        ->getTransport();
                    $transport->sendMessage();
                    $this->inlineTranslation->resume();
                } catch (\Exception $e) {
                    $this->_logger->info($e->getMessage());
                }
                /* email sending code end */


    }
}

