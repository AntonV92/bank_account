<?php

namespace account;

use currency\CurrencyInterface;

final class Account
{
    /**
     * @var CurrencyInterface|null
     */
    private ?CurrencyInterface $mainCurrency = null;

    /**
     * @var array
     */
    private array $balance = [];
}
