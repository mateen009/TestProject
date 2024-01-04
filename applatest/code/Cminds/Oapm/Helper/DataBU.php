<?php

namespace Cminds\Oapm\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Translate\Inline\StateInterface;
// use Magento\Framework\Mail\Template\TransportBuilder;
use Cminds\Oapm\Model\Mail\TransportBuilder;
use Magento\Framework\Url;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Store\Model\ScopeInterface;
use Mobility\QuoteRequest\Model\QuoteRequestFactory;
use Mobility\QuoteRequest\Model\ResourceModel\QuoteRequest;
use Webhive\MultiVendor\Plugin\Order;

class Data extends AbstractHelper
{
    const EMAIL_ORDER_PLACED_PAYER = 'order_placed_payer';
    const EMAIL_ORDER_PAYED_PAYER = 'order_payed_payer';
    const EMAIL_ORDER_CANCELED_PAYER = 'order_canceled_payer';
    const EMAIL_ORDER_PENDING_REMINDER_PAYER = 'order_pending_last_reminder_payer';
    const EMAIL_ORDER_PENDING_LAST_REMINDER_PAYER = 'order_pending_last_reminder_payer';

    const EMAIL_ORDER_PLACED_CREATOR = 'order_placed_creator';
    const EMAIL_ORDER_PAYED_CREATOR = 'order_payed_creator';
    const EMAIL_ORDER_CANCELED_CREATOR = 'order_canceled_creator';
    const EMAIL_ORDER_PENDING_LAST_REMINDER_CREATOR = 'order_pending_last_reminder_creator';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Mobility\QuoteRequest\Model\QuoteRequestFactory
     */
    protected $quoteRequestFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Cminds\Oapm\Model\Mail\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     */
    protected $addressRenderer;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem,
        QuoteRequestFactory $quoteRequestFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        Url $urlBuilder,
        LoggerInterface $logger,
        Renderer $addressRenderer
    ) {
        $this->storeManager = $storeManager;
        $this->_filesystem = $filesystem;
        $this->quoteRequestFactory = $quoteRequestFactory;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->urlBuilder = $urlBuilder;
        $this->addressRenderer = $addressRenderer;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Return email template xml id.
     *
     * @param string $type
     * @param string $storeId
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getTemplateId($type, $storeId)
    {
        switch ($type) {
            case self::EMAIL_ORDER_PLACED_PAYER:
            case self::EMAIL_ORDER_PAYED_PAYER:
            case self::EMAIL_ORDER_CANCELED_PAYER:
            case self::EMAIL_ORDER_PENDING_REMINDER_PAYER:
            case self::EMAIL_ORDER_PENDING_LAST_REMINDER_PAYER:
            case self::EMAIL_ORDER_PLACED_CREATOR:
            case self::EMAIL_ORDER_PAYED_CREATOR:
            case self::EMAIL_ORDER_CANCELED_CREATOR:
            case self::EMAIL_ORDER_PENDING_LAST_REMINDER_CREATOR:
                $configPath = sprintf('payment/cminds_oapm/%s_notification_email', $type);
                break;
            default:
                throw new \Magento\Framework\Exception\LocalizedException(__('Unsupported template type.'));
                break;
        }

        $configData = $this->scopeConfig->getValue(
            $configPath,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $configData ?: '';
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return string|null
     */
    protected function getFormattedShippingAddress($order)
    {
        return $order->getIsVirtual()
            ? null
            : $this->addressRenderer->format($order->getShippingAddress(), 'html');
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return string|null
     */
    protected function getFormattedBillingAddress($order)
    {
        return $this->addressRenderer->format($order->getBillingAddress(), 'html');
    }

    /**
     * Send email.
     *
     * @param   string $type
     * @param   array $recipient
     * @param   array $data
     * @return  \Cminds\Oapm\Helper\Data
     */
    protected function sendEmail($type, $recipient, $data)
    {
        $storeId = $this->storeManager->getStore()->getId();

        $this->inlineTranslation->suspend();

        $this->transportBuilder
            ->setTemplateIdentifier($this->getTemplateId($type, $storeId))
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId
                ]
            )->setFrom(
                $this->scopeConfig->getValue(
                    \Magento\Sales\Model\Order\Email\Container\OrderIdentity::XML_PATH_EMAIL_IDENTITY,
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                )
            )->setTemplateVars($data)
            ->addTo(
                $recipient['email'],
                $recipient['name']
            );
        $transport = $this->transportBuilder->getTransport();
        try {
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        $this->inlineTranslation->resume();

        return $this;
    }

    /**
     * Send notification to order creator that order has been placed using OAPM payment method.
     *
     * @param   array $recipientData
     * @return  \Cminds\Oapm\Helper\Data
     */
    public function sendOrderPlacedCreatorNotification($recipientData)
    {
        $storeId = $this->storeManager->getStore()->getWebsiteId();
        if ($storeId == '1') {
            $default = true;
        } elseif ($storeId == '3') {
            $default = false;
        }
        $this->sendEmail(
            self::EMAIL_ORDER_PLACED_CREATOR,
            [
                'email' => $recipientData['creator_email'],
                'name' => $recipientData['creator_name']
            ],
            [
                'default' => $default,
                'quoteCustomer' => false,
                'creator_name' => $recipientData['creator_name'],
                'payer_name' => $recipientData['payer_name'],
                'payer_email' => $recipientData['payer_email'],
                'order' => $recipientData['order'],
                'formattedShippingAddress' => $this->getFormattedShippingAddress($recipientData['order']),
                'formattedBillingAddress' => $this->getFormattedBillingAddress($recipientData['order'])
            ]
        );

        $order = $recipientData['order'];
        if ($order->getData('customer_approval_status') != 'approved' && $order->getData('approval_1_status') == 'approved' && $order->getData('approval_2_status') == 'approved' && empty($order->getNsInternalId())) {
            $quote_id = $order->getQuoteId();
            $formQuoteCollection = $this->quoteRequestFactory->create()->getCollection();
            $formQuote = $formQuoteCollection->addFieldToFilter('quote_id', $quote_id)->getFirstItem();
            $customer = $this->_customerRepositoryInterface->getById($order->getCustomerId());
            $isCustomerApproval = $customer->getCustomAttribute('customer_approval');
            if ($isCustomerApproval) {
                $isCustomerApprovalRequired = (int)$isCustomerApproval->getValue();
                if ($isCustomerApprovalRequired) {
                    if ($formQuote->getCustomerEmail()) {
                        $order->setData('customer_approval_status', 'requested');
                        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        $customerToken = substr(str_shuffle($permitted_chars), 0, 20) . substr(str_shuffle($permitted_chars), 0, 20);
                        $order->setData('customer_approval_token', $customerToken);
                        $order->setData('customer_approval_token_status', 'not_expired');
                        $this->sendOrderPlacedCustomerNotification($recipientData, $customerToken, $formQuote);
                        $order->setData('customer_approval_email', 'sent');
                        $order->save();
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Send notification to order customer that order has been placed using OAPM payment method.
     *
     * @param   array $recipientData
     * @return  \Cminds\Oapm\Helper\Data
     */
    public function sendOrderPlacedCustomerNotification($recipientData, $token, $customerEmail)
    {
        $storeId = $this->storeManager->getStore()->getWebsiteId();

        if ($storeId == '1') {
            $default = true;
        } elseif ($storeId == '3') {
            $default = false;
        }

        $this->sendCustomerEmail(
            9,
            [
                'email' => $customerEmail->getCustomerEmail(),
                'name' => $customerEmail->getCustomerName()
            ],
            [
                'default' => $default,
                'quote_customer' => true,
                'token' => $token,
                'creator_name' => $recipientData['creator_name'],
                'order' => $recipientData['order'],
                'formattedShippingAddress' => $this->getFormattedShippingAddress($recipientData['order']),
                'formattedBillingAddress' => $this->getFormattedBillingAddress($recipientData['order'])
            ]
        );

        return $this;
    }


    /**
     * Send notification to person which has been marked
     * to pay for the order placed using OAPM payment method.
     *
     * @param   array $recipientData
     * @return  \Cminds\Oapm\Helper\Data
     */
    public function sendOrderPlacedPayerNotification($recipientData)
    {
        $this->sendEmail(
            self::EMAIL_ORDER_PLACED_PAYER,
            [
                'email' => $recipientData['payer_email'],
                'name' => $recipientData['payer_name']
            ],
            [
                'creator_name' => $recipientData['creator_name'],
                'creator_email' => $recipientData['creator_email'],
                'payer_name' => $recipientData['payer_name'],
                'payer_note' => $recipientData['payer_note'],
                'checkout_url' => $recipientData['checkout_url'],
                'cancel_url' => $recipientData['cancel_url'],
                'order' => $recipientData['order'],
                'formattedShippingAddress' => $this->getFormattedShippingAddress($recipientData['order']),
                'formattedBillingAddress' => $this->getFormattedBillingAddress($recipientData['order'])
            ]
        );

        return $this;
    }

    /**
     * Send notification to order creator that order has been payed.
     *
     * @param   array $recipientData
     * @return  \Cminds\Oapm\Helper\Data
     */
    public function sendOrderPayedCreatorNotification($recipientData)
    {
        $this->sendEmail(
            self::EMAIL_ORDER_PAYED_CREATOR,
            [
                'email' => $recipientData['creator_email'],
                'name' => $recipientData['creator_name']
            ],
            []
        );

        return $this;
    }

    /**
     * Send notification to order payer that order has been payed.
     *
     * @param   array $recipientData
     * @return  \Cminds\Oapm\Helper\Data
     */
    public function sendOrderPayedPayerNotification($recipientData)
    {
        $this->sendEmail(
            self::EMAIL_ORDER_PAYED_PAYER,
            [
                'email' => $recipientData['payer_email'],
                'name' => $recipientData['payer_name']
            ],
            [
                'creator_name' => $recipientData['creator_name']
            ]
        );

        return $this;
    }

    /**
     * Send notification to order creator that order has been canceled.
     *
     * @param   array $recipientData
     * @return  \Cminds\Oapm\Helper\Data
     */
    public function sendOrderCanceledCreatorNotification($recipientData)
    {
        $this->sendEmail(
            self::EMAIL_ORDER_CANCELED_CREATOR,
            [
                'email' => $recipientData['creator_email'],
                'name' => $recipientData['creator_name']
            ],
            [
                'payer_name' => $recipientData['payer_name']
            ]
        );

        return $this;
    }

    /**
     * Send notification to order payer that order has been canceled.
     *
     * @param   array $recipientData
     * @return  \Cminds\Oapm\Helper\Data
     */
    public function sendOrderCanceledPayerNotification($recipientData)
    {
        $this->sendEmail(
            self::EMAIL_ORDER_CANCELED_PAYER,
            [
                'email' => $recipientData['payer_email'],
                'name' => $recipientData['payer_name']
            ],
            [
                'creator_name' => $recipientData['creator_name']
            ]
        );

        return $this;
    }

    /**
     * Return checkout url.
     *
     * @param   string $hash
     * @return  string
     */
    public function getCheckoutUrl($hash)
    {
        return $this->urlBuilder->getUrl('oapm/checkout/finalize', [
            'order' => $hash,
            '_nosid' => true  //prevents sessionId from getting added
        ]);
    }

    /**
     * Return cancel url.
     *
     * @param   string $hash
     * @return  string
     */
    public function getCancelUrl($hash)
    {
        return $this->urlBuilder->getUrl('oapm/checkout/cancel', [
            'order' => $hash,
            '_nosid' => true  //prevents sessionId from getting added
        ]);
    }

    /**
     * Send reminder to person which has been marked
     * to pay for the order placed using OAPM payment method.
     *
     * @param   array $recipientData
     * @return  \Cminds\Oapm\Helper\Data
     */
    public function sendOrderPendingReminderPayerNotification($recipientData)
    {
        $this->sendEmail(
            self::EMAIL_ORDER_PENDING_REMINDER_PAYER,
            [
                'email' => $recipientData['payer_email'],
                'name' => $recipientData['payer_name']
            ],
            [
                'creator_name' => $recipientData['creator_name'],
                'creator_email' => $recipientData['creator_email'],
                'payer_name' => $recipientData['payer_name'],
                'checkout_url' => $recipientData['checkout_url'],
                'cancel_url' => $recipientData['cancel_url'],
                'order' => $recipientData['order']
            ]
        );

        return $this;
    }

    /**
     * Send last reminder to person which has been marked
     * to pay for the order placed using OAPM payment method.
     *
     * @param   array $recipientData
     * @return  \Cminds\Oapm\Helper\Data
     */
    public function sendOrderPendingLastReminderPayerNotification($recipientData)
    {
        $this->sendEmail(
            self::EMAIL_ORDER_PENDING_LAST_REMINDER_PAYER,
            [
                'email' => $recipientData['payer_email'],
                'name' => $recipientData['payer_name']
            ],
            [
                'creator_name' => $recipientData['creator_name'],
                'creator_email' => $recipientData['creator_email'],
                'payer_name' => $recipientData['payer_name'],
                'checkout_url' => $recipientData['checkout_url'],
                'cancel_url' => $recipientData['cancel_url'],
                'order' => $recipientData['order']
            ]
        );

        return $this;
    }

    /**
     * Send last reminder to order creator for the order placed using OAPM payment method.
     *
     * @param   array $recipientData
     * @return  \Cminds\Oapm\Helper\Data
     */
    public function sendOrderPendingLastReminderCreatorNotification($recipientData)
    {
        $this->sendEmail(
            self::EMAIL_ORDER_PENDING_LAST_REMINDER_CREATOR,
            [
                'email' => $recipientData['creator_email'],
                'name' => $recipientData['creator_name']
            ],
            [
                'creator_name' => $recipientData['creator_name'],
                'creator_email' => $recipientData['creator_email'],
                'payer_name' => $recipientData['payer_name'],
                'payer_email' => $recipientData['payer_email']
            ]
        );

        return $this;
    }

    /**
     * Send last reminder to order creator for the order placed using OAPM payment method.
     *
     * @param   array $recipientData
     * @return  \Cminds\Oapm\Helper\Data
     */
    public function sendCustomerEmail($id, $recipient, $data)
    {
        $filePath = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath().'termsandcondition/';
        $fileName = 'termsandconditions.pdf';
        $file = $filePath.$fileName;
        $templateId = $id; // template id

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('text message');
        $logger->info($file);

        try {
            $storeId = $this->storeManager->getStore()->getId();

            $this->inlineTranslation->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $storeId
                    ]
                )
                ->setFrom(
                    $this->scopeConfig->getValue(
                        \Magento\Sales\Model\Order\Email\Container\OrderIdentity::XML_PATH_EMAIL_IDENTITY,
                        ScopeInterface::SCOPE_STORE,
                        $storeId
                    )
                )->setTemplateVars($data)
               // ->addAttachment(file_get_contents($file))
                ->addAttachment($filePath, $fileName)
                ->addTo(
                    $recipient['email'],
                    $recipient['name']
                )
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
        }
    }
}
