<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Controller\Adminhtml\Report;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Api\ReportRepositoryInterface;
use Amasty\ReportBuilder\Model\Backend\Report\DataCollector;
use Amasty\ReportBuilder\Model\View\Ui\Component\Listing\BookmarkCleaner;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Request\DataPersistorInterface;

class Save extends Action implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'Amasty_ReportBuilder::report_edit';

    /**
     * @var ReportRepositoryInterface
     */
    private $reportRepository;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var DataCollector
     */
    private $dataCollector;

    /**
     * @var BookmarkCleaner
     */
    private $bookmarkCleaner;

    public function __construct(
        Context $context,
        ReportRepositoryInterface $reportRepository,
        DataPersistorInterface $dataPersistor,
        LoggerInterface $logger,
        DataCollector $dataCollector,
        BookmarkCleaner $bookmarkCleaner
    ) {
        parent::__construct($context);
        $this->reportRepository = $reportRepository;
        $this->dataPersistor = $dataPersistor;
        $this->logger = $logger;
        $this->dataCollector = $dataCollector;
        $this->bookmarkCleaner = $bookmarkCleaner;
    }

    /**
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Amasty_ReportBuilder::Amasty_ReportBuilder');
        $resultPage->getConfig()->getTitle()->prepend(__('Amasty Custom Reports Builder'));

        return $resultPage;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $reportId = (int)$this->getRequest()->getParam(ReportInterface::REPORT_ID);

        try {
            if ($reportId) {
                /**
                 * @var ReportInterface $report
                 */
                die('here');
                $report = $this->reportRepository->getById($reportId);
            } else {
                $report = $this->reportRepository->getNew();
            }
            $this->dataCollector->execute($report, $this->getRequest()->getPostValue());
            $this->reportRepository->save($report);
            $this->messageManager->addSuccessMessage(__('You have saved the Report.'));

            $this->dataPersistor->clear(ReportInterface::PERSIST_NAME);
            $this->bookmarkCleaner->cleanByReportId($report->getReportId());

            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', [
                    ReportInterface::REPORT_ID => $report->getReportId()
                ]);
            } elseif ($this->getRequest()->getParam('redirect', false)) {
                $action = $this->getRequest()->getParam('redirect', false);

                return $resultRedirect->setPath('*/*/' . $action, [
                    ReportInterface::REPORT_ID => $report->getReportId()
                ]);
            }

        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            $this->messageManager->addErrorMessage(
                __('A Report with the same id already exists.')
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultRedirectFactory->create();
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage(__('This Report no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred while saving report. See exception log for details')
            );
            $this->logger->critical($e->getMessage(), ['exception' => $e]);
            $this->dataPersistor->set(ReportInterface::PERSIST_NAME, $report->getData());
            $resultRedirect = $this->resultRedirectFactory->create();
        }

        return $resultRedirect->setPath('*/*/');
    }
}
