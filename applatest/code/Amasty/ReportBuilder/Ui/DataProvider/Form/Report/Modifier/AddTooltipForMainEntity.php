<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Ui\DataProvider\Form\Report\Modifier;

use Magento\Framework\View\Asset\Repository;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class AddTooltipForMainEntity implements ModifierInterface
{
    const IMAGE_PATH = 'Amasty_ReportBuilder::images/choosing_entity.gif';
    const TOOLTIP_PATH = 'Amasty_ReportBuilder/form/element/tooltip';

    /**
     * @var Repository
     */
    private $assetRepository;

    public function __construct(
        Repository $assetRepository
    ) {
        $this->assetRepository = $assetRepository;
    }

    public function modifyData(array $data)
    {
        return $data;
    }

    public function modifyMeta(array $meta): array
    {
        $meta["general"]["children"]["main_entity"]["arguments"]["data"]["config"] = [
            'tooltip' => [
                'description' => $this->getTooltipImageHtml()
            ],
            'tooltipTpl' => self::TOOLTIP_PATH
        ];

        return $meta;
    }

    private function getTooltipImageHtml(): string
    {
        return sprintf(
            '<img src="%s"/>',
            $this->assetRepository->getUrl(self::IMAGE_PATH)
        );
    }
}
