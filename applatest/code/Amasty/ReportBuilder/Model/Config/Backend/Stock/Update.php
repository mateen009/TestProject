<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Config\Backend\Stock;

use Amasty\ReportBuilder\Model\Config\SaveConfigValue;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Update extends ConfigValue
{
    /**
     * @var SaveConfigValue
     */
    private $saveConfigValue;

    public function __construct(
        SaveConfigValue $saveConfigValue,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->saveConfigValue = $saveConfigValue;
    }

    /**
     * @return Update
     */
    public function afterSave()
    {
        if (!$this->getValue()) {
            $this->saveConfigValue->execute(CronExpression::CRON_STRING_PATH, '');
        }

        return parent::afterSave();
    }
}
