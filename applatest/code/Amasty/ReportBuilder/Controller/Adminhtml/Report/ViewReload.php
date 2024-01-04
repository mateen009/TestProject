<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Controller\Adminhtml\Report;

use Amasty\ReportBuilder\Model\View\ReportLoader;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class ViewReload extends Action implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'Amasty_ReportBuilder::report_view';

    /**
     * @var ReportLoader
     */
    private $reportLoader;

    public function __construct(
        Context $context,
        ReportLoader $reportLoader
    ) {
        parent::__construct($context);
        $this->reportLoader = $reportLoader;
    }

    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        try {
            $this->reportLoader->execute();
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage(__('This Report no longer exists.'));

            return $resultRaw;
        }

        $rawContent = $resultPage->addHandle('amreportbuilder_report_view')
            ->getLayout()
            ->renderElement('amreportbuilder.report.chart');
        $resultRaw->setContents($rawContent);

        return $resultRaw;
    }
}
