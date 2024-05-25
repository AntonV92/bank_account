<?php

namespace currency;

class UsdCurrency extends Currency
{
    protected static array $exchangeRates = [
        self::AVAILABLE_CURRENCIES["RUB"] => 70,
        self::AVAILABLE_CURRENCIES["EUR"] => 1,
    ];

    /**
     * @return string
     */
    public static function getCode(): string
    {
        return static::AVAILABLE_CURRENCIES['USD'];
    }
}
