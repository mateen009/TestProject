<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Config;

use Magento\Framework\App\Config\ValueFactory;

class SaveConfigValue
{
    /**
     * @var ValueFactory
     */
    private $configValueFactory;

    public function __construct(ValueFactory $configValueFactory)
    {
        $this->configValueFactory = $configValueFactory;
    }

    public function execute(string $path, string $value): void
    {
        $configValue = $this->configValueFactory->create()->load($path, 'path');
        $configValue->setValue($value);
        $configValue->setPath($path);
        $configValue->save();
    }
}
