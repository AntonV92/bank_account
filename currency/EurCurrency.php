<?php

namespace currency;

class EurCurrency extends Currency
{
    protected static array $exchangeRates = [
        self::AVAILABLE_CURRENCIES["RUB"] => 80,
        self::AVAILABLE_CURRENCIES["USD"] => 1,
    ];

    /**
     * @return string
     */
    public static function getCode(): string
    {
        return "EUR";
    }
}
