<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Controller\Adminhtml\Report;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Api\ReportRepositoryInterface;
use Amasty\ReportBuilder\Model\ReportRegistry;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends Action implements HttpGetActionInterface
{
    const ADMIN_RESOURCE = 'Amasty_ReportBuilder::report_edit';

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * @var ReportRepositoryInterface
     */
    private $reportRepository;

    /**
     * @var ReportRegistry
     */
    private $registry;

    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        ReportRepositoryInterface $reportRepository,
        ReportRegistry $registry
    ) {
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->reportRepository = $reportRepository;
        $this->registry = $registry;
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
        $reportId = (int)$this->getRequest()->getParam(ReportInterface::REPORT_ID);

        try {
            if ($reportId) {
                /**
                 * @var ReportInterface $report
                 */
                $report = $this->reportRepository->getById($reportId);
            } else {
                $report = $this->reportRepository->getNew();
            }

            $this->registry->setReport($report);
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage(__('This Report no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $text = $report->getReportId() ? __('Edit Report "%1"', $report->getName()) : __('New Report');
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend($text);

        return $resultPage;
    }
}
