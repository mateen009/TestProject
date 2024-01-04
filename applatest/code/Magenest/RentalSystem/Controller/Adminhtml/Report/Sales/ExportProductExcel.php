<?php
namespace Magenest\RentalSystem\Controller\Adminhtml\Report\Sales;

use Magenest\RentalSystem\Controller\Adminhtml\ProductReport;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class ExportProductExcel extends ProductReport
{
    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        try {
            $this->_view->loadLayout();
            $fileName = 'rental_products.xml';
            $exportFile = $this->_view->getLayout()
                ->getChildBlock('adminhtml.report.grid', 'grid.export')
                ->getExcelFile($fileName);
            return $this->_fileFactory->create($fileName, $exportFile, DirectoryList::VAR_DIR);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }
}
