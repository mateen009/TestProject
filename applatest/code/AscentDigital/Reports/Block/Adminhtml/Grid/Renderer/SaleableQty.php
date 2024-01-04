<?php

namespace AscentDigital\Reports\Block\Adminhtml\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class SaleableQty extends AbstractRenderer
{
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_authorization = $context->getAuthorization();
    }

    public function render(DataObject $row)
    {
        if ($row->getQty() > 0) {
            if ($row->getReserveQty() < 0) {
                $availableQty = $row->getQty() + $row->getReserveQty();
            } else {
                $availableQty = $row->getQty();
            }
        } else {
            $availableQty = '0';
        }
        return $availableQty;
    }
}
