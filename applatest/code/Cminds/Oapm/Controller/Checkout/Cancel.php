<?php
namespace Cminds\Oapm\Controller\Checkout;

use Cminds\Oapm\Exception\Exception;
use Cminds\Oapm\Exception\InvalidOrderException;

class Cancel extends \Cminds\Oapm\Controller\AbstractCheckout
{
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
                ->cancelOrder();

            $this->messageManager->addSuccess(__('Order has been canceled successfully.'));

            return $redirectResult;
        } catch (InvalidOrderException $e) {
            $this->logger->debug($e->getMessage());

            $this->messageManager->addError(__('Your cancel order url is not active any more.'));

            return $redirectResult;
        } catch (Exception $e) {
            $this->logger->debug($e->getMessage());

            $this->messageManager->addError(__('During processing your order error occurred. Please try again later.'));

            return $redirectResult;
        }
    }
}
