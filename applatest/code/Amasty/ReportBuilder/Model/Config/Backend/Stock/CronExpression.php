<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Config\Backend\Stock;

use Amasty\ReportBuilder\Model\Config\SaveConfigValue;
use Exception;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

class CronExpression extends ConfigValue
{
    const CRON_STRING_PATH = 'crontab/default/jobs/amasty_report_builder_stock_update/schedule/cron_expr';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SaveConfigValue
     */
    private $saveConfigValue;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        SaveConfigValue $saveConfigValue,
        LoggerInterface $logger,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->logger = $logger;
        $this->saveConfigValue = $saveConfigValue;
    }

    /**
     * @return CronExpression
     */
    public function afterSave()
    {
        try {
            $this->saveConfigValue->execute(self::CRON_STRING_PATH, $this->generateCronExpr());
        } catch (Exception $e) {
            $this->logger->error(__('We can\'t save the cron expression.'));
        }

        return parent::afterSave();
    }

    private function generateCronExpr(): string
    {
        $time = $this->getData('groups/stock_data/fields/time/value');
        $frequency = $this->getData('groups/stock_data/fields/frequency/value');

        $cronExprArray = [
            (int)$time[1], //Minute
            (int)$time[0], //Hour
            $frequency == \Magento\Cron\Model\Config\Source\Frequency::CRON_MONTHLY ? '1' : '*', //Day of the Month
            '*', //Month of the Year
            $frequency == \Magento\Cron\Model\Config\Source\Frequency::CRON_WEEKLY ? '1' : '*', //Day of the Week
        ];

        return implode(' ', $cronExprArray);
    }
}
