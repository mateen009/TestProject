<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder\Column;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Magento\Config\Model\Config\Source\Yesno;

class BooleanModifier implements TypeModifierInterface
{
    public function execute(array $data): array
    {
        $data[ColumnInterface::SOURCE_MODEL] = Yesno::class;
        return $data;
    }
}
