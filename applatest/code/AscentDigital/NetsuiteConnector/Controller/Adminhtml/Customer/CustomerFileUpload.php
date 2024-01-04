<?php

namespace AscentDigital\NetsuiteConnector\Controller\Adminhtml\Customer;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Filesystem;
use AscentDigital\NetsuiteConnector\Model\CustomerFileUploadFactory;
use AscentDigital\NetsuiteConnector\Model\ResourceModel\CustomerFileUpload\Collection;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Filesystem\Driver\File;
//CHM YM

class CustomerFileUpload extends \Magento\Backend\App\Action
{
    protected $uploaderFactory;
    protected $adapterFactory;
    protected $filesystem;
    protected $customerFileUpload;
    protected $customerSession;
    /**
     * @param \Magento\Framework\App\Action\Context $context,
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        UploaderFactory $uploaderFactory,
        AdapterFactory $adapterFactory,
        Filesystem $filesystem,
        ManagerInterface $messageManager,
        Collection $customerFileUploadCollectionFactory,
        CustomerFileUploadFactory $customerFileUploadFactory,
        File $file
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->customerFileUploadCollectionFactory = $customerFileUploadCollectionFactory;
        $this->customerFileUploadFactory = $customerFileUploadFactory;
        $this->_file = $file;
        parent::__construct($context);
    }

    /**
     * Test Order Create Shipment Controller
     */
    public function execute()
    {
        $delete = $this->getRequest()->getParam('delete');
        if (isset($delete)) {
            $this->deleteFiles($delete);
        } else {
            $this->saveCustomerFiles();
        }
    }
    public function saveCustomerFiles()
    {
        $id = $this->getRequest()->getParam('csutomer_id');
        //    print_r($id);die('mateeen');
        $model = $this->customerFileUploadFactory->Create();

        if (isset($_FILES['csutomer_attachment']['name']) && $_FILES['csutomer_attachment']['name'] != '') {
            try {
                $uploaderFactory = $this->uploaderFactory->create(['fileId' => 'csutomer_attachment']);
                $uploaderFactory->setAllowedExtensions(['csv', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg']);
                $uploaderFactory->setAllowRenameFiles(true);
                $imageAdapter = $this->adapterFactory->create();



                $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $destinationPath = $mediaDirectory->getAbsolutePath('mobility_customer/attachment');
                $result = $uploaderFactory->save($destinationPath);
                if (!$result) {
                    throw new LocalizedException(
                        __('File cannot be saved to path: $1', $destinationPath)
                    );
                }

                $imagePath = $result['file'];
                $model->setCustomerId($id);
                $model->setFileName($imagePath);
                $model->save();
                $this->messageManager->addSuccess(__("File Uploaded Successfully"));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect = $this->_redirect('customer/index/edit/id/' . $id);
                return $resultRedirect;
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        } else {
            echo $msg = "File not found!";
            die;
        }
    }
    public function deleteFiles($delete)
    {
        try {
            if ($delete) {
                $model = $this->customerFileUploadFactory->Create()->Load($delete);
                $id = $model->getCustomerId();
                $fileName = $model->getFileName();

                $model->delete();
                $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $destinationPath = $mediaDirectory->getAbsolutePath('mobility_customer/attachment/');
                if ($this->_file->isExists($destinationPath . $fileName)) {
                    $this->_file->deleteFile($destinationPath . $fileName);
                }
                $this->messageManager->addSuccessMessage(__("Record Delete Successfully."));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e, __("We can\'t delete record, Please try again."));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect = $this->_redirect('customer/index/edit/id/' . $id);
        return $resultRedirect;
    }
}
