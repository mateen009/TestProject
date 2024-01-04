<?php
namespace Magenest\RentalSystem\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;

abstract class ProductReport extends \Magento\Reports\Controller\Adminhtml\Report\Product
{
    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param Date $dateFilter
     * @param LoggerInterface $logger
     * @param TimezoneInterface $timezone
     * @param BackendHelper|null $backendHelperData
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        Date $dateFilter,
        LoggerInterface $logger,
        TimezoneInterface $timezone
    ) {
        $this->logger = $logger;
        parent::__construct($context, $fileFactory, $dateFilter, $timezone);
    }

    /**
     * Check ACL
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_RentalSystem::productreport');
    }
}
