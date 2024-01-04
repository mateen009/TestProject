<?php

namespace Amasty\Rma\Controller\Account;

use Amasty\Rma\Api\CustomerRequestRepositoryInterface;
use Amasty\Rma\Controller\FrontendRma;
use Amasty\Rma\Model\ConfigProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Registry;
use Magento\Sales\Api\OrderRepositoryInterface;
use AscentDigital\NetsuiteConnector\Helper\RMA;


class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var CustomerRequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var FrontendRma
     */
    private $frontendRma;

    /**
     * @var \AscentDigital\NetsuiteConnector\Helper\RMA
     */
    protected $helper;

    public function __construct(
        Session $customerSession,
        OrderRepositoryInterface $orderRepository,
        Registry $registry,
        ConfigProvider $configProvider,
        CustomerRequestRepositoryInterface $requestRepository,
        FrontendRma $frontendRma,
        RMA $helper,
        Context $context
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->orderRepository = $orderRepository;
        $this->registry = $registry;
        $this->requestRepository = $requestRepository;
        $this->configProvider = $configProvider;
        $this->frontendRma = $frontendRma;
        $this->helper = $helper;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        if (!($customerId = $this->customerSession->getCustomerId())) {
            return $this->_redirect('customer/account/login');
        }

        $orderId = (int)$this->getRequest()->getParam('order');
        // $orderId = $this->getRequest()->getParams();
        // echo "<pre>"; print_r($orderId);die;


        try {
            $order = $this->orderRepository->get($orderId);
        } catch (\Exception $exception) {
            $orderId = false;
        }

        if (!$orderId) {
            $this->messageManager->addWarningMessage(__('Order is not set'));

            return $this->_redirect(
                $this->_url->getUrl($this->configProvider->getUrlPrefix() . '/account/history')
            );
        }

        $items = $this->getRequest()->getParam('items');
        if (!is_array($items) || !$items) {
            $this->messageManager->addWarningMessage(__('Items were not selected'));

            return $this->_redirect(
                $this->_url->getUrl(
                    $this->configProvider->getUrlPrefix() . '/account/newreturn/order/' . $orderId
                )
            );
        }

        if ($this->configProvider->isReturnPolicyEnabled() && !$this->getRequest()->getParam('rmapolicy')) {
            $this->messageManager->addWarningMessage(__('You didn\'t agree to Privacy policy'));

            return $this->_redirect(
                $this->_url->getUrl(
                    $this->configProvider->getUrlPrefix() . '/account/newreturn/order/' . $orderId
                )
            );
        }

        $request = $this->requestRepository->create(
            $this->frontendRma->processNewRequest(
                $this->requestRepository,
                $order,
                $this->getRequest()
            )
        );
        $files = [];
        if ($jsonFiles = $this->getRequest()->getParam('attach-files')) {
            $files = json_decode($jsonFiles, true);
        }
        if (!empty($comment = $this->getRequest()->getParam('comment')) || !(empty($files))) {
            $this->frontendRma->saveNewReturnMessage($request, $comment, $files);
        }
        // netsuite rma call
        $response = $this->helper->returnAuthorizationNetsuite($order);

        return $this->_redirect(
            $this->configProvider->getUrlPrefix() . '/account/view',
            ['request' => $request->getRequestId()]
        );
    }
}
