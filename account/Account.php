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
        $code = $currency->getCode();
        // сохраняем при необходимости в хранилище
        if (array_key_exists($code, $this->balance)) {
            $this->mainCurrency = $currency;
        } else {
            throw new \Exception("Currency $code not available for this account");
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
                throw new \Exception("No currency account available for " . $balanceCurrency->getCode());
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
            throw new \Exception("Currency $code not available for this account");
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
            throw new \Exception("Currency $code not available for this account");
        }
    }

    /**
     * @return array
     */
    public function getAvailableCurrencies(): array
    {
        return array_keys($this->balance);
    }

    /**
     * @param CurrencyInterface $from
     * @param CurrencyInterface $to
     * @return void
     * @throws \Exception
     */
    public function convert(CurrencyInterface $from, CurrencyInterface $to): void
    {
        switch (true) {
            case $this->balance[$from->getCode()] < $from->getCurrentValue():
                throw new \Exception("Not enough funds to convert");
            case !array_key_exists($from->getCode(), $this->balance):
                throw new \Exception($from->getCode() . " currency not available to convert from");
            case !array_key_exists($to->getCode(), $this->balance):
                throw new \Exception($to->getCode() . " currency not available to convert");
        }

        $this->balance[$from->getCode()] -= $from->getCurrentValue();
        $this->balance[$to->getCode()] += $from->getCurrentValue() * $from::getExchangeRate($to);
    }

    /**
     * @param CurrencyInterface $currency
     * @return void
     * @throws \Exception
     */
    public function disableCurrency(CurrencyInterface $currency): void
    {
        $code = $currency->getCode();
        $mainCurr = $this->mainCurrency;
        if ($code == $mainCurr->getCode()) {
            throw new \Exception("Cannot disable main currency");
        }

        $disabledCurrBalance = $this->balance[$code];

        $this->convert($currency->addCurrentValue($disabledCurrBalance), $mainCurr);
        unset($this->balance[$code]);
    }
}
