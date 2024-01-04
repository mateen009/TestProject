<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Custom\AdvanceExchange\Controller\Index;

use Magento\Framework\App\Filesystem\DirectoryList;
use AscentDigital\NetsuiteConnector\Helper\AdvancedExchange;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Image\AdapterFactory;
use Custom\AdvanceExchange\Model\AdvancedExchangeFactory;
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

    protected $advancedExchangeFactory;
    protected $advancedExchange;
    protected $messageManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Custom\AdvanceExchange\Model\SaveData $saveData,
        UploaderFactory $uploaderFactory,
        AdapterFactory $adapterFactory,
        AdvancedExchangeFactory $advancedExchangeFactory,
        AdvancedExchange $advancedExchange,
        Filesystem $filesystem,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_customerSession = $customerSession;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->saveData = $saveData;
        $this->uploaderFactory = $uploaderFactory;
        $this->advancedExchangeFactory = $advancedExchangeFactory;
        $this->advancedExchange = $advancedExchange;
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
        $_data = '';
        $data = $this->getRequest()->getParams();
        // echo "<pre>";print_r($lastRecord->getData());die();
        if(!isset($_GET['ae_id']) && $data) {
            $files = $this->getRequest()->getFiles();
            if($files) {
                $filePath = $this->uploadFile($files);
                $data['filePath'] = $filePath;
            }
            try{
            $id = $this->saveData->execute($data);
            $lastRecord = $this->advancedExchangeFactory->create()->Load($id);
            $data = $this->advancedExchange->addAdvanceExchange($lastRecord);

            if($data) {
                $this->messageManager->addSuccess(__("Advanced Exchang Request Added Successfully!"));
            } else {
                $this->messageManager->addError(__("Something went wrong while adding in Netsuite."));
            }
            }
            catch(\Exception $e){
                $this->messageManager->addError($e->getMessage());
            }
        }
        
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Depot Services'));
        
        return $resultPage;
    }

    public function uploadFile($file)
    {
        $filePath = '';
        if (isset($file['attachedfile']) && !empty($file['attachedfile']["name"])){
            try{
                $uploaderFactory = $this->uploaderFactory->create(['fileId' => 'attachedfile']);
                //check upload file type or extension
                $uploaderFactory->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'pdf', 'docx', 'doc', 'txt']);
                $imageAdapter = $this->adapterFactory->create();
                $uploaderFactory->addValidateCallback('custom_image_upload',$imageAdapter,'validateUploadFile');
                $uploaderFactory->setAllowRenameFiles(true);
                $uploaderFactory->setFilesDispersion(true);
                $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $destinationPath = $mediaDirectory->getAbsolutePath('advanceExchange');
                $result = $uploaderFactory->save($destinationPath);
                if (!$result) {
                    throw new LocalizedException(
                        __('File cannot be saved to path: $1', $destinationPath)
                    );
                    $this->messageManager->addError(__("Something went wrong, Please try again"));
                }
                $filePath = 'advanceExchange'.$result['file'];
                
                //Set file path with name for save into database
                $data['filesubmission'] = $filePath;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        return $filePath;
        
    }
}

