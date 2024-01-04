<?php

namespace AscentDigital\NetsuiteConnector\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;
use Magento\Catalog\Model\Product\Gallery\EntryFactory;
use Magento\Catalog\Model\Product\Gallery\ReadHandler;
use Magento\Catalog\Model\Product\Gallery\Processor;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;

class ProductImageHelper extends AbstractHelper
{

    protected $galleryReadHandler;
    protected $productGallery;
    protected $imageProcessor;
    // Inject the necessary dependencies
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ImageContentInterfaceFactory $imageContentFactory,
        ReadHandler $galleryReadHandler,
        Processor $imageProcessor,
        Gallery $productGallery,
        EntryFactory $mediaEntryFactory
    ) {
        $this->productRepository = $productRepository;
        $this->imageContentFactory = $imageContentFactory;
        $this->galleryReadHandler = $galleryReadHandler;
        $this->imageProcessor = $imageProcessor;
        $this->productGallery = $productGallery;
        $this->mediaEntryFactory = $mediaEntryFactory;
    }
    public function addImage($product, $imagePath, $imageName, $logger)
    {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            // Load your product by SKU or ID
            $product = $this->productRepository->get($product->getSku());

            // Create the image content object
            // $imageContent = $this->imageContentFactory->create();
            // $imageContent->setBase64EncodedData(base64_encode(file_get_contents($imagePath)));
            // $imageContent->setType('image/jpeg');
            // $imageContent->setName($imageName);

            $this->galleryReadHandler->execute($product);

            // Unset existing images
            // $product = $objectManager->create('Magento\Catalog\Model\Product')->load($product->getId());
            // $imageProcessor = $objectManager->create('\Magento\Catalog\Model\Product\Gallery\Processor');
            // $images = $product->getMediaGalleryImages();
            // foreach ($images as $child) {
            //     $imageProcessor->removeImage($product, $child->getFile());
            // }
            $images = $product->getMediaGalleryImages();
            foreach ($images as $child) {
                $this->productGallery->deleteGallery($child->getValueId());
                $this->imageProcessor->removeImage($product, $child->getFile());
            }

            // Create an image model
            $mediaGalleryEntry = $this->mediaEntryFactory->create();

            // Set the image as the base image
            $mediaGalleryEntry->setFile($imagePath);
            $mediaGalleryEntry->setMediaType('image');
            $mediaGalleryEntry->setLabel('Image Label');
            $mediaGalleryEntry->setPosition(1);
            // Add the media gallery entry to the product
            $product->addImageToMediaGallery(
                $mediaGalleryEntry->getFile(),
                ['image', 'small_image', 'thumbnail'],
                false,
                false
            );
            // $product->addImageToMediaGallery($mediaGalleryEntry, false, false);

            // Save the product
            // $this->productRepository->save($product);
            $product->save();   
            // $processor->addImage($product, $mediaGalleryEntry->getFile(),'small_image', false, false);
        } catch (\Exception $ex) {
            $logger->debug(__($ex->getMessage()));
        }
    }
}
