<?php
/**
 * Created by PhpStorm.
 * User: ducanh
 * Date: 21/01/2019
 * Time: 11:14
 */

namespace Magenest\RentalSystem\Ui\DataProvider\Product\Form\Modifier;

use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magenest\RentalSystem\Model\RentalFactory;
use Magenest\RentalSystem\Model\RentalPriceFactory;
use Magenest\RentalSystem\Model\RentalOptionFactory;
use Magenest\RentalSystem\Model\RentalOptionTypeFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class RentalProduct extends AbstractModifier
{
    const PRODUCT_TYPE                   = 'rental';
    const CONTROLLER_ACTION_EDIT_PRODUCT = 'catalog_product_edit';
    const CONTROLLER_ACTION_NEW_PRODUCT  = 'catalog_product_new';
    const XML_PATH_DEFAULT_ADDRESS       = 'rental_system/rental/default_address';

    const DELIVERY_SHIPPING     = 0;
    const DELIVERY_LOCAL_PICKUP = 1;
    const DELIVERY_BOTH         = 2;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var RentalFactory
     */
    protected $_rentalFactory;

    /**
     * @var RentalPriceFactory
     */
    protected $_rentalPriceFactory;

    /**
     * @var RentalOptionFactory
     */
    protected $_rentalOptionFactory;

    /**
     * @var RentalOptionTypeFactory
     */
    protected $_rentalOptionTypeFactory;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * RentalProduct constructor.
     *
     * @param RequestInterface $request
     * @param LocatorInterface $locator
     * @param RentalFactory $rentalFactory
     * @param RentalPriceFactory $rentalPriceFactory
     * @param RentalOptionFactory $rentalOptionFactory
     * @param RentalOptionTypeFactory $rentalOptionTypeFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        RequestInterface $request,
        LocatorInterface $locator,
        RentalFactory $rentalFactory,
        RentalPriceFactory $rentalPriceFactory,
        RentalOptionFactory $rentalOptionFactory,
        RentalOptionTypeFactory $rentalOptionTypeFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->request                  = $request;
        $this->locator                  = $locator;
        $this->_rentalFactory           = $rentalFactory;
        $this->_rentalPriceFactory      = $rentalPriceFactory;
        $this->_rentalOptionFactory     = $rentalOptionFactory;
        $this->_rentalOptionTypeFactory = $rentalOptionTypeFactory;
        $this->_scopeConfig             = $scopeConfig;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function modifyData(array $data)
    {
        $product   = $this->locator->getProduct();
        $productId = $product->getId();
        if ($this->isRentalProduct()) {
            if (isset($data[strval($productId)]['product']['price']) && $data[strval($productId)]['product']['price'] < 0) {
                $data[strval($productId)]['product']['price'] = 0;
            }

            $rentalModel = $this->_rentalFactory->create()->load($productId, 'product_id');
            if (!empty($rentalModel->getData())) {
                $rentalData = $rentalModel->getData();

                if (!empty($rentalData['type'])) {
                    // $data[strval($productId)]['rental']['type'] = ($rentalData['type'] == 0) ? 'shipping' : 'local_pickup';
                    if ($rentalData['type'] == self::DELIVERY_SHIPPING) $data[strval($productId)]['rental']['type'] = 'shipping';
                    if ($rentalData['type'] == self::DELIVERY_LOCAL_PICKUP) $data[strval($productId)]['rental']['type'] = 'local_pickup';
                    if ($rentalData['type'] == self::DELIVERY_BOTH) $data[strval($productId)]['rental']['type'] = 'chosen_customer';
                }

                $data[strval($productId)]['rental']['max_duration']   = !empty($rentalData['max_duration']) ? $rentalData['max_duration'] : 0;
                $data[strval($productId)]['rental']['lead_time']      = $rentalData['lead_time'];
                $data[strval($productId)]['rental']['pickup_address'] = $rentalData['pickup_address'];
                $data[strval($productId)]['rental']['initial_qty']    = $rentalData['initial_qty'];
                $data[strval($productId)]['rental']['email_template'] = $rentalData['email_template'];
                $data[strval($productId)]['rental']['hold']           = !empty($rentalData['hold']) ? $rentalData['hold'] : 0;

                $priceModel = $this->_rentalPriceFactory->create()->getCollection()
                    ->addFieldToFilter('product_id', $productId)->getFirstItem();

                if (!empty($priceModel->getData())) {
                    $priceData        = $priceModel->getData();
                    $basePeriod       = preg_split('/(?<=[0-9])(?=[a-z]+)/i', $priceData['base_period']);
                    $additionalPeriod = preg_split('/(?<=[0-9])(?=[a-z]+)/i', $priceData['additional_period']);

                    $data[strval($productId)]['rental']['row'][0]['base_price']       = $priceData['base_price'];
                    $data[strval($productId)]['rental']['row'][0]['base_period']      = $basePeriod[0];
                    $data[strval($productId)]['rental']['row'][0]['base_period_unit'] = $basePeriod[1];

                    $data[strval($productId)]['rental']['row'][0]['additional_price']       = $priceData['additional_price'];
                    $data[strval($productId)]['rental']['row'][0]['additional_period']      = $additionalPeriod[0];
                    $data[strval($productId)]['rental']['row'][0]['additional_period_unit'] = isset($additionalPeriod[1]) ? $additionalPeriod[1] : null;
                }

                $optionData = $this->getOptionData($productId);

                $data[strval($productId)]['rental']['additional_options'] = $optionData;

//                $data[strval($productId)]['advanced_inventory_modal']['stock_data']['manage_stock'] = "No";
            }
        }

        return $data;
    }

    /**
     * @param $productId
     *
     * @return array
     */
    public function getOptionData($productId)
    {
        $data       = [];
        $options    = $this->_rentalOptionFactory->create()->getCollection()
            ->addFilter('product_id', $productId);
        $sizeOption = sizeof($options);
        for ($j = 0; $j < $sizeOption; $j++) {
            $option = $this->_rentalOptionFactory->create()->getCollection()
                ->addFilter('product_id', $productId)
                ->addFilter('option_id', $j)->getLastItem();

            $type = $option->getData('type');

            if (!empty($option->getData('id')))
                $row = $this->getOptionTypeData($productId, $option->getData('id'));

            $data[$j] = [
                'record_id'    => $option->getData('option_id'),
                'type'         => $type,
                'is_required'  => $option->getData('is_required'),
                'option_title' => $option->getData('option_title'),
                'id_option'    => $option->getData('id'),
                'row'          => !empty($row) ? $row : null
            ];
        }

        return $data;
    }

    /**
     * @param $productId
     * @param $optionId
     *
     * @return array
     */
    public function getOptionTypeData($productId, $optionId)
    {
        $data  = [];
        $types = $this->_rentalOptionTypeFactory->create()->getCollection()
            ->addFilter('product_id', $productId)
            ->addFilter('option_id', $optionId);
        foreach ($types as $type) {
            /** @var \Magenest\RentalSystem\Model\RentalOptionType $type */
            $number        = $type->getData('option_number');
            $data[$number] = [
                'id_type'   => $type->getId(),
                'record_id' => $type->getData('option_number'),
                'option'    => $type->getData('option_title'),
                'price'     => !empty($type->getData('price')) ? $type->getData('price') : 0,
            ];
        }

        return $data;
    }

    /**
     * @param array $meta
     *
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $rentalProduct = $this->isRentalProduct();
        if (!$rentalProduct) {
            //disable rental fieldset
            $meta['rental']   = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'disabled' => true,
                            'visible'  => false
                        ]
                    ]
                ],
                'children'  => [
                    'rental_price' => [
                        'children' => [
                            'base_price'  => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'disabled' => true,
                                            'visible'  => false
                                        ]
                                    ]
                                ]
                            ],
                            'base_period' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'disabled' => true,
                                            'visible'  => false
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                ]
            ];

        } else {
            $currencySymbol = $this->locator->getStore()->getBaseCurrency()->getCurrencySymbol();
            //add symbol, default address
            $meta['rental']   = [
                'children' => [
                    'rental_price'         => [
                        'children' => [
                            'base_price'       => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'addbefore' => $currencySymbol
                                        ]
                                    ]
                                ]
                            ],
                            'additional_price' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'addbefore' => $currencySymbol
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'rental_delivery_type' => [
                        'children' => [
                            'pickup_address' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'default' => $this->_scopeConfig->getValue(self::XML_PATH_DEFAULT_ADDRESS)
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            //disable manage stock
            $meta['advanced_inventory_modal']['children']['stock_data']['children']['use_config_manage_stock']['arguments']['data']['config'] = [
                'value'   => 0,
                'visible' => false
            ];
            $meta['advanced_inventory_modal']['children']['stock_data']['children']['manage_stock']['arguments']['data']['config']            = [
                'value'   => 0,
                'visible' => false
            ];

            //hide default price field and advanced pricing
            $meta['product-details']['children']['container_price']['arguments']['data']['config']['visible'] = false;
            $meta['product-details']['children']['container_price']['children']['price']['arguments']['data']['config']['default'] = 0;

            //hide advanced inventory and stock status when creating new rental product
            if (self::PRODUCT_TYPE == $this->request->getParam('type')) {
                $meta['product-details']['children']['quantity_and_stock_status_qty']['children']['advanced_inventory_button']['arguments']['data']['config']['visible']      = false;
                $meta['product-details']['children']['container_quantity_and_stock_status']['children']['quantity_and_stock_status']['arguments']['data']['config']['imports'] = [
                    'visible' => '!${$.provider}:data.product.stock_data.manage_stock'
                ];
            }
        }

        return $meta;
    }


    /**
     * @return bool
     */
    private function isRentalProduct()
    {
        $actionName = $this->request->getFullActionName();
        $isRental   = false;
        if ($actionName == self::CONTROLLER_ACTION_EDIT_PRODUCT) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->locator->getProduct();
            if ($product->getTypeId() == self::PRODUCT_TYPE) {
                $isRental = true;
            }
        } elseif ($actionName == self::CONTROLLER_ACTION_NEW_PRODUCT) {
            if (self::PRODUCT_TYPE == $this->request->getParam('type')) {
                $isRental = true;
            }
        }

        return $isRental;
    }

}
