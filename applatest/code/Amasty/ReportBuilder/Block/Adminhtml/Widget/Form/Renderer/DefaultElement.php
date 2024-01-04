<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Block\Adminhtml\Widget\Form\Renderer;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class DefaultElement extends Template implements RendererInterface
{
    /**
     * @var AbstractElement
     */
    private $element;

    public function render(AbstractElement $element)
    {
        $this->element = $element;
        return $this->toHtml();
    }

    public function getElement(): AbstractElement
    {
        return $this->element;
    }

    public function getTemplate()
    {
        return 'Amasty_ReportBuilder::view/renderer/element.phtml';
    }
}
