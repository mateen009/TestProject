<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\ViewModel\Adminhtml\View;

use Amasty\ReportBuilder\Model\ReportResolver;
use Amasty\ReportBuilder\Model\View\MenuDataProvider;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Sidebar implements ArgumentInterface
{
    /**
     * @var MenuDataProvider
     */
    private $menuDataProvider;

    private $jsonSerializer;

    /**
     * @var ReportResolver
     */
    private $reportResolver;

    public function __construct(
        MenuDataProvider $menuDataProvider,
        JsonSerializer $jsonSerializer,
        ReportResolver $reportResolver
    ) {
        $this->menuDataProvider = $menuDataProvider;
        $this->jsonSerializer = $jsonSerializer;
        $this->reportResolver = $reportResolver;
    }

    public function getMenuDataJson(): string
    {
        $menuData = $this->menuDataProvider->execute();

        return $this->jsonSerializer->serialize($menuData);
    }

    public function getCurrentReportId(): int
    {
        return $this->reportResolver->resolve()->getReportId();
    }

    public function prepareJsLayout(array $blockData): string
    {
        $jsLayout = $blockData['jsLayout']['components'] ?? [];

        return $this->jsonSerializer->serialize($jsLayout);
    }
}
