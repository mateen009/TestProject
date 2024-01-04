<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Custom\AdvanceExchange\Controller\Manage;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Filesystem;

class Index extends \Magento\Framework\App\Action\Action
{

    private $saveData;
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

    protected $messageManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Custom\AdvanceExchange\Model\SaveData $saveData,
        UploaderFactory $uploaderFactory,
        AdapterFactory $adapterFactory,
        Filesystem $filesystem,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_customerSession = $customerSession;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->saveData = $saveData;
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        // $_data = '';
        // $data = $this->getRequest()->getParams();
        //echo "<pre>";print_r($data);die();
        // if($data) {
            // $files = $this->getRequest()->getFiles();
            // if($files) {
            //     $filePath = $this->uploadFile($files);
            //     $data['filePath'] = $filePath;
            // }
            // $_data = $this->saveData->execute($data);
        // }
        
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Manage Advanced Exchange'));
        // if($_data) {
        //     $this->messageManager->addSuccess(__("Success"));
        // }
        return $resultPage;
    }

    // public function uploadFile($file)
    // {
    //     $filePath = '';
    //     if (isset($file['attachedfile']) && !empty($file['attachedfile']["name"])){
    //         try{
    //             $uploaderFactory = $this->uploaderFactory->create(['fileId' => 'attachedfile']);
    //             //check upload file type or extension
    //             $uploaderFactory->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'pdf', 'docx', 'doc', 'txt']);
    //             $imageAdapter = $this->adapterFactory->create();
    //             $uploaderFactory->addValidateCallback('custom_image_upload',$imageAdapter,'validateUploadFile');
    //             $uploaderFactory->setAllowRenameFiles(true);
    //             $uploaderFactory->setFilesDispersion(true);
    //             $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
    //             $destinationPath = $mediaDirectory->getAbsolutePath('advanceExchange');
    //             $result = $uploaderFactory->save($destinationPath);
    //             if (!$result) {
    //                 throw new LocalizedException(
    //                     __('File cannot be saved to path: $1', $destinationPath)
    //                 );
    //                 $this->messageManager->addError(__("Something went wrong, Please try again"));
    //             }
    //             $filePath = 'advanceExchange'.$result['file'];
                
    //             //Set file path with name for save into database
    //             $data['filesubmission'] = $filePath;
    //         } catch (\Exception $e) {
    //             $this->messageManager->addErrorMessage($e->getMessage());
    //         }
    //     }
    //     return $filePath;
        
    // }
}

