<?php
namespace Cminds\Oapm\Controller\Checkout;

use Cminds\Oapm\Exception\Exception;
use Cminds\Oapm\Exception\InvalidOrderException;
use Magento\Framework\App\Action\Context;
use Cminds\Oapm\Model\Checkout;
use Cminds\Oapm\Logger\Logger;
use Cminds\Oapm\Model\Cron;
use Magento\Checkout\Model\Session;

class Finalize extends \Cminds\Oapm\Controller\AbstractCheckout
{
    /**
     * @var \Magento\Checkout\Model\Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Cminds\Oapm\Model\Checkout $oapmCheckout
     * @param \Cminds\Oapm\Logger\Logger $logger
     * @param \Cminds\Oapm\Model\Cron $cron
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        Context $context,
        Checkout $oapmCheckout,
        Logger $logger,
        Cron $cron,
        Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $oapmCheckout, $logger, $cron);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $redirectResult = $this->resultRedirectFactory->create();
        $redirectResult->setRefererUrl();

        try {
            $hash = $this->getRequest()->getParam('order');
            if (empty($hash)) {
                throw new InvalidOrderException(__('Order hash parameter is missing.'));
            }

            $this->oapmCheckout
                ->setHash($hash)
                ->prepareQuote();

            $this->checkoutSession->setOapmReloadCheckoutProgressFlag(true);
            $redirectResult->setUrl($this->oapmCheckout->getCheckoutPath());
            return $redirectResult;
        } catch (InvalidOrderException $e) {
            $this->logger->debug($e->getMessage());

            $this->messageManager->addError(__('Your finalize order url is not active any more. Order can not be finalized.'));

            return $redirectResult;
        } catch (Exception $e) {
            $this->logger->debug($e->getMessage());

            $this->messageManager->addError(__('During processing your order error occurred. Please try again later.'));

            return $redirectResult;
        }
    }
}
