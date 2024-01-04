<?php
namespace Cminds\Oapm\Block\Form;

use \Magento\Framework\View\Element\Template\Context as TemplateContext;
use \Cminds\Oapm\Helper\Config as OapmConfig;

class Oapm extends \Magento\Payment\Block\Form
{
    /**
     * @var string
     */
    protected $_template = 'Cminds_Oapm::form/oapm.phtml';

    /**
     *  @var OapmConfig
     */
    protected $helperConfig;

    /**
     * Constructor method.
     *
     * @param TemplateContext
     */
    public function __construct(
        TemplateContext $context,
        OapmConfig $helperConfig,
        array $data = []
    ) {
        $this->helperConfig = $helperConfig;
        parent::__construct($context, $data);
    }

    public function canShowForm()
    {
        if ((int)$this->helperConfig->getConfigData("approver") === \Cminds\Oapm\Model\Config\Source\Approver::APPROVER_CUSTOMER) {
            return true;
        }

        return false;
    }
}
