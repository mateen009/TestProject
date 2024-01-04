<?php
namespace Magenest\RentalSystem\Controller\Adminhtml\Report\Sales;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultInterface;

class ExportSalesReportCsv extends \Magenest\RentalSystem\Controller\Adminhtml\SalesReport
{
    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        try {
            $this->_view->loadLayout();
            $exportFile = $this->_view->getLayout()
                ->getChildBlock('adminhtml.report.grid', 'grid.export')
                ->getCsvFile();
            return $this->_fileFactory->create('rental_revenue.csv', $exportFile, DirectoryList::VAR_DIR);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

    /**
     * Check ACL
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_RentalSystem::salesreport');
    }
}
