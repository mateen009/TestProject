<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 */

namespace Magenest\RentalSystem\Model;

class Status
{
    const UNPAID     = 0;
    const PENDING    = 1;
    const DELIVERING = 2;
    const DELIVERED  = 3;
    const RETURNING  = 4;
    const COMPLETE   = 5;
    const CANCELED   = 6;
}
