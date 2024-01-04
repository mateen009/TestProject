<?php

namespace Amasty\Rma\Api;

/**
 * Interface CreateReturnProcessorInterface
 */
interface CreateReturnProcessorInterface
{
    /**
     * @param int $orderId
     * @param bool $isAdmin
     *
     * @return \Amasty\Rma\Api\Data\ReturnOrderInterface|bool
     */
    public function process($orderId, $isAdmin = false);
}
