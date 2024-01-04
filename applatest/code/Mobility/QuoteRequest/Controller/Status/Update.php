<?php
namespace Mobility\QuoteRequest\Controller\Status;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Mobility\QuoteRequest\Api\Data\QuoteRequestInterface;
use Mobility\QuoteRequest\Api\QuoteRequestRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ResourceConnection;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Mobility\QuoteRequest\Model\ConfigInterface;
use Mobility\QuoteRequest\Model\MailInterface;

class Update extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var QuoteRequestRepositoryInterface
     */
    private $quoteRequestRepository;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ConfigInterface
     */
    private $quoteRequestConfig;

    /**
     * @var MailInterface
     */
    private $mail;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param QuoteRequestRepositoryInterface $quoteRequestRepository
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param ResourceConnection $resourceConnection
     * @param CustomerRepositoryInterface $customerRepository
     * @param ConfigInterface $quoteRequestConfig
     * @param MailInterface $mail
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        QuoteRequestRepositoryInterface $quoteRequestRepository,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        ResourceConnection $resourceConnection,
        CustomerRepositoryInterface $customerRepository,
        ConfigInterface $quoteRequestConfig,
        MailInterface $mail,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context);
        $this->quoteRequestRepository = $quoteRequestRepository;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->resourceConnection = $resourceConnection;
        $this->customerRepository = $customerRepository;
        $this->quoteRequestConfig = $quoteRequestConfig;
        $this->mail = $mail;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * Post user question
     *
     * @return Redirect
     */
    public function execute()
    {
        try {
            $id = (int)$this->getRequest()->getParam(QuoteRequestInterface::ID);
            $status = (string)$this->getRequest()->getParam(QuoteRequestInterface::STATUS);
            $customerType = (int)$this->customerSession->getCustomerType();

            if ($id && $status) {
                $quoteRequest = $this->quoteRequestRepository->getById($id);
                if ($quoteRequest->getId()) {
                    $customerId = (int)$this->customerSession->getCustomerId();
                    $customer = $this->customerRepository->getById($customerId);
                    $connection  = $this->resourceConnection->getConnection();
                    $tableName = $connection->getTableName(QuoteRequestInterface::MAIN_TABLE);
                    $data = [];
                    $data['opportunity'] = $quoteRequest->getData('opportunity');
                    $data['quote_name'] = $quoteRequest->getData('quote_name');
                    $data['approval_1_status'] = $quoteRequest->getData('approval_1_status');
                    $data['approval_2_status'] = $quoteRequest->getData('approval_2_status');

                    if ($customerType == 3) {
                        if ($status == ConfigInterface::STATUS_APPROVED) {
                            $data['approval_1_status'] = ConfigInterface::STATUS_APPROVED;
                        } else if ($status == ConfigInterface::STATUS_REJECTED) {
                            $data['approval_1_status'] = ConfigInterface::STATUS_REJECTED;
                        }
                    }
                    if ($customerType == 4) {
                        if ($status == ConfigInterface::STATUS_APPROVED) {
                            $data['status'] = ConfigInterface::STATUS_APPROVED;
                            $data['approval_2_status'] = ConfigInterface::STATUS_APPROVED;
                        } else if ($status == ConfigInterface::STATUS_REJECTED) {
                            $data['approval_2_status'] = ConfigInterface::STATUS_REJECTED;
                        }
                    }

                    if ($data['approval_1_status'] == ConfigInterface::STATUS_APPROVED && $data['approval_2_status'] == ConfigInterface::STATUS_APPROVED) {
                        $data['status'] = ConfigInterface::STATUS_APPROVED;
                    } else if ($data['approval_1_status'] == ConfigInterface::STATUS_REJECTED && $data['approval_2_status'] == ConfigInterface::STATUS_REJECTED) {
                        $data['status'] = ConfigInterface::STATUS_REJECTED;
                    }

                    $connection->update(
                        $tableName, $data,
                        ['id = ?' => (int)$id]
                    );
                    $data['name'] = $customer->getFirstname() . ' ' . $customer->getLastname();
                    
                    if ($status == ConfigInterface::STATUS_APPROVED) {
                        $this->sendEmail($customer->getEmail(), $quoteRequest->getData('customer_email'), $data, $this->quoteRequestConfig->approvedEmailTemplate());
                    } else if ($status == ConfigInterface::STATUS_REJECTED) {
                        $this->sendEmail($customer->getEmail(), $quoteRequest->getData('customer_email'), $data, $this->quoteRequestConfig->rejectedEmailTemplate());
                    }

                    $this->messageManager->addSuccessMessage(
                        __('Your device quote request has been %1.', $status)
                    );
                } else {
                    $this->messageManager->addErrorMessage(
                        __('An error occurred while processing your request. Please try again later.')
                    );
                }
            } else {
                $this->messageManager->addErrorMessage(
                    __('An error occurred while processing your request. Please try again later..')
                );
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __('An error occurred while processing your request. Please try again later...')
            );
        }
        
        return $this->resultRedirectFactory->create()->setPath('quote/account/approval');
    }

    /**
     * @param array $post Post data from quote request form
     * @return void
     */
    private function sendEmail($replyTo, $recipientEmail, $data, $emailTemplate)
    {
        $this->mail->send(
            $replyTo,
            $recipientEmail,
            ['data' => new DataObject($data)],
            $emailTemplate
        );
    }
}
