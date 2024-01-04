<?php
namespace Cminds\Oapm\Block\Adminhtml\Sales\Order\Grid\Column\Renderer;

use Magento\Backend\Block\Context as BlockContext;
use Magento\Sales\Api\OrderRepositoryInterface;

class PayerName extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param BlockContext $context
     * @param OrderRepositoryInterface $orderRepository
     * @param array $data
     */
    public function __construct(
        BlockContext $context,
        OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        return $this->orderRepository
            ->get($row->getData('entity_id'))
            ->getPayment()
            ->getAdditionalInformation('recipient_name');
    }
}
