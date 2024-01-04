<?php
declare(strict_types=1);

namespace TradeCentric\Version\Block\Forms\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Data\Form\Element\AbstractElement;
use TradeCentric\Version\Api\ModuleHelperInterface;
use TradeCentric\Version\Model\ModuleVersion;
use Magento\Backend\Block\Template\Context;

/**
 * Class Version
 * @package Punchout2Go\Punchout\Block\Forms\Config
 */
class Version extends Field
{
    /**
     * @var AbstractHelper
     */
    protected $helper;

    /**
     * @param Context $templateContext
     * @param ModuleHelperInterface $helper
     * @param array $data
     */
    public function __construct(
        Context $templateContext,
        ModuleHelperInterface $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($templateContext, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return __(
            '<label class="label"><span>' . $this->helper->getModuleVersion() . '</span></label>'
        );
    }
}
