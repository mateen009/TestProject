<?php
namespace Cminds\Oapm\Controller;

abstract class AbstractCheckout extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Cminds\Oapm\Model\Checkout $oapmCheckout
     */
    protected $oapmCheckout;

    /**
     * @var \Cminds\Oapm\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Cminds\Oapm\Logger\Logger
     */
    protected $cron;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Cminds\Oapm\Model\Checkout $oapmCheckout
     * @param \Cminds\Oapm\Logger\Logger $logger
     * @param \Cminds\Oapm\Model\Cron $cron
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Cminds\Oapm\Model\Checkout $oapmCheckout,
        \Cminds\Oapm\Logger\Logger $logger,
        \Cminds\Oapm\Model\Cron $cron
    ) {
        $this->oapmCheckout = $oapmCheckout;
        $this->logger = $logger;
        $this->cron = $cron;
        parent::__construct($context);
    }

    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $response = parent::dispatch($request);

        $this->cron->processReminders();

        return $response;
    }
}
