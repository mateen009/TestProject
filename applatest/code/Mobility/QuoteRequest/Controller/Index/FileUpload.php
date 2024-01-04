<?php
namespace Mobility\QuoteRequest\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Filesystem;
class FileUpload extends \Magento\Framework\App\Action\Action
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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cartObj = $objectManager->get('\Magento\Checkout\Model\Cart');
        $quote = $cartObj->getQuote();
       
        
        if(isset($_FILES['filepath']['name']) && $_FILES['filepath']['name'] != '') {
            try{
                
                
                $uploaderFactory = $this->uploaderFactory->create(['fileId' => 'filepath']);
                $uploaderFactory->setAllowedExtensions(['pdf', 'docx', 'doc']);
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
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }else{
            echo $msg = "File not found!";
        }

       
        
        

    }
}
?>
