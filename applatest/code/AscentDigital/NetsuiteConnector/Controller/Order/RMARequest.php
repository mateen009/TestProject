<?php

namespace AscentDigital\NetsuiteConnector\Controller\Order;

use Magento\Sales\Api\Data\ShipmentTrackInterfaceFactory;
use Magento\Framework\Filesystem\DirectoryList as Directory;
use AscentDigital\NetsuiteConnector\Helper\RMA;
use Magento\Framework\App\Bootstrap;

class RMARequest extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \AscentDigital\NetsuiteConnector\Helper\RMA
     */
    protected $helper;


    /**
     * @param \Magento\Framework\App\Action\Context $context,
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        RMA $helper
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Test Order Create Shipment Controller
     */
    public function execute()
    {

    
        // $obj = \Magento\Framework\App\ObjectManager::getInstance();

        // /** @var \Magento\Framework\Filesystem $filesystem */
        // $filesystem = $obj->create('Magento\Framework\Filesystem');
        // /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $mediaDirectory */
        // $mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        // $mediaPath = $mediaDirectory->getAbsolutePath();
        // $productRepository = $obj->create('\Magento\Catalog\Api\ProductRepositoryInterface');
        
        // //Sample product id
        // $productId = 2372;
        // $model = $obj->create('Magento\Catalog\Model\Product');
        // $product = $model->load($productId);
        // $mediaGallery = $product->getMediaGallery();
        // $productImage = $product->getImage();
        // // $ProductPath = substr($productImage, 20);
        // // $str = "Hire freelance developer";
        // // trim($productImage, "/M/C/");
        // print_r( $mediaGallery);die('matenn');
        // // print_r(substr($productImage, 20));die('mateen');
        // // print_r($mediaPath);die('mateen');
        // $product->setMediaGallery(['images' => [], 'values' => []]);
        // $product->addImageToMediaGallery($mediaPath, ['image', 'small_image', 'thumbnail'], false, false);
        // // $product->addImageToMediaGallery( $mediaPath.$productImage, array('image', 'small_image', 'thumbnail'), false, false);
        // // $product->setImage(
        // //             $mediaPath . $productImage
        // //         )
        // //         ->setSmallImage(
        // //             $mediaPath . $productImage
        // //         )
        // //         ->setThumbnail(
        // //             $mediaPath . $productImage
        // //         )
        // //         ->addImageToMediaGallery(
        // //           $mediaPath . $productImage,
        // //             null,
        // //             false,
        // //             false
        // //         )
        // //         ->addImageToMediaGallery(
        // //             $mediaPath . $productImage,
        // //             null,
        // //             false,
        // //             false
        // //         )
        // $product->save();
                // $product->getImage();

                
    // require __DIR__ . '/app/bootstrap.php';
 
    // $bootstrap = Bootstrap::create(BP, $_SERVER);
 
    // $obj = $bootstrap->getObjectManager();
 
    // $state = $obj->get('Magento\Framework\App\State');
    // $state->setAreaCode('frontend');
 
    try
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $importDir = __DIR__ . '/pub/media/catalog/product'; //This is the directory path from where you have to take the images
        
        $i = '2372'; //  It must be product ID for which product it is to be assigned
        
        $product = $objectManager->get('Magento\Catalog\Model\Product')->load($i);
            
        $id = $product->getId();
        $url = $importDir . $product->getImage();
        $abc= substr($url, 131);
        // print_r($abc);die('mateen');
        // $mediaGallery['images'][0]['role'] = $product->getImage();
        // $product->setMediaGallery(['images' => $mediaGallery['images']]);
        // $product->save();
        $product->addImageToMediaGallery($abc, array('thumbnail'), true, false);
        $product->save();
        echo "<br /><br /> $id Product Save Succefully";

    }
    catch(\Exception $e)
    {
        echo $e->getMessage();
        exit;
    }
    }
}
