<?php

namespace AscentDigital\NetsuiteConnector\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Filesystem\DirectoryList as Directory;
use Magento\Framework\App\Request\Http;

class RMA extends AbstractHelper
{
    protected $productRepository;
    protected $addressCollection;
    protected $countryFactory;
    protected $messageManager;
    protected $customerSession;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directory;

    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory $addressCollection,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Customer\Model\Session $customerSession,
        Directory $directory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Http $request
    ) {
        $this->addressCollection = $addressCollection;
        $this->productRepository = $productRepository;
        $this->countryFactory = $countryFactory;
        $this->customerSession = $customerSession;
        $this->directory = $directory;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
    }


    public function returnAuthorizationNetsuite($order)
    {
        try {
            $root = $this->directory->getRoot();

            require_once($root . '/lib/PHPToolkit/NetSuiteService.php');
            $service = new \NetSuiteService();
            $service->setPreferences(false, true);

            $so = new \ReturnAuthorization();
            $so->email = $order->getCustomerEmail();
            $so->entity = new \RecordRef();
            // $so->entity->internalId = $this->customerSession->getCustomer()->getNsInternalId();
            $so->entity->internalId = '81315';
            // $so->entity->name = 'ATL PD ';
            // division

            $so->department = new \RecordRef();
            // $so->entity->internalId = $this->customerSession->getCustomer()->getNsInternalId();
            $so->department->internalId = '12';
            $so->department->name = 'FirstNet : Demo';
            // division

            $so->itemList = new \ReturnAuthorizationItemList();

            // get order items
            foreach ($order->getAllVisibleItems() as $_item) {
                $product_id = $_item->getProductId();
                $product = $this->productRepository->getById($product_id);
                $internalId = $product->getItemNsInternalId();

                $soi = new \ReturnAuthorizationItem();
                $soi->item = new \RecordRef();
                $soi->item->internalId = $internalId;
                $soi->quantity = $_item->getQtyOrdered();
                $soi->amount = $_item->getBasePrice();  //required
                $so->itemList->item = array($soi);
            }

            // if (!$order->getIsVirtual()) {
            if (0) {
                $orderShippingId = $order->getShippingAddressId();
                $shippingAddress = $this->addressCollection->create()->addFieldToFilter('entity_id', array($orderShippingId))->getFirstItem();
                if ($shippingAddress->getData()) {
                    $country = $this->countryFactory->create()->loadByCode($shippingAddress->getCountryId());
                    $address = new \Address();
                    $address->country = '_unitedStates';
                    $address->attention = $shippingAddress->getCompany();
                    $address->addressee = $shippingAddress->getCompany(); // customer name
                    $address->addr1 = $shippingAddress->getStreet()[0];
                    $address->city = $shippingAddress->getCity();
                    $address->state = 'NC';
                    $address->zip = '28217';
                    $address->addrText = $shippingAddress->getCompany();
                    $so->shippingAddress = $address;
                }
            }
            $request = new \AddRequest();
            $request->record = $so;
            $addResponse = $service->add($request);

            if (!$addResponse->writeResponse->status->isSuccess) {
                $messages = $addResponse->writeResponse->status->statusDetail;
                echo "<pre>";
                print_r($messages);
                die;
                // foreach ($messages as $message) {
                //     $this->messageManager->addError(__($message->message));
                // }
                // return $this;
            } else {

                echo "<pre>";
                print_r($addResponse->writeResponse);
                die;
                // $orderInternalId = $addResponse->writeResponse->baseRef->internalId;
                // $order->setNsInternalId($orderInternalId);
                // $order->save();
                // $message =  "ADD SUCCESS, id " . $addResponse->writeResponse->baseRef->internalId;
                // $this->messageManager->addSuccess(__($message));
                // return 'success';
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            die('error');
            // $this->messageManager->addError(__("No such entity exist!"));
            // return 'error';
        } catch (\Exception $e) {
            die('error');
            // $this->messageManager->addError(__($e->getMessage()));
            // return 'error';
        }
    }
  


    public function getRMA()
    {
        $rmaId = $this->request->getParam('rma_id');
        try {
            $root = $this->directory->getRoot();
            
            require_once($root . '/lib/PHPToolkit/NetSuiteService.php');
            //Get Record of Return authroization RMA
            $service = new \NetSuiteService();
            // $service->setSearchPreferences(false, 20);
            $request = new \GetRequest();
            $request->baseRef = new \RecordRef();
            // $request->baseRef->internalId = 412716;
            $request->baseRef->internalId = $rmaId;
            $request->baseRef->type = "returnAuthorization";
            $getResponse = $service->get($request);
            
            if (!$getResponse->readResponse->status->isSuccess) {
                echo "<pre>";
                print_r($getResponse);
            } else {
                $soNumber = $getResponse->readResponse->record;
                // echo "<pre>";print_r($soNumber); die("mateen");
                return $soNumber;
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            die('error');
            // $this->messageManager->addError(__("No such entity exist!"));
            // return 'error';
        } catch (\Exception $e) {
            die('error');
            // $this->messageManager->addError(__($e->getMessage()));
            // return 'error';
        }
    }
}
