<?php
namespace Cminds\Oapm\Controller\Adminhtml\Oapm;

class Index extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Cminds_Oapm::sales_oapm';

    const TITLE = 'OAPM Orders';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_addContent(
            $this->_view->getLayout()->createBlock(
                \Cminds\Oapm\Block\Adminhtml\Sales\Order::class
            )
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__(self::TITLE));
        $this->_view->renderLayout();
    }

    /**
     * Init layout, menu and breadcrumb
     *
     * @return \Cminds\Oapm\Controller\Adminhtml\Oapm\Index
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(self::ADMIN_RESOURCE)
        ->_addBreadcrumb(__('Sales'), __('Sales'))
        ->_addBreadcrumb(__(self::TITLE), __(self::TITLE));

        return $this;
    }
}
