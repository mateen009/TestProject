<?php

namespace Mobility\QuoteRequest\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteFactory;
use Psr\Log\LoggerInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Customer\Model\Session as CustomerSession;
use Mobility\QuoteRequest\Model\QuoteRequestFactory;

class QuoteToOrder implements ObserverInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Mobility\QuoteRequest\Model\QuoteRequestFactory
     */
    protected $quoteRequestFactory;


    protected $uploaderFactory;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $quoteFactory;

    private $_checkoutSession;

    protected $filesystem;
    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     */

    public function __construct(
        LoggerInterface $logger,
        QuoteRequestFactory $quoteRequestFactory,
        QuoteFactory $quoteFactory,
        CustomerSession $customerSession,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem
    ) {
        $this->_logger = $logger;
        $this->filesystem = $filesystem;
        $this->quoteFactory = $quoteFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->customerSession = $customerSession;
        $this->quoteRequestFactory = $quoteRequestFactory;
    }

    public function execute(Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $observer->getOrder();
        $quoteId = $order->getQuoteId();
        $quote = $this->quoteFactory->create()->load($quoteId);
        $id = $quote->getFormQuoteId();
        $formQuote = $this->quoteRequestFactory->create()->load($id);
        $formQuote->setOrderId($order->getIncrementId());
        $formQuote->save();
        $order->setAttachment($quote->getAttachment());
        $order->setFormQuoteId($id);
        $order->save();

        //////////////////////
        // $orderItems = $order->getAllItems();
        // $allItems = $order->getAllItems();
        // foreach ($allItems as $item) {
        //     $prod = $objectManager->get('Magento\Catalog\Model\Product')->getById($item->getProductId());
        //     $item->setData('UNSPSC', $prod->getData('UNSPSC'));
        //     $item->setData('classification_id', $prod->getData('classification_id'));
        //     $item->setData('mfg_part_number', $prod->getData('name'));
        //     $item->setData('description', $prod->getData('short_description'));
        //     $item->save();
        // }
        // $product = $objectManager->get('Magento\Catalog\Model\Product')->load($product_id);

    }
}
