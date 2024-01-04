<?php

namespace Mobility\QuoteRequest\Controller\Index;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
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
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Filesystem;

class Post extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
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
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

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
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var AdapterFactory
     */
    protected $adapterFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;


    protected $addresss;
    /**
     * @param Context $context
     * @param QuoteRequestRepositoryInterface $quoteRequestRepository
     * @param CustomerSession $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param CheckoutSession $checkoutSession
     * @param ResourceConnection $resourceConnection
     * @param ConfigInterface $quoteRequestConfig
     * @param MailInterface $mail
     * @param UploaderFactory $uploaderFactory
     * @param AdapterFactory $adapterFactory
     * @param Filesystem $filesystem
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        QuoteRequestRepositoryInterface $quoteRequestRepository,
        CustomerSession $customerSession,
        CustomerRepositoryInterface $customerRepository,
        CheckoutSession $checkoutSession,
        ResourceConnection $resourceConnection,
        ConfigInterface $quoteRequestConfig,
        MailInterface $mail,
        UploaderFactory $uploaderFactory,
        AdapterFactory $adapterFactory,
        Filesystem $filesystem,
        \Magento\Customer\Model\AddressFactory $addresss,
        LoggerInterface $logger = null,
        \Magento\Quote\Api\Data\AddressInterface $addressInt,
        \Magento\Checkout\Api\ShippingInformationManagementInterface $shippingInformationManagement,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $shippingInformation
    ) {
        parent::__construct($context);
        $this->quoteRequestRepository = $quoteRequestRepository;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->checkoutSession = $checkoutSession;
        $this->resourceConnection = $resourceConnection;
        $this->quoteRequestConfig = $quoteRequestConfig;
        $this->mail = $mail;
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
        $this->addresss = $addresss;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
        $this->addressInt = $addressInt;
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->shippingInformation = $shippingInformation;
    }

    /**
     * Post user question
     *
     * @return Redirect
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $quote = $this->checkoutSession->getQuote();
        // print_r($quote->getAttachment());die;
        $customer = $this->customerRepository->getById($quote->getCustomerId());

        $data = (array) $this->getRequest()->getPost();
        $redirectToCheckout = false;
        if (isset($data['convert'])) {
            $redirectToCheckout = true;
            unset($data['convert']);
        }
        // CHM YM
        // checks approval 
        $isApproval1 = $customer->getCustomAttribute('Approval_1_ID');
        $isApproval2 = $customer->getCustomAttribute('Approval_2_ID');
        if ($isApproval1) {
            $isApproval1Required = (int)$isApproval1->getValue();
            $approval1Id = $customer->getCustomAttribute('SalesManager_ID');
            if ($approval1Id) {
                if ($isApproval1Required) {
                    $apprId = (int)$approval1Id->getValue();
                    $approval1 = $this->customerRepository->getById($apprId);
                    $data['approval_1_id'] = $approval1->getId();
                    $data['approval_1_email'] = $approval1->getEmail();
                    $data['approval_1_status'] = ConfigInterface::STATUS_REQUESTED;
                    $data['status'] = ConfigInterface::STATUS_REQUESTED;
                } else {
                    if (isset($data['approval_1_id'])) {
                        unset($data['approval_1_id']);
                    }
                    if (isset($data['approval_1_email'])) {
                        unset($data['approval_1_email']);
                    }
                    $data['approval_1_status'] = ConfigInterface::STATUS_APPROVED;
                    $data['status'] = ConfigInterface::STATUS_APPROVED;
                }
            }
        }

        if ($isApproval2) {
            $isApproval2Required = (int)$isApproval2->getValue();
            $approval2Id = $customer->getCustomAttribute('TerritoryManager_ID');
            if ($approval2Id) {
                if ($isApproval2Required) {
                    $approval2 = $this->customerRepository->getById((int)$approval2Id->getValue());
                    $data['approval_2_id'] = $approval2->getId();
                    $data['approval_2_email'] = $approval2->getEmail();
                    $data['approval_2_status'] = ConfigInterface::STATUS_REQUESTED;
                    $data['status'] = ConfigInterface::STATUS_REQUESTED;
                } else {
                    if (isset($data['approval_2_id'])) {
                        unset($data['approval_2_id']);
                    }
                    if (isset($data['approval_2_email'])) {
                        unset($data['approval_2_email']);
                    }
                    $data['approval_2_status'] = ConfigInterface::STATUS_APPROVED;
                    $data['status'] = ConfigInterface::STATUS_APPROVED;
                }
            }
        }

        $shippingAddressId = 0;
        $billingAddressId = 0;
        // if (isset($data['select_address']) && $data['select_address'] != 'default') {
        //     //get default shipping address
        //     $shippingAddressId = $customer->getDefaultShipping();
        //     $billingAddressId = $customer->getDefaultBilling();
        //     if ($shippingAddressId && empty($data['previous_default_shipping'])) {
        //         $data['previous_default_shipping'] = $shippingAddressId;
        //     }
        //     if ($billingAddressId && empty($data['previous_default_billing'])) {
        //         $data['previous_default_billing'] = $billingAddressId;
        //     }
        //     // set default shipping address
        //     $firstName = "";
        //     $lastName = "";
        //     $name = explode(" ", $data['customer_name']);
        //     $count =  str_word_count($data['customer_name']);

        //     if ($count == 1) {
        //         $firstName = $name['0'];
        //         $lastName =  $name['0'];
        //     }

        //     if ($count == 2) {
        //         $firstName = $name['0'];
        //         $lastName =  $name['1'];
        //     }
        //     if ($count > 2) {
        //         $firstName = $name['0'];
        //         $lastName =  $name['1'] . " " . $name['2'];
        //     }

        //     $address = $this->addresss->create();

        //     $address->setCustomerId($quote->getCustomerId())
        //         ->setFirstname($firstName)
        //         ->setLastname($lastName)
        //         ->setCountryId('US')
        //         ->setPostcode($data['agency_zipcode'])
        //         ->setCity($data['agency_city'])
        //         ->setTelephone($data['customer_phone'])
        //         ->setRegion($data['agency_state'])
        //         ->setCompany('')
        //         ->setStreet($data['agency_street'])
        //         ->setIsDefaultBilling(1)
        //         ->setIsDefaultShipping(1)
        //         ->setSaveInAddressBook('0');
        //     try {
        //         $address->save();
        //     } catch (\Exception $e) {
        //         $this->messageManager->addErrorMessage(
        //             __($e->getMessage())
        //         );
        //         return $this->resultRedirectFactory->create()->setPath('quote/index');
        //     }
        // }




        ////////////////////////////////////////////

        // $newAddress = $this->addressInt
        //             ->setFirstname('john')
        //             ->setLastname('Dev')
        //             ->setStreet('15th street')
        //             ->setCity('Alab')
        //             ->setCountryId('US')
        //             ->setRegionId(12)
        //             ->setRegion('California')
        //             ->setPostcode('92010')
        //             ->setTelephone('4444444444444')
        //             ->setFax('3333')
        //             ->setSaveInAddressBook(1)
        //             ->setSameAsBilling(1);
        // if ($this->checkoutSession->getQuote()) {
        //     $cartId = $this->checkoutSession->getQuote()->getId();
        //     if ($cartId) {
        //         $add = $this->shippingInformation->setShippingAddress($newAddress)
        //         ->setShippingCarrierCode('flatrate')
        //         ->setShippingMethodCode('flatrate');;
        //         $this->shippingInformationManagement->saveAddressInformation($cartId, $add);
        //     }
        // }



        //CHM YM END
        $data['quote_id'] = $quote->getId();
        $data['customer_id'] = $quote->getCustomerId();
        unset($data['existing_address']);
        unset($data['form_key']);
        $id = (int)$this->getRequest()->getParam(QuoteRequestInterface::ID);
        $files = $this->getRequest()->getFiles();  // get file from request
        try {
            // upload and save attachment in database
            if (isset($files['attachment']) && !empty($files['attachment']["name"])) {
                $uploaderFactory = $this->uploaderFactory->create(['fileId' => 'attachment']);
                $uploaderFactory->setAllowedExtensions(['pdf', 'docx', 'doc']);
                $uploaderFactory->setAllowRenameFiles(true);
                $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $destinationPath = $mediaDirectory->getAbsolutePath('orders/attachment');
                $result = $uploaderFactory->save($destinationPath);
                $imagePath = $result['file'];
                $data['attachment'] = $imagePath;
                $quote->setAttachment($imagePath);
                $quote->save();
            }
            $connection = $this->resourceConnection->getConnection();
            $tableName = $connection->getTableName(QuoteRequestInterface::MAIN_TABLE);
            // echo "<pre>";print_r($data);die;
            if ($id) {
                unset($data['id']);
                $data['updated_at'] = date("Y-m-d H:i:s"); // updated date
                $connection->update(
                    $tableName,
                    $data,
                    ['id = ?' => (int)$id]
                );
                $data['name'] = $data['customer_name'];
                $data['subject'] = __("Quote #%1 has been updated successfully.", $id);
                if (isset($data['approval_1_email'])) {
                    $this->sendEmail($data['customer_email'], $data['approval_1_email'], $data, $this->quoteRequestConfig->requestedEmailTemplate());
                }
                if (isset($data['customer_email'], $data['approval_2_email'])) {
                    $this->sendEmail($data['approval_2_email'], $data['approval_2_email'], $data, $this->quoteRequestConfig->requestedEmailTemplate());
                }
            } else {
                $connection->insert($tableName, $data);
                $data['name'] = $data['customer_name'];
                $data['subject'] = __("New Quote has been created successfully.");
                if (isset($data['approval_1_email']))
                    $this->sendEmail($data['customer_email'], $data['approval_1_email'], $data, $this->quoteRequestConfig->requestedEmailTemplate());
                if (isset($data['approval_2_email']))
                    $this->sendEmail($data['customer_email'], $data['approval_2_email'], $data, $this->quoteRequestConfig->requestedEmailTemplate());
            }
            if (!$redirectToCheckout) {
                $this->messageManager->addSuccessMessage(
                    __($data['subject'])
                );
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('quote/index');
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __('An error occurred while processing your form. Please try again later.')
            );
            return $this->resultRedirectFactory->create()->setPath('quote/index');
        }
        if ($redirectToCheckout) {
            return $this->resultRedirectFactory->create()->setPath('checkout');
        } else {
            return $this->resultRedirectFactory->create()->setPath('quote/index');
        }
    }

    /**
     * @param array $post Post data from quote request form
     * @return void
     */
    private function sendEmail($replyTo, $recipientEmail, $data, $emailTemplate)
    {
        // $this->mail->send(
        //     $replyTo,
        //     $recipientEmail,
        //     ['data' => new DataObject($data)],
        //     $emailTemplate
        // );
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function validatedParams()
    {
        $request = $this->getRequest();
        if (trim($request->getParam('name')) === '') {
            throw new LocalizedException(__('Enter the Name and try again.'));
        }
        if (trim($request->getParam('comment')) === '') {
            throw new LocalizedException(__('Enter the comment and try again.'));
        }
        if (false === \strpos($request->getParam('email'), '@')) {
            throw new LocalizedException(__('The email address is invalid. Verify the email address and try again.'));
        }
        if (trim($request->getParam('hideit')) !== '') {
            throw new \Exception();
        }

        return $request->getParams();
    }
}
