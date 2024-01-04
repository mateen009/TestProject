<?php
namespace Cminds\Oapm\Model\Config\Backend;

class Cron extends \Magento\Framework\App\Config\Value
{
    const CRON_STRING_PATH = 'crontab/default/jobs/cminds_oapm_cleaning/schedule/cron_expr';
    const CRON_MODEL_PATH = 'crontab/default/jobs/cminds_oapm_cleaning/run/model';

    const XML_PATH_ACTIVE = 'groups/cminds_oapm/fields/auto_clean_active/value';
    const XML_PATH_TIME = 'groups/cminds_oapm/fields/auto_clean_time/value';
    const XML_PATH_INTERVAL = 'groups/cminds_oapm/fields/auto_clean_frequency/value';

    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $_configValueFactory;

    /**
     * @var string
     */
    protected $_runModelPath = '';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        $runModelPath = '',
        array $data = []
    ) {
        $this->_configValueFactory = $configValueFactory;
        $this->_runModelPath = $runModelPath;

        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function afterSave()
    {
        $isActive = $this->getData(self::XML_PATH_ACTIVE);
        $cronExprString = '';

        if ($isActive ) {
            $timeConfig = $this->getData(self::XML_PATH_TIME);
            $intervalConfig = $this->getData(self::XML_PATH_INTERVAL);

            $frequencyWeekly    = \Magento\Cron\Model\Config\Source\Frequency::CRON_WEEKLY;
            $frequencyMonthly   = \Magento\Cron\Model\Config\Source\Frequency::CRON_MONTHLY;

            $cronExprArray = [
                intval($timeConfig[1]), // minutes
                intval($timeConfig[0]), // hours
                $intervalConfig == $frequencyMonthly ? '1' : '*',
                '*',
                $intervalConfig == $frequencyWeekly ? '1' : '*',
            ];
            $cronExprString = join(' ', $cronExprArray);
        }

        try {
            $this->_configValueFactory->create()->load(
                self::CRON_STRING_PATH,
                'path'
            )->setValue(
                $cronExprString
            )->setPath(
                self::CRON_STRING_PATH
            )->save();

            $this->_configValueFactory->create()->load(
                self::CRON_MODEL_PATH,
                'path'
            )->setValue(
                $this->_runModelPath
            )->setPath(
                self::CRON_MODEL_PATH
            )->save();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t save the Cron expression.'));
        }

        return parent::afterSave();
    }
}
