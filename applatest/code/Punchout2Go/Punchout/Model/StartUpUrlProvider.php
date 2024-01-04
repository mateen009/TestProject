<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Punchout2Go\Punchout\Api\SessionInterface;
use Punchout2Go\Punchout\Api\StartUpUrlProviderInterface;
use Punchout2Go\Punchout\Helper\Session as SessionHelper;
use Magento\Framework\App\RequestInterface;
use Punchout2Go\Punchout\Helper\Data as DataHelper;

class StartUpUrlProvider implements StartUpUrlProviderInterface
{
    /**
     * @var SessionHelper
     */
    private $sessionHelper;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /** @var RequestInterface  */
    private $request;

    /** @var DataHelper */
    private $dataHelper;

    /**
     * StartUpUrlProvider constructor.
     *
     * @param SessionHelper $helper
     * @param UrlInterface $url
     * @param ProductRepositoryInterface $productRepository
     * @param RequestInterface $request
     */
    public function __construct(
        SessionHelper $helper,
        UrlInterface $url,
        ProductRepositoryInterface $productRepository,
        RequestInterface $request,
        DataHelper $dataHelper
    ) {
        $this->sessionHelper = $helper;
        $this->url = $url;
        $this->productRepository = $productRepository;
        $this->request = $request;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param SessionInterface $session
     * @return string
     */
    public function getUrl(SessionInterface $session): string
    {
        $query = $this->getRedirectQueryParams($session);
        $startUrl = $this->sessionHelper->getSessionStartupUrl();
        if ($session->isEdit()) {
            $startUrl = $this->sessionHelper->getSessionStartupEditUrl();
        }
        $productUrl = $this->getProductUrl($session);
        if (!$productUrl) {
            return $this->url->getUrl($startUrl, ['_query' => $query]);
        }
        return $productUrl . "?" . http_build_query($query);
    }

    /**
     * @return string
     */
    private function getProductUrl(SessionInterface $session): string
    {
        $sku = $session->getInItemSku();
        if (!$sku) {
            return '';
        }
        if (in_array($sku, $this->sessionHelper->getIgnoreItems())) {
            return '';
        }
        try {
            /** @var Product $product */
            $product = $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            return '';
        }
        $url = $this->sessionHelper->getSessionStartupEditItemUrl();
        return str_replace(['{item_url}'], [$product->getProductUrl()], $url);
    }

    /**
     * @param SessionInterface $session
     * @return int[]
     */
    private function getRedirectQueryParams(SessionInterface $session)
    {
        $queryParams = [$this->sessionHelper->getFirstLoadParam() => 1];
        if ($this->sessionHelper->isIncludePosidInRedirect()) {
            $queryParams['posid'] = $session->getPunchoutSessionId();
        }

        if ($this->dataHelper->isMaintainQueryString()) {
            return array_merge($queryParams, $this->request->getQuery()->toArray());
        }

        return $queryParams;
    }
}
