<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Controller\Adminhtml\Report;

use Amasty\ReportBuilder\Api\Data\ReportInterface;
use Amasty\ReportBuilder\Api\ReportRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Action\HttpGetActionInterface;

class Delete extends Action implements HttpGetActionInterface
{
    const ADMIN_RESOURCE = 'Amasty_ReportBuilder::report_delete';

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * @var ReportRepositoryInterface
     */
    private $reportRepository;

    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        ReportRepositoryInterface $reportRepository
    ) {
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->reportRepository = $reportRepository;
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

        if ($reportId) {
            try {
                $this->reportRepository->deleteById($reportId);
                $this->messageManager->addSuccessMessage(__('Report was successfully removed.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }

        } else {
            $this->messageManager->addErrorMessage(__('This Report no longer exists.'));
        }

        if ($this->getRequest()->getParam('redirect')) {
            $action = $this->getRequest()->getParam('redirect');
            $params = [];
            if ($entityId = $this->getRequest()->getParam('entity_id')) {
                $params = [ReportInterface::REPORT_ID => $entityId];
            }

            return $this->resultRedirectFactory->create()->setPath('*/*/' . $action, $params);
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
