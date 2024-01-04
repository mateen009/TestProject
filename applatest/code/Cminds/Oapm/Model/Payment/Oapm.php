<?php
namespace Cminds\Oapm\Model\Payment;

class Oapm extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Payment method code.
     *
     * @var string
     */
    const METHOD_CODE = 'cminds_oapm';

    /**
     * @var string
     */
    protected $_code = self::METHOD_CODE;

    /**
     * @var string
     */
    protected $_formBlockType = \Cminds\Oapm\Block\Form\Oapm::class;

    /**
     * @var string
     */
    protected $_infoBlockType = \Cminds\Oapm\Block\Info\Oapm::class;

    /**
     * Payment Method feature.
     *
     * @var bool
     */
    protected $_isOffline = true;
}
