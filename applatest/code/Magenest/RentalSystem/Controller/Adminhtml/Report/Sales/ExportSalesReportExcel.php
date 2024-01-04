<?php
namespace Magenest\RentalSystem\Controller\Adminhtml\Report\Sales;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class ExportSalesReportExcel extends \Magenest\RentalSystem\Controller\Adminhtml\SalesReport
{
    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        try {
            $this->_view->loadLayout();
            $fileName = 'rental_revenue.xml';
            $exportFile = $this->_view->getLayout()
                ->getChildBlock('adminhtml.report.grid', 'grid.export')
                ->getExcelFile($fileName);
            return $this->_fileFactory->create($fileName, $exportFile, DirectoryList::VAR_DIR);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }
}
