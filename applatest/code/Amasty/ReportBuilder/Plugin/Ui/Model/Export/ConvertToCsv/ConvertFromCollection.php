<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Plugin\Ui\Model\Export\ConvertToCsv;

use Magento\Ui\Component\MassAction\Filter;

class ConvertFromCollection
{
    /**
     * @var \Amasty\ReportBuilder\Ui\Export\ToCsv
     */
    private $toCsv;

    /**
     * @var Filter
     */
    private $filter;

    public function __construct(\Amasty\ReportBuilder\Ui\Export\ToCsv $toCsv, Filter $filter)
    {
        $this->toCsv = $toCsv;
        $this->filter = $filter;
    }

    /**
     * @param \Magento\Ui\Model\Export\ConvertToCsv $subject
     * @param callable $proceed
     *
     * @return array
     */
    public function aroundGetCsvFile(\Magento\Ui\Model\Export\ConvertToCsv $subject, callable $proceed)
    {
        if ($this->filter->getComponent()->getName() === 'amreportbuilder_view_listing') {
            return $this->toCsv->getCsvFile();
        }

        return $proceed();
    }
}
