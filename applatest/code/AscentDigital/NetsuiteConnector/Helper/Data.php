<?php

namespace AscentDigital\NetsuiteConnector\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem\DirectoryList as Directory;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use AscentDigital\NetsuiteConnector\Helper\StateCode;

class Data extends AbstractHelper
{
    protected $productRepository;
    protected $addressCollection;
    protected $countryFactory;
    protected $messageManager;
    protected $customerSession;
    protected $file;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directory;
    protected $logger;


    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory $addressCollection,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Customer\Model\Session $customerSession,
        Directory $directory,
        FileDriver $file,
        LoggerInterface $logger,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->addressCollection = $addressCollection;
        $this->productRepository = $productRepository;
        $this->countryFactory = $countryFactory;
        $this->customerSession = $customerSession;
        $this->directory = $directory;
        $this->file = $file;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
    }


    public function orderToNetsuite($order)
    {
        try {
            $root = $this->directory->getRoot();

            require_once($root . '/lib/PHPToolkit/NetSuiteService.php');
            $service = new \NetSuiteService();
            $service->setPreferences(false, true);

            $so = new \SalesOrder();
            $so->email = $order->getCustomerEmail();
            $so->externalId = $order->getIncrementId();
            $so->entity = new \RecordRef();
            // store id = 7 is perks at work store 
            if($order->getStoreId() == 7){
                $so->entity->internalId = '89855';
            } else {
                $so->entity->internalId = $this->customerSession->getCustomer()->getNsInternalId();
            }

            // perks at work customer internal ids 
            // Production Internal ID: 101684
            // Sandbox Internal ID: 89855


            // $so->entity->internalId = '470';
            // $so->entity->name = 'ATL PD ';

            // sales order status
            $orderStatus = \SalesOrderOrderStatus::_pendingFulfillment;
            $so->orderStatus = $orderStatus;
            // $so-> = $orderStatus;
            // custom fields
            // ship using customer account 
            $custbody_ship_using_customers_account = new \BooleanCustomFieldRef();
            $custbody_ship_using_customers_account->value = $order->getCustomerShippingAccount();
            $custbody_ship_using_customers_account->scriptId = 'custbody_ship_using_customers_account';

            // Cost Center
            $custbodycost_center = new \StringCustomFieldRef();
            $custbodycost_center->value = $order->getCostCenter();
            $custbodycost_center->scriptId = 'custbodycost_center';

            // client po#  
            $custbodymcg_cust_client_po = new \StringCustomFieldRef();
            $custbodymcg_cust_client_po->value = $order->getClientPo();
            $custbodymcg_cust_client_po->scriptId = 'custbodymcg_cust_client_po';
            $customFieldList = new \CustomFieldList();
            $customFieldList->customField = [$custbody_ship_using_customers_account, $custbodycost_center, $custbodymcg_cust_client_po];

            // add custom field to order
            $so->customFieldList = $customFieldList;
            $so->itemList = new \SalesOrderItemList();
            // get order items
            foreach ($order->getAllVisibleItems() as $_item) {
                $product_id = $_item->getProductId();
                $product = $this->productRepository->getById($product_id);
                $internalId = $product->getItemNsInternalId();
                $soi = new \SalesOrderItem();
                $soi->item = new \RecordRef();
                $soi->item->internalId = $internalId;
                $soi->quantity = $_item->getQtyOrdered();
                $soi->amount = $_item->getBasePrice();  //required
                $so->itemList->item = array($soi);
            }

            if (!$order->getIsVirtual()) {
                $orderShippingId = $order->getShippingAddressId();
                $shippingAddress = $this->addressCollection->create()->addFieldToFilter('entity_id', array($orderShippingId))->getFirstItem();
                if ($shippingAddress->getData()) {
                    $address = new \Address();
                    if ($shippingAddress->getCountryId() == 'US') {
                        $address->country = '_unitedStates';
                        $address->state = StateCode::US_STATE[$shippingAddress->getRegion()];
                    }
                    $address->attention = $shippingAddress->getFirstname();
                    $address->addressee = $shippingAddress->getCompany(); // customer name
                    $address->addr1 = $shippingAddress->getStreet()[0];
                    $address->city = $shippingAddress->getCity();
                    $address->zip = $shippingAddress->getPostcode();
                    $address->addrText = $shippingAddress->getCompany();
                    $so->shippingAddress = $address;
                    // $country = $this->countryFactory->create()->loadByCode($shippingAddress->getCountryId());
                }
            }

            $request = new \AddRequest();
            $request->record = $so;
            $addResponse = $service->add($request);

            if (!$addResponse->writeResponse->status->isSuccess) {
                $messages = $addResponse->writeResponse->status->statusDetail;
                foreach ($messages as $message) {
                    $this->messageManager->addError(__($message->message));
                }
                return $this;
            } else {

                $orderInternalId = $addResponse->writeResponse->baseRef->internalId;
                $order->setNsInternalId($orderInternalId);
                $time = date('Y-m-d H:i:s');
                $order->setCustomerTs($time);
                $orderState =  \Magento\Sales\Model\Order::STATE_PROCESSING;
                $order->setState($orderState)->setStatus($orderState);
                $order->save();
                $message =  "NetSuite Order Created Successfully, with internal id " . $addResponse->writeResponse->baseRef->internalId;
                $this->logger->debug($message);
                // Add file in netstsuite
                $attachment = $order->getAttachment();
                if ($attachment) {
                    $media = $this->directory->getPath('media');
                    $path = $media . '/orders/attachment/' . $attachment;
                    $contents = $this->file->fileGetContents($path);

                    $file = new \File();
                    $file->name = $orderInternalId . '.csv';
                    $file->content = $contents;
                    $folder = new \RecordRef();
                    $folder->internalId = '595322';
                    $file->folder = $folder;
                    $request = new \AddRequest();
                    $request->record = $file;
                    $addResponse = $service->add($request);
                }
                return 'success';
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->logger->debug('No such entity exist!');
            $this->logger->error('No such entity exist!');
            $this->messageManager->addError(__("No such entity exist!"));
            return 'error';
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            $this->logger->error($e->getMessage());
            return 'error';
        }
    }
}
