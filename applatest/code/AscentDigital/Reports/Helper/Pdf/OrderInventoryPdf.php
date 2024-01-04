<?php
// CHM-MA

namespace AscentDigital\Reports\Helper\Pdf;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;


class OrderInventoryPdf extends AbstractHelper{
    /** @var ResultFactory */
    protected $resultFactory;
    protected $_dir;
    protected $resultPageFactory;
    protected $fileFactory;

    /**
     * @param Context $context
     * @param ResultFactory $resultFactory
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        DirectoryList $dir,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Controller\ResultFactory $resultFactory
    ) {
        $this->_fileFactory = $fileFactory;
        $this->_dir = $dir;
        $this->resultPageFactory = $resultPageFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->resultFactory = $resultFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function generate()
    {
        
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $block =  $objectManager->create('AscentDigital\Reports\Block\Reports\FirstNet\OrderInventoryReport');
            $orders = $block->getProducts();
            $fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
            $mediaPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
               
            $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Ascent Digital');
            $pdf->SetTitle('Order Inventory Report');
            $pdf->SetSubject('Order Inventory Report');
            $pdf->SetKeywords('Order Inventory Report');

            $PDF_HEADER_LOGO_WIDTH = "30";
            $PDF_HEADER_TITLE = "";
            $PDF_HEADER_STRING = "";

            $pdf->SetHeaderData(PDF_HEADER_LOGO, $PDF_HEADER_LOGO_WIDTH, $PDF_HEADER_TITLE, $PDF_HEADER_STRING);

            //$pdf->SetHeaderData(logopath, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            $pdf->SetFont('dejavusans', '', 10);

            $pdf->AddPage();
        
       
            $html='<h1>Order Inventory Report</h1>';
            $html .= '<table style="font-family: Arial, Helvetica, sans-serif;border-collapse: collapse;width: 100%;"> <tr>
            <th  style="width:38%;height:30px;border: 1px solid #ddd;padding: 8px;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: black;color:white;">Item Name</th>
              <th  style="width:18%;height:30px;border: 1px solid #ddd;padding: 8px;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: black;color:white;">SKU</th>
             <th style="width:10%;border: 1px solid #ddd;height:30px;padding: 8px;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: black;color:white;">Total Orders</th>
            <th style="width:15%;border: 1px solid #ddd;height:30px;padding: 8px;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: black;color:white;">Total Qty Ordered</th>
            <th style="width:10%;border: 1px solid #ddd;height:30px;padding: 8px;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: black;color:white;">On Demo</th>
            <th style="width:5%;border: 1px solid #ddd;height:30px;padding: 8px;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: black;color:white;">Due</th>
            <th style="width:10%;border: 1px solid #ddd;height:30px;padding: 8px;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: black;color:white;">Returned</th>
           </tr>';
          // foreach ($orders as $order) {
               foreach($orders as $item){
           $html .= '
                  <tr>
                    <td style="width:38%;height:30px;border: 1px solid #ddd;padding: 8px;">'.$item->getName().'</td>
                    <td style="width:18%;height:30px;border: 1px solid #ddd;padding: 8px;">'.$item->getSku().'</td>
                    <td style="width:10%;border: 1px solid #ddd;height:30px;padding: 8px;">'.$item->getTotalOrders().'</td>
                    <td style="width:15%;height:30px;border: 1px solid #ddd;padding: 8px;">'.$item->getTotalQty() .'</td>
                    <td style="width:10%;border: 1px solid #ddd;height:30px;padding: 8px;">'.$block->getOnDemo($item->getSku()).'</td>
                    <td style="width:5%;border: 1px solid #ddd;height:30px;padding: 8px;">'.$block->getDue($item->getSku()).'</td>
                    <td style="width:10%;border: 1px solid #ddd;height:30px;padding: 8px;">'.$block->getReturned($item->getSku()).'</td>
                    
                  </tr>';
               }
          // }
           $html .= '</table>';
           $pdf->writeHTML($html, true, false, true, false, '');
           
        
            

            // reset pointer to the last page
            $pdf->lastPage();

            // ---------------------------------------------------------

            //CHM file name
            $search = array('/', '"');
            $file_n = str_replace('Order Inventory Report', "", 'Total Sales By Sales Rep Report' . '-' . '' . '.pdf');

            //Close and output PDF document
            $pdf->Output($file_n, 'I');

            //============================================================+
            // END OF FILE
            //============================================================+
    }
    
}
