<?php
namespace Cminds\Oapm\Block\Info;

class Oapm extends \Magento\Payment\Block\Info
{
    /**
     * @var string
     */
    protected $_template = 'Cminds_Oapm::info/oapm.phtml';

    /**
     * Prepare information specific to current payment method
     *
     * @param null|\Magento\Framework\DataObject|array $transport
     * @return \Magento\Framework\DataObject
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if ($this->_paymentSpecificInformation !== null) {
            return $this->_paymentSpecificInformation;
        }

        $data = [
            __('Recipient Name') => $this->getInfo()->getAdditionalInformation('recipient_name'),
            __('Recipient Email') => $this->getInfo()->getAdditionalInformation('recipient_email'),
            __('Recipient Note') => $this->getInfo()->getAdditionalInformation('recipient_note')
        ];

        $transport = parent::_prepareSpecificInformation($transport);

        return $transport->setData(array_merge($data, $transport->getData()));
    }
}
