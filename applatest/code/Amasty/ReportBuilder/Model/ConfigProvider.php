<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model;

use \Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigProvider
{
    const SECTION_NAME = 'amasty_report_builder/';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }
}
