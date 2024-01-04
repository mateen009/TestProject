<?php

namespace Amasty\ReportBuilder\Model\Cache;

use Magento\Framework\Cache\Frontend\Decorator\TagScope;

class Type extends TagScope
{
    const TYPE_IDENTIFIER = 'amasty_report_builder_scheme';

    const CACHE_TAG = 'AMASTY_REPORT_BUILDER_SCHEME';

    const CACHE_ID = 'amasty_report_builder_scheme';

    public function __construct(\Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool)
    {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }
}
