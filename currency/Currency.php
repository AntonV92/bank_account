<?php

namespace currency;

abstract class Currency implements CurrencyInterface
{
    /**
     * @var array
     */
    protected static array $exchangeRates = [];

    /**
     * @var float
     */
    protected float $currentValue = 0.0;

    /**
     * значение для конкретной операции - пополнение/снятие баланса
     * @param float $value
     * @return $this
     */
    public function addCurrentValue(float $value): static
    {
        $this->currentValue = $value;
        return $this;
    }

    /**
     * @return float
     */
    public function getCurrentValue(): float
    {
        return $this->currentValue;
    }

    /**
     * @param CurrencyInterface $currency
     * @param float $exchangeRate
     * @return void
     */
    public static function setExchangeRate(CurrencyInterface $currency, float $exchangeRate): void
    {
        static::$exchangeRates[$currency->getCode()] = $exchangeRate;
    }

    /**
     * @param CurrencyInterface $currency
     * @return float
     */
    public static function getExchangeRate(CurrencyInterface $currency): float
    {
        return static::$exchangeRates[$currency->getCode()] ?? 0.0;
    }
}
