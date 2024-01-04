<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Plugin\Ui\Model\ResourceModel;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Model\ReportResolver;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Magento\Ui\Api\Data\BookmarkInterface;

class ModifyNamespace
{
    const VIEW_LISTING_NAMESPACE = 'amreportbuilder_view_listing';

    /**
     * @var ReportResolver
     */
    private $reportResolver;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        ReportResolver $reportResolver,
        RequestInterface $request
    ) {
        $this->reportResolver = $reportResolver;
        $this->request = $request;
    }

    public function beforeSave(BookmarkRepositoryInterface $subject, BookmarkInterface $bookmark): array
    {
        if ($bookmark->getNamespace() == self::VIEW_LISTING_NAMESPACE) {
            try {
                $reportId = (int)$this->request->getParam(ReportInterface::REPORT_ID);
                $report = $this->reportResolver->resolve($reportId);
            } catch (NoSuchEntityException $exception) {
                return [$bookmark];
            }

            $namespace = sprintf('%s_%s', $bookmark->getNamespace(), $report->getReportId());
            $bookmark->setNamespace($namespace);
        }

        return [$bookmark];
    }
}
