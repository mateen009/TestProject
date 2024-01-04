<?php
namespace Mobility\DemoReminder\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Data helper
 */
class Data extends AbstractHelper
{
    const XML_PATH_MODULE_ENABLED = 'demoreminder/general/enabled';
    const XML_PATH_REMINDER_DAYS_BEFORE = 'demoreminder/general/reminder_days_before';
    const XML_PATH_CRON_TIME = 'demoreminder/general/cron_time';
    const XML_PATH_SENDER_NAME = 'demoreminder/email/sender_name';
    const XML_PATH_SENDER_EMAIL = 'demoreminder/email/sender_email';
    const XML_PATH_CUSTOMER_EMAIL_TEMPLATE = 'demoreminder/email/customer_email_template';
    const XML_PATH_SALESREP_EMAIL_TEMPLATE = 'demoreminder/email/sales_rep_email_template';

    /**
     * @var CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param CollectionFactory $orderCollectionFactory
     * @param StateInterface $inlineTranslation
     * @param Escaper $escaper
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        CollectionFactory $orderCollectionFactory,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
    }

    /*
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_MODULE_ENABLED,
            ScopeConfigInterface::SCOPE_STORE
        );
    }

    /*
     * @return int
     */
    public function getReminderDaysBefore()
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_REMINDER_DAYS_BEFORE,
            ScopeConfigInterface::SCOPE_STORE
        );
    }

    /*
     * @return string
     */
    public function getCronTime()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CRON_TIME,
            ScopeConfigInterface::SCOPE_STORE
        );
    }

    /*
     * @return string
     */
    public function getSenderName()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SENDER_NAME,
            ScopeConfigInterface::SCOPE_STORE
        );
    }

    /*
     * @return string
     */
    public function getSenderEmail()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SENDER_EMAIL,
            ScopeConfigInterface::SCOPE_STORE
        );
    }

    /*
     * @return string
     */
    public function getCustomerEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CUSTOMER_EMAIL_TEMPLATE,
            ScopeConfigInterface::SCOPE_STORE
        );
    }

    /*
     * @return string
     */
    public function getSalesRepEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SALESREP_EMAIL_TEMPLATE,
            ScopeConfigInterface::SCOPE_STORE
        );
    }

    /*
     * @return mixed
     */
    public function getDemoOrdersCollection()
    {
        $collection = $this->orderCollectionFactory->create()
         ->addFieldToSelect('*')
         ->addFieldToFilter('demo_reminder', ['eq' => 0]);

        return $collection;
    }

    /*
     * @return void
     */
    public function sendCustomerEmail($salesRepName, $salesRepEmail, $customerName, $orderId, $expireDate)
    {
        try {
            $this->inlineTranslation->suspend();
            $sender = [
                'name' => $this->escaper->escapeHtml($this->getSenderName()),
                'email' => $this->escaper->escapeHtml($this->getSenderEmail()),
            ];
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($this->getCustomerEmailTemplate())
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars([
                    'customerName'  => $customerName,
                    'salesRepName'  => $salesRepName,
                    'orderId' => $orderId,
                    'expireDate' => $expireDate
                ])
                ->setFrom($sender)
                ->addTo($salesRepEmail, $salesRepName)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    /*
     * @return void
     */
    public function sendSalesRepEmail($customerName, $customerEmail, $orderId, $expireDate)
    {
        try {
            $this->inlineTranslation->suspend();
            $sender = [
                'name' => $this->escaper->escapeHtml($this->getSenderName()),
                'email' => $this->escaper->escapeHtml($this->getSenderEmail()),
            ];
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($this->getCustomerEmailTemplate())
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars([
                    'customerName'  => $customerName,
                    'orderId' => $orderId,
                    'expireDate' => $expireDate
                ])
                ->setFrom($sender)
                ->addTo($customerEmail, $customerName)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    public function getLogger($error, $message) 
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/DemoReminder.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info($error . " : " . $message);
    }
}
