<?php
namespace Magenest\RentalSystem\Block\Adminhtml\Rule\Edit;

class GenericButton
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->registry = $registry;
    }

    /**
     * Return the current Catalog Rule Id.
     *
     * @return int|null
     */
    public function getRuleId()
    {
        $rentalRule = $this->registry->registry('rentalsystem_rule_current');
        return $rentalRule ? $rentalRule->getId() : null;
    }
}
