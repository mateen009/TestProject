<?php

namespace Orders\Data\Block\Adminhtml\Order\View\Tab;

use Mobility\QuoteRequest\Model\QuoteRequestFactory;

class QuoteTab extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_template = 'order/view/tab/quotetab.phtml';
    /**
     * @var \Magento\Framework\Registry
     */
    private $_coreRegistry;

    /**
     * @var \Mobility\QuoteRequest\Model\QuoteRequestFactory
     */
    protected $quoteRequest;

    /**
     * View constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        QuoteRequestFactory $quoteRequest,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->quoteRequest = $quoteRequest;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve order model instance
     * 
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    public function getFormQuote()
    {
        $formQuoteId = $this->getOrder()->getFormQuoteId();
        $formQuote = $this->quoteRequest->create()->load($formQuoteId);
        return $formQuote;
    }
    /**
     * Retrieve order model instance
     *
     * @return int
     *Get current id order
     */
    public function getOrderId()
    {
        return $this->getOrder()->getEntityId();
    }

    /**
     * Retrieve order increment id
     *
     * @return string
     */
    public function getOrderIncrementId()
    {
        return $this->getOrder()->getIncrementId();
    }
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Quote Detail');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Quote Detail');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
