<?php

namespace currency;

class CurrenciesFactory
{
    /**
     * @var string
     */
    private string $currencyCode;

    /**
     * @param string $currencyCode
     */
    public function __construct(string $currencyCode)
    {
        $this->currencyCode = $currencyCode;
    }

    /**
     * @return CurrencyInterface
     * @throws \Exception
     */
    public function getCurrency(): CurrencyInterface
    {
        switch ($this->currencyCode) {
            case CurrencyInterface::AVAILABLE_CURRENCIES["USD"]:
                return new UsdCurrency();
            case CurrencyInterface::AVAILABLE_CURRENCIES["EUR"]:
                return new EurCurrency();
            case CurrencyInterface::AVAILABLE_CURRENCIES["RUB"]:
                return new RubCurrency();
            default:
                throw new \Exception("Currency not supported");
        }
    }
}
