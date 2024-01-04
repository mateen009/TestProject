<?php
namespace Cminds\Oapm\Plugin\Webapi\Controller\Rest;

use Magento\Checkout\Model\Session as CheckoutSession;
use Cminds\Oapm\Helper\Config as OapmConfig;
use Magento\Webapi\Controller\Rest\Router;
use Magento\Framework\Webapi\Rest\Request as RestRequest;

class RequestValidator
{
    /**
     * @var CheckoutSession $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @var OapmConfig
     */
    protected $helperConfig;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var RestRequest
     */
    private $request;

    /**
     * @var array
     */
    protected $skipValidation = [
        'V1/carts/mine/estimate-shipping-methods',
        'V1/carts/mine/shipping-information',
        'V1/carts/mine/payment-information'
    ];

    /**
     * @param CheckoutSession $checkoutSession,
     * @param OapmConfig $helperConfig
     * @param Router $router
     * @param RestRequest $request
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        OapmConfig $helperConfig,
        Router $router,
        RestRequest $request
    ){
        $this->checkoutSession = $checkoutSession;
        $this->helperConfig = $helperConfig;
        $this->router = $router;
        $this->request = $request;
    }

    // this plugin is needed to skip validation
    // Consumer is not authorized to access %resources error
    public function aroundValidate(
        \Magento\Webapi\Controller\Rest\RequestValidator $subject,
        callable $proceed
    ){
        // if module enabled in config
        if($this->helperConfig->isEnabled()){
            if($this->checkoutSession->getData('oapm_order_id')){
                $route = $this->router->match($this->request);
                if( in_array(trim($this->request->getPathInfo(), '/'), $this->skipValidation ) ){
                    if ($route->isSecure() && !$this->request->isSecure()) {
                        throw new \Magento\Framework\Webapi\Exception(__('Operation allowed only in HTTPS'));
                    }
                } else {
                    return $proceed();
                }
            } else {
                return $proceed();
            }
        } else {
            return $proceed();
        }
    }
}