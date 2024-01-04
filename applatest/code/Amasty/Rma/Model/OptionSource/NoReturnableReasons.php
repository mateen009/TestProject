<?php

namespace Amasty\Rma\Model\OptionSource;

class NoReturnableReasons
{
    public const ALREADY_RETURNED = 0;
    public const EXPIRED_PERIOD = 1;
    public const REFUNDED = 2;
    public const ITEM_WASNT_SHIPPED = 3;
    public const ITEM_WAS_ON_SALE = 4;
}
