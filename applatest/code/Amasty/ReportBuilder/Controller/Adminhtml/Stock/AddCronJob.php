<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Controller\Adminhtml\Stock;

use Amasty\ReportBuilder\Model\Cron\Schedule\AddNewJob;
use Amasty\ReportBuilder\Model\Cron\Schedule\IfJobExist;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;

class AddCronJob extends Action implements HttpPostActionInterface
{
    const JOB_CODE = 'amasty_report_builder_stock_update';

    /**
     * @var IfJobExist
     */
    private $ifJobExist;

    /**
     * @var AddNewJob
     */
    private $addNewJob;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        IfJobExist $ifJobExist,
        AddNewJob $addNewJob,
        LoggerInterface $logger,
        Context $context
    ) {
        parent::__construct($context);
        $this->ifJobExist = $ifJobExist;
        $this->addNewJob = $addNewJob;
        $this->logger = $logger;
    }

    /**
     * @return Json
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        if (!$this->ifJobExist->execute(self::JOB_CODE)) {
            try {
                $this->addNewJob->execute(self::JOB_CODE);
                $message = __('Cronjob has been added');
            } catch (Exception $e) {
                $this->logger->debug($e->getMessage());
                $message = __('Error adding cronjob to the queue');
            }
        } else {
            $message = __('Cronjob is already scheduled');
        }

        return $resultJson->setData(['message' => $message]);
    }
}
