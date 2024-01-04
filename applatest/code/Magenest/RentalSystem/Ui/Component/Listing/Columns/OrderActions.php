<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magenest\RentalSystem\Model\Status;

class OrderActions extends Column
{
    const URL_PATH_VIEW_ORDER   = 'sales/order/view';
    const URL_PATH_SET_STATUS   = 'rentalsystem/order/setStatus';
    const URL_PATH_SEND_RECEIPT = 'rentalsystem/order/sendReceipt';

    /**
     * URL builder
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * constructor
     *
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['order_id'])) {
                    $viewUrlPath                  = self::URL_PATH_VIEW_ORDER;
                    $urlEntityParamName           = 'order_id';
                    $item[$this->getData('name')] = [
                        'view' => [
                            'href'   => $this->_urlBuilder->getUrl(
                                $viewUrlPath,
                                [
                                    $urlEntityParamName => $item['order_id']
                                ]
                            ),
                            'label'  => __('View Order'),
                            'target' => '_blank'
                        ]
                    ];

                    $item[$this->getData('name')]['set_delivering'] = [
                        'href'    => $this->_urlBuilder->getUrl(
                            self::URL_PATH_SEND_RECEIPT,
                            [
                                'id' => $item['id'],
                            ]
                        ),
                        'label'   => __('Resend Receipt'),
                        'hidden'  => false,
                        'confirm' => [
                            'title' => __('Do you want to resend receipt to customer %1 for item %2?', $item['customer_name'], $item['title']),
                        ]
                    ];

                    if (isset($item['status'])) {
                        if ($item['status'] == "<span class=\"grid-severity-minor\">Pending</span>")
                            $item[$this->getData('name')]['set_delivering'] = [
                                'href'    => $this->_urlBuilder->getUrl(
                                    self::URL_PATH_SET_STATUS,
                                    [
                                        'id'     => $item['id'],
                                        'status' => Status::DELIVERING
                                    ]
                                ),
                                'label'   => __('Set as Delivering'),
                                'hidden'  => false,
                                'confirm' => [
                                    'title' => __('Do you want to set item %1 for customer %2 as Delivering?', $item['title'], $item['customer_name']),
                                ]
                            ];
                        if ($item['status'] == "<span class=\"grid-severity-minor\">Delivering</span>")
                            $item[$this->getData('name')]['set_delivered'] = [
                                'href'    => $this->_urlBuilder->getUrl(
                                    self::URL_PATH_SET_STATUS,
                                    [
                                        'id'     => $item['id'],
                                        'status' => Status::DELIVERED
                                    ]
                                ),
                                'label'   => __('Set as Delivered'),
                                'hidden'  => false,
                                'confirm' => [
                                    'title' => __('Do you want to set item %1 for customer %2 as Delivered?', $item['title'], $item['customer_name']),
                                ]
                            ];

                        if ($item['status'] == "<span class=\"grid-severity-minor\">Delivered</span>") {
                            $item[$this->getData('name')]['set_returing'] = [
                                'href'    => $this->_urlBuilder->getUrl(
                                    self::URL_PATH_SET_STATUS,
                                    [
                                        'id'     => $item['id'],
                                        'status' => Status::RETURNING
                                    ]
                                ),
                                'label'   => __('Set as Returning'),
                                'hidden'  => false,
                                'confirm' => [
                                    'title' => __('Do you want to set item %1 for customer %2 as Returning?', $item['title'], $item['customer_name']),
                                ]
                            ];
                            $item[$this->getData('name')]['set_complete'] = [
                                'href'    => $this->_urlBuilder->getUrl(
                                    self::URL_PATH_SET_STATUS,
                                    [
                                        'id'     => $item['id'],
                                        'status' => Status::COMPLETE
                                    ]
                                ),
                                'label'   => __('Set as Complete'),
                                'hidden'  => false,
                                'confirm' => [
                                    'title' => __('Do you want to set item %1 for customer %2 as Complete?', $item['title'], $item['customer_name']),
                                ]
                            ];
                        }

                        if ($item['status'] == "<span class=\"grid-severity-notice\">Returning</span>")
                            $item[$this->getData('name')]['set_complete'] = [
                                'href'    => $this->_urlBuilder->getUrl(
                                    self::URL_PATH_SET_STATUS,
                                    [
                                        'id'     => $item['id'],
                                        'status' => Status::COMPLETE
                                    ]
                                ),
                                'label'   => __('Set as Complete'),
                                'hidden'  => false,
                                'confirm' => [
                                    'title'   => __('Do you want to set item %1 for customer %2 as Complete?', $item['title'], $item['customer_name']),
                                ]
                            ];

                    }
                }
            }
        }

        return $dataSource;
    }
}
