<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Plugin\Ui;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Plugin\Ui\Model\ResourceModel\ModifyNamespace;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\Api\BookmarkManagementInterface;

class ChangeNamespace
{
    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    public function beforeGetByIdentifierNamespace(
        BookmarkManagementInterface $subject,
        string $identifier,
        string $namespace
    ): array {
        if ($namespace == ModifyNamespace::VIEW_LISTING_NAMESPACE) {
            $namespace = sprintf('%s_%s', $namespace, $this->request->getParam(ReportInterface::REPORT_ID));
        }

        return [$identifier, $namespace];
    }
}
