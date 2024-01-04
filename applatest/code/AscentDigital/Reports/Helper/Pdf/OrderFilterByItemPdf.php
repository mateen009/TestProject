<?php

namespace AscentDigital\Reports\Helper\Pdf;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;


class OrderFilterByItemPdf extends AbstractHelper{
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
    public function generate($orders)
    {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
            $mediaPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
        
            $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Ascent Digital');
            $pdf->SetTitle('Order By Item Report');
            $pdf->SetSubject('Order By Item Report');
            $pdf->SetKeywords('Order By Item Report');

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
        
        
            $heading='<h1>Order By Item Report</h1>';
            $pdf->writeHTML($heading, true, false, true, false, '');
            foreach ($orders as $order) {
            $html = '<table cellspacing="0" border="0">
            <tr>
            <td><span>SO #  '.$order['ns_so_number'].'</span></td>
            </tr>
            <tr>
            <td><span>Email:  '.$order->getCustomerEmail().'</span></td>
            </tr>
            <tr>
            <td><span>Agency Name:  '.$order->getAgencyName().'</span></td>
            </tr>
            <tr>
            <td><span>Date:  '.date('M d, Y', strtotime($order->getCustomerTs())).'</span></td>
            </tr>
            <tr>
            <td><span>Order Status:  '.$order->getStatusLabel().'</span></td>
            </tr>
            </table>
                
            </br>
            </br>';
           
           $html .= '<table style="font-family: Arial, Helvetica, sans-serif;border-collapse: collapse;width: 100%;"> <tr>
              <th  style="width:50%;height:30px;border: 1px solid #ddd;padding: 8px;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: black;color:white;">Product Name</th>
              <th style="width:50%;border: 1px solid #ddd;height:30px;padding: 8px;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: black;color:white;">Sku</th>
            </tr>';
            foreach($order->getAllItems() as $item){
            $html .= '
                   <tr>
                     
                     <td style="width:50%;height:30px;border: 1px solid #ddd;padding: 8px;">'.$item->getName().'</td>
                     <td style="width:50%;border: 1px solid #ddd;height:30px;padding: 8px;">'.$item->getSku().'</td>
                   </tr>';
            }
            $html .= '</table>';
           $pdf->writeHTML($html, true, false, true, false, '');
            }
        
            

            // reset pointer to the last page
            $pdf->lastPage();

            // ---------------------------------------------------------

            //CHM file name
            $search = array('/', '"');
            $file_n = str_replace('Order Filter By Item Report', "", 'Order Filter By Item Report' . '-' . '' . '.pdf');

            //Close and output PDF document
            $pdf->Output($file_n, 'I');

            //============================================================+
            // END OF FILE
            //============================================================+
    }
    
}
