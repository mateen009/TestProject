<?php


namespace Amasty\RmaAutomation\Model\OptionSource;

use Magento\Email\Model\ResourceModel\Template\Collection;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class EmailTemplates
 */
class EmailTemplates implements OptionSourceInterface
{
    /**
     * @var Collection
     */
    private $templateCollection;

    public function __construct(
        Collection $templateCollection
    ) {
        $this->templateCollection = $templateCollection;
    }

    public function toOptionArray()
    {
        $items = $this->templateCollection->toOptionArray();

        return $items;
    }
}
