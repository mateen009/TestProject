<?php
namespace Cminds\Oapm\Controller\Adminhtml\Oapm;

use Cminds\Oapm\Exception\Exception;
use Cminds\Oapm\Exception\InvalidOrderException;
use Magento\Backend\App\Action\Context as ActionContext;
use Cminds\Oapm\Model\OrderFactory as OapmOrderFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Cminds\Oapm\Model\Order as OapmOrder;

class Approve extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Cminds_Oapm::sales_oapm';

    /**
     * @var OapmOrderFactory
     */
    protected $oapmOrderFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ActionContext $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ActionContext $context,
        OapmOrderFactory $oapmOrderFactory,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger
    ) {
        $this->oapmOrderFactory = $oapmOrderFactory;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        try {
            $order_id = $this->getRequest()->getParam("order_id", null);

            if ($order_id === null) {
                throw new InvalidOrderException(__("Missing Order ID"));
            }

            $pendingOrder = $this->oapmOrderFactory->create()->loadByOrderId($order_id);

            if (! $pendingOrder->getId()) {
                throw new InvalidOrderException(__("Pending order with specified ID was not found"));
            }

            $order = $this->orderRepository->get($order_id);

            if (! $order->getId()) {
                throw new InvalidOrderException(__("Order with specified ID was not found"));
            }

            if ($pendingOrder->getStatus() == OapmOrder::STATUS_APPROVED) {
                throw new InvalidOrderException(__("Order #%1 was already approved in the past", $order->getIncrementId()));
            }

            if ($pendingOrder->getStatus() == OapmOrder::STATUS_FINALIZED) {
                throw new InvalidOrderException(__("Can't approve a finalized order"));
            }

            if ($pendingOrder->getStatus() == OapmOrder::STATUS_CANCELED) {
                throw new InvalidOrderException(__("Can't approve a canceled order"));
            }

            $pendingOrder
                ->approve()
                ->notifyCustomer()
                ->save();

            $this->messageManager->addSuccess(
                __("Order #%1 was approved. Notification was sent to %2", $order->getIncrementId(), $order->getCustomerName())
            );
        } catch (InvalidOrderException $e) {
            $this->logger->debug($e->getMessage());

            $this->messageManager->addError($e->getMessage());
        } catch (Exception $e) {
            $this->logger->debug($e->getMessage());

            $this->messageManager->addError($e->getMessage());
        }

        $this->_redirect('*/*/index');
    }
}
