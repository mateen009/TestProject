<?php
namespace Cminds\Oapm\Model\Mail;
 
class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    public function addAttachment($pdfString)
    {
        $this->message->createAttachment(
            $pdfString,
            'application/pdf',
            \Zend_Mime::DISPOSITION_ATTACHMENT,
            \Zend_Mime::ENCODING_BASE64,
            'attatched.pdf'
        );
        return $this;
    }
}