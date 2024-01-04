<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_
 * @copyright Copyright (C) 2020 Magezon (https://magezon.com)
 */

namespace Magezon\EmailBuilder\Block\Email\Element;

use Magento\Framework\View\Element\Template;

class Logo extends \Magezon\SimpleBuilder\Block\Element
{
    /**
     * @var \Magezon\Core\Helper\Data
     */
    protected $coreHelper;

    /**
     * @var \Magezon\Builder\Helper\Data
     */
    protected $builderHelper;

    /**
     * Logo constructor.
     * @param Template\Context $context
     * @param \Magezon\Core\Helper\Data $coreHelper
     * @param \Magezon\Builder\Helper\Data $builderHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magezon\Core\Helper\Data $coreHelper,
        \Magezon\Builder\Helper\Data $builderHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreHelper         = $coreHelper;
        $this->builderHelper      = $builderHelper;
    }

    /**
     * @return mixed
     */
    public function getElementData()
    {
        $element = $this->getElement();
        return $element;
    }

    /**
     * @return string
     */
    public function getLogo()
    {
        $logo     = '';
        $source  = $this->getElementData()->getSource();

        switch ($source) {
            case 'media_library':
                $logo = $this->builderHelper->getImageUrl($this->getElementData()->getLogoImg());
                break;

            case 'external_link':
                $logo = $this->coreHelper->filter($this->getElementData()->getCustomSrc());
                break;
        }

        return $logo;
    }

    /**
     * @return string
     */
    public function getAdditionalStyleHtml()
    {
        $element = $this->getElement();
        $styles = [
            'width' => $this->getStyleProperty($element->getData('logo_width')),
            'height' => $this->getStyleProperty($element->getData('logo_height'))
        ];
        $styleHtml = $this->getStyles('.mgz-email-template-logo img', $styles);
        return $styleHtml;
    }
}
