<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Controller\Adminhtml\Report;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Exception\NotExistColumnException;
use Amasty\ReportBuilder\Exception\NotExistTableException;
use Amasty\ReportBuilder\Model\Backend\Report\GetInvalidColumns;
use Amasty\ReportBuilder\Model\Cache\Type;
use Amasty\ReportBuilder\Model\Validation\ReportFailedFlag;
use Amasty\ReportBuilder\Model\View\ReportLoader;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Cache;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class View extends Action implements HttpGetActionInterface
{
    const ADMIN_RESOURCE = 'Amasty_ReportBuilder::report_view';

    /**
     * @var ReportLoader
     */
    private $reportLoader;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var ReportFailedFlag
     */
    private $reportFailedFlag;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var GetInvalidColumns
     */
    private $getInvalidColumns;

    public function __construct(
        Context $context,
        ReportLoader $reportLoader,
        ReportFailedFlag $reportFailedFlag,
        Cache $cache,
        GetInvalidColumns $getInvalidColumns
    ) {
        parent::__construct($context);
        $this->reportLoader = $reportLoader;
        $this->context = $context;
        $this->reportFailedFlag = $reportFailedFlag;
        $this->cache = $cache;
        $this->getInvalidColumns = $getInvalidColumns;
    }

    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        try {
            $report = $this->reportLoader->execute();
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage(__('This Report no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }

        try {
            $resultPage->getConfig()->getTitle()->prepend($report->getName());
        } catch (NotExistColumnException | NotExistTableException $e) {
            $this->reportFailedFlag->set();
        }

        $resultPage->renderResult($this->_response);
        if ($this->reportFailedFlag->get()) {
            $this->processFailedReport();

            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/edit', [
                ReportInterface::REPORT_ID => $report->getReportId()
            ]);
        } else {
            return $this->_response;
        }
    }

    private function processFailedReport(): void
    {
        $this->cache->remove(Type::CACHE_ID);

        if ($invalidColumns = $this->getInvalidColumns->execute(true)) {
            $this->messageManager->addComplexErrorMessage('addInvalidColumnsMessage', [
                'invalid_columns' => $invalidColumns
            ]);
        }
    }
}
