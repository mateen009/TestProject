<?php
/**
 * Created by PhpStorm.
 * User: ducanh
 * Date: 14/02/2019
 * Time: 14:33
 */

namespace Magenest\RentalSystem\Ui\DataProvider\RentalOrder\Modifier;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Pricing\Helper\Data;

/**
 * Class RentalOrderPrice
 * @package Magenest\RentalSystem\Ui\DataProvider\RentalOrder\Modifier
 */
class RentalOrderPrice extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var Data
     */
    protected $dataHelper;

    const NAME = 'column.price';

    /**
     * RentalOrderPrice constructor.
     *
     * @param Data $dataHelper
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        Data $dataHelper,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = [])
    {
        $this->dataHelper = $dataHelper;
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
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$fieldName])) {
                    $item[$fieldName] = $this->dataHelper->currency($item[$fieldName], true, false);
                }
            }
        }

        return $dataSource;
    }

}
