<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Controller\Adminhtml\Report;

use Amasty\ReportBuilder\Api\ReportRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Amasty\ReportBuilder\Model\ResourceModel\Report\CollectionFactory;
use \Magento\Backend\Model\View\Result\ForwardFactory;

class MassDelete extends Action implements HttpPostActionInterface
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

    /**
     * @var CollectionFactory
     */
    private $reportCollectionFactory;

    /**
     * @var Filter
     */
    private $filter;

    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        ReportRepositoryInterface $reportRepository,
        CollectionFactory $reportCollectionFactory,
        Filter $filter
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->reportRepository = $reportRepository;
        $this->reportCollectionFactory = $reportCollectionFactory;
        $this->filter = $filter;
        parent::__construct($context);
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
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->reportCollectionFactory->create());
        $collectionSize = $collection->getSize();

        foreach ($collection as $report) {
            $this->reportRepository->delete($report);
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
