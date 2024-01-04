<?php

namespace Amasty\RmaAutomation\Api;

/**
 * Interface PerformActionInterface
 */
interface PerformActionInterface
{
    /**
     * Performs rule action
     *
     * @param \Amasty\Rma\Api\Data\RequestInterface $request
     */
    public function perform(\Amasty\Rma\Api\Data\RequestInterface $request);
}
