<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Plugin\Ui\TemplateEngine\Xhtml\Result;

use Amasty\ReportBuilder\Exception\CollectionFetchException;
use Amasty\ReportBuilder\Exception\NotExistColumnException;
use Amasty\ReportBuilder\Exception\NotExistTableException;
use Amasty\ReportBuilder\Model\Validation\ReportFailedFlag;
use Magento\Ui\TemplateEngine\Xhtml\Result;

class CatchException
{
    /**
     * @var ReportFailedFlag
     */
    private $reportFailedFlag;

    public function __construct(ReportFailedFlag $reportFailedFlag)
    {
        $this->reportFailedFlag = $reportFailedFlag;
    }

    /**
     * Catch our execption when report listing rendered.
     * If exception catched, set flag, for future redirect in view controller.
     *
     * @see Result::appendLayoutConfiguration
     *
     * @param Result $subject
     * @param callable $proceed
     * @return void
     * @throws CollectionFetchException
     * @throws NotExistColumnException
     * @throws NotExistTableException
     */
    public function aroundAppendLayoutConfiguration(Result $subject, callable $proceed): void
    {
        try {
            $proceed();
        } catch (CollectionFetchException | NotExistColumnException | NotExistTableException $e) {
            $this->reportFailedFlag->set();
            throw $e;
        }
    }
}
