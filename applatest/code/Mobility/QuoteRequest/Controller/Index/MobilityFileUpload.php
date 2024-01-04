<?php
namespace Mobility\QuoteRequest\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Filesystem;
class MobilityFileUpload extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Test
     */
    
    protected $uploaderFactory;
    protected $adapterFactory;
    protected $filesystem;

    public function __construct(
        Context $context,
        
        UploaderFactory $uploaderFactory,
        AdapterFactory $adapterFactory,
        Filesystem $filesystem
    ) {
        
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
        parent::__construct($context);
    }
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $quoteId = $this->getRequest()->getParam('quoteid');
        // print_r($quoteId) ;die('here');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $checkoutSession = $objectManager->create('\Magento\Checkout\Model\Session');
        // $quote = $checkoutSession->getQuote()->Load($quoteId);
        
        $cartObj = $objectManager->get('\Magento\Quote\Api\CartRepositoryInterface');
        $quote = $cartObj->get($quoteId);
        
        if(isset($_FILES['filepath']['name']) && $_FILES['filepath']['name'] != '') {
            try{
                
                
                $uploaderFactory = $this->uploaderFactory->create(['fileId' => 'filepath']);
                $uploaderFactory->setAllowedExtensions(['csv', 'docx', 'doc']);
                $uploaderFactory->setAllowRenameFiles(true);
                $imageAdapter = $this->adapterFactory->create();
                
               
               
                $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $destinationPath = $mediaDirectory->getAbsolutePath('orders/attachment');
                $result = $uploaderFactory->save($destinationPath);
                if (!$result) {
                    throw new LocalizedException(
                        __('File cannot be saved to path: $1', $destinationPath)
                    );
                }

                $imagePath = $result['file'];
                $data['filepath'] = $imagePath;
                $quote->setAttachment($imagePath);
                $quote->save();
                
                echo $quote->getAttachment();
                //echo $data['filepath'];
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }else{
            echo $msg = "File not found!";
        }

       
        
        

    }
}
?>
