<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ReindexStock extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_ReportBuilder::system/config/reindex_stock.phtml';

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element = clone $element;
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $this->addData([
            'button_label' => __($originalData['button_label']),
            'html_id' => $element->getHtmlId(),
            'ajax_url' => $this->_urlBuilder->getUrl('amreportbuilder/stock/addCronJob')
        ]);

        return $this->_toHtml();
    }
}
