<?php
/**
 * Created by PhpStorm.
 * User: ducanh
 * Date: 01/02/2019
 * Time: 08:35
 */

namespace Magenest\RentalSystem\Block\Adminhtml\Sales\Items\Column\Rental;

use Magenest\RentalSystem\Helper\Rental;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Registry;

class Name extends \Magento\Sales\Block\Adminhtml\Items\Column\Name
{
    const XML_PATH_DATE_FORMAT = 'rental_system/locale/dateformat';
    const XML_PATH_TIME_FORMAT = 'rental_system/locale/timeformat';

    /** @var Rental */
    private $rentalHelper;

    /**
     * Name constructor.
     *
     * @param Context $context
     * @param StockRegistryInterface $stockRegistry
     * @param StockConfigurationInterface $stockConfiguration
     * @param Registry $registry
     * @param OptionFactory $optionFactory
     * @param Rental $rentalHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        StockRegistryInterface $stockRegistry,
        StockConfigurationInterface $stockConfiguration,
        Registry $registry,
        OptionFactory $optionFactory,
        Rental $rentalHelper,
        array $data = []
    ) {
        $this->rentalHelper = $rentalHelper;
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $optionFactory, $data);
    }

    /**
     * @param $option
     * @return array
     */
    public function getOptionTitleAndPrice($option)
    {
        $option = explode('_', $option);
        $price = $this->formatPrice($option[0]);

        return $this->rentalHelper->getOptionTitleAndPrice($option, $price);
    }

    /**
     * @param $time
     * @return string
     */
    public function getLocateTime($time)
    {
        return $this->rentalHelper->getDateString($time, '');
    }
}
