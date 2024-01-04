<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RentalSystem\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;
use Magenest\RentalSystem\Model\Status as SourceStatus;

class Status extends Column
{
    /**
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $status = $item['status'];

                if ($status == SourceStatus::UNPAID) {
                    $message = __('Unpaid');
                    $class   = 'grid-severity-critical';
                } else if ($status == SourceStatus::PENDING) {
                    $message = __('Pending');
                    $class   = 'grid-severity-minor';
                } else if ($status == SourceStatus::DELIVERING) {
                    $message = __('Delivering');
                    $class   = 'grid-severity-minor';
                } else if ($status == SourceStatus::DELIVERED) {
                    $message = __('Delivered');
                    $class   = 'grid-severity-minor';
                } else if ($status == SourceStatus::RETURNING) {
                    $message = __('Returning');
                    $class   = 'grid-severity-notice';
                } else if ($status == SourceStatus::COMPLETE) {
                    $message = __('Complete');
                    $class   = 'grid-severity-notice';
                } else {
                    $message = __('Canceled');
                    $class   = 'grid-severity-notice';
                }

                $item['status'] = '<span class="' . $class . '">' . $message . '</span>';
            }
        }

        return $dataSource;
    }
}
