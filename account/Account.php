<?php

namespace account;

use currency\CurrenciesFactory;
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


    /**
     * @param CurrencyInterface $currency
     * @return void
     */
    public function addCurrency(CurrencyInterface $currency): void
    {
        $code = $currency->getCode();
        if (!array_key_exists($code, $this->balance)) {
            $this->balance[$code] = 0.0;
            if (is_null($this->mainCurrency)) {
                $this->mainCurrency = $currency;
            }
        }
    }

    /**
     * @param CurrencyInterface $currency
     * @return void
     * @throws \Exception
     */
    public function setMainCurrency(CurrencyInterface $currency): void
    {
        // сохраняем при необходимости в хранилище
        if (array_key_exists($currency->getCode(), $this->balance)) {
            $this->mainCurrency = $currency;
        } else {
            throw new \Exception("No currency available for this account");
        }
    }

    /**
     * @return CurrencyInterface|null
     */
    public function getMainCurrency(): ?CurrencyInterface
    {
        return $this->mainCurrency;
    }

    /**
     * @param CurrencyInterface|null $balanceCurrency
     * @return float
     * @throws \Exception
     */
    public function getBalance(?CurrencyInterface $balanceCurrency = null): float
    {
        if (empty($balanceCurrency)) {
            $balanceCurrency = $this->getMainCurrency();
            if (is_null($balanceCurrency)) {
                throw new \Exception("No currency added");
            }
        }

        $result = 0.0;

        foreach ($this->balance as $code => $amount) {
            if ($balanceCurrency->getCode() == $code) {
                $result += $amount;
                continue;
            } elseif (!array_key_exists($balanceCurrency->getCode(), $this->balance)) {
                // если идет запрос баланса для несуществующей валюты
                throw new \Exception("No currency account available for " . $balanceCurrency::class);
            }
            $factory = new CurrenciesFactory($code);
            $result += $factory->getCurrency()::getExchangeRate($balanceCurrency) * $amount;
        }

        return round($result, 4);
    }

    /**
     * @param CurrencyInterface $currency
     * @return void
     * @throws \Exception
     */
    public function addFunds(CurrencyInterface $currency): void
    {
        $code = $currency->getCode();
        if (array_key_exists($code, $this->balance)) {
            $this->balance[$code] += $currency->getCurrentValue();
        } else {
            throw new \Exception("No currency available");
        }
    }

    /**
     * @param CurrencyInterface $currency
     * @return void
     * @throws \Exception
     */
    public function removeFunds(CurrencyInterface $currency): void
    {
        $code = $currency->getCode();
        if (array_key_exists($code, $this->balance)) {

            if ($this->balance[$code] < $currency->getCurrentValue()) {
                throw new \Exception("Not enough funds");
            }
            $this->balance[$code] -= $currency->getCurrentValue();
        } else {
            throw new \Exception("No currency available");
        }
    }
}
