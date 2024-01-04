<?php
/**
 * Created by PhpStorm.
 * User: ducanh
 * Date: 30/01/2019
 * Time: 13:44
 */

namespace Magenest\RentalSystem\Block\Order\Item\Renderer;

use Magenest\RentalSystem\Helper\Rental;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\View\Element\Template\Context;

class DefaultRenderer extends \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer
{
    const XML_PATH_DATE_FORMAT = 'rental_system/locale/dateformat';
    const XML_PATH_TIME_FORMAT = 'rental_system/locale/timeformat';

    /** @var PriceHelper */
    protected $priceHelper;

    /** @var Rental */
    private $rentalHelper;

    /**
     * DefaultRenderer constructor.
     *
     * @param Context $context
     * @param StringUtils $string
     * @param OptionFactory $productOptionFactory
     * @param PriceHelper $priceHelper
     * @param Rental $rentalHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        StringUtils $string,
        OptionFactory $productOptionFactory,
        PriceHelper $priceHelper,
        Rental $rentalHelper,
        array $data = []
    ) {
        $this->priceHelper = $priceHelper;
        $this->rentalHelper = $rentalHelper;
        parent::__construct($context, $string, $productOptionFactory, $data);
    }

    /**
     * @param $option
     * @return array|string
     */
    public function getOptionTitleAndPrice($option)
    {
        if (!empty($option)) {
            $option = explode('_', $option);
            $price = $this->priceHelper->currency($option[0], true, false);

            return $this->rentalHelper->getOptionTitleAndPrice($option, $price);
        } else {
            return '';
        }
    }

    /**
     * @param $time
     * @return string
     */
    public function getDateString($time)
    {
        return $this->rentalHelper->getDateString($time, ' ');
    }
}
