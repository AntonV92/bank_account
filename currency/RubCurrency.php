<?php

namespace currency;

class RubCurrency extends Currency
{
    protected static array $exchangeRates = [
        self::AVAILABLE_CURRENCIES["USD"] => 0.0142,
        self::AVAILABLE_CURRENCIES["EUR"] => 0.0125,
    ];

    /**
     * @return string
     */
    public static function getCode(): string
    {
        return static::AVAILABLE_CURRENCIES["RUB"];
    }
}
