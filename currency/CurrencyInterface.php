<?php

namespace currency;

interface CurrencyInterface
{
    public const AVAILABLE_CURRENCIES = [
        'USD' => 'USD',
        'EUR' => 'EUR',
        'RUB' => 'RUB'
    ];

    /**
     * @param float $value
     * @return $this
     */
    public function addCurrentValue(float $value): static;

    /**
     * @return float
     */
    public function getCurrentValue(): float;

    /**
     * @param CurrencyInterface $currency
     * @param float $exchangeRate
     * @return mixed
     */
    public static function setExchangeRate(CurrencyInterface $currency, float $exchangeRate): void;

    /**
     * @param CurrencyInterface $currency
     * @return float
     */
    public static function getExchangeRate(CurrencyInterface $currency): float;

    /**
     * @return string
     */
    public static function getCode(): string;
}
