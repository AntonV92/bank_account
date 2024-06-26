<?php

namespace tests;

use account\Account;
use currency\EurCurrency;
use currency\RubCurrency;
use currency\UsdCurrency;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\TestCase;

class AccountTest extends TestCase
{
    #[Before]
    public function setDefaultRates()
    {
        UsdCurrency::setExchangeRate(new RubCurrency(), 70);
        UsdCurrency::setExchangeRate(new EurCurrency(), 1);

        EurCurrency::setExchangeRate(new RubCurrency(), 80);
        EurCurrency::setExchangeRate(new UsdCurrency(), 1);

        RubCurrency::setExchangeRate(new UsdCurrency(), 0.0142);
        RubCurrency::setExchangeRate(new EurCurrency(), 0.0125);
    }

    public function testAccount()
    {
        $account = new Account();
        $account->addCurrency(new UsdCurrency());

        $this->assertEquals(0.0, $account->getBalance());
    }

    public function testBalances()
    {
        $account = new Account();
        $account->addCurrency(new UsdCurrency());
        $account->addCurrency(new RubCurrency());
        $account->addCurrency(new EurCurrency());
        $account->setMainCurrency(new RubCurrency());

        $this->assertEquals(["USD", "RUB", "EUR"], $account->getAvailableCurrencies());
        $this->assertEquals("RUB", $account->getMainCurrency()->getCode());

        $usd = new UsdCurrency();
        $usd->addCurrentValue(50);

        $eur = new EurCurrency();
        $eur->addCurrentValue(50);

        $rub = new RubCurrency();
        $rub->addCurrentValue(1000);

        $account->addFunds($usd);
        $account->addFunds($eur);
        $account->addFunds($rub);

        $this->assertEquals(8500, $account->getBalance());
        $this->assertEquals(114.2, $account->getBalance(new UsdCurrency()));
        $this->assertEquals(112.5, $account->getBalance(new EurCurrency()));

        UsdCurrency::setExchangeRate(new RubCurrency(), 100);
        EurCurrency::setExchangeRate(new RubCurrency(), 150);

        $this->assertEquals(13500.0, $account->getBalance());

        $account->setMainCurrency(new EurCurrency());
        $this->assertEquals(112.5, $account->getBalance());

        EurCurrency::setExchangeRate(new RubCurrency(), 120);
        $this->assertEquals(112.5, $account->getBalance());
    }

    public function testConvertRubToUsd()
    {
        $account = new Account();
        $account->addCurrency(new UsdCurrency());
        $account->addCurrency(new RubCurrency());

        $rub = new RubCurrency();
        $rub->addCurrentValue(100);

        $account->addFunds($rub);

        $account->convert($rub->addCurrentValue(70), new UsdCurrency());

        $rub = new RubCurrency();
        $rub->addCurrentValue(30);

        $account->removeFunds($rub);

        $this->assertEquals(0.994, $account->getBalance());
    }

    public function testDisableMainCurrency()
    {
        $account = new Account();
        $account->addCurrency(new UsdCurrency());
        $account->addCurrency(new RubCurrency());

        $this->expectExceptionMessage("Cannot disable main currency");
        $account->disableCurrency(new UsdCurrency());
    }

    public function testDisableCurrency()
    {
        $account = new Account();
        $account->addCurrency(new UsdCurrency());
        $account->addCurrency(new RubCurrency());
        $account->addCurrency(new EurCurrency());

        $usd = new UsdCurrency();
        $usd->addCurrentValue(50);

        $eur = new EurCurrency();
        $eur->addCurrentValue(50);

        $rub = new RubCurrency();
        $rub->addCurrentValue(700);

        $account->addFunds($usd);
        $account->addFunds($eur);
        $account->addFunds($rub);

        $account->setMainCurrency(new RubCurrency());

        $this->assertEquals(8200, $account->getBalance());

        $account->disableCurrency(new EurCurrency());
        $account->disableCurrency(new UsdCurrency());

        $this->assertEquals(8200, $account->getBalance());
    }

    public function testNotEnoughFunds()
    {
        $account = new Account();
        $account->addCurrency(new UsdCurrency());

        $usd = new UsdCurrency();
        $usd->addCurrentValue(10);

        $account->addFunds($usd);

        $this->expectExceptionMessage("Not enough funds");

        $usd->addCurrentValue(11);
        $account->removeFunds($usd);
    }

    public function testSetUnavailableCurrency()
    {
        $account = new Account();
        $account->addCurrency(new UsdCurrency());

        $eur = new EurCurrency();
        $code = $eur->getCode();

        $this->expectExceptionMessage("Currency $code not available for this account");
        $account->setMainCurrency($eur);
    }

    public function testBalanceEmptyCurrency()
    {
        $account = new Account();
        $account->addCurrency(new UsdCurrency());

        $usd = new UsdCurrency();
        $usd->addCurrentValue(50);

        $eur = new EurCurrency();

        $this->expectExceptionMessage("No currency account available for " . $eur->getCode());
        $this->assertEquals(50, $account->getBalance($eur));
    }

    public function testAddFundsUnavailableCurrency()
    {
        $account = new Account();
        $account->addCurrency(new UsdCurrency());

        $eur = new EurCurrency();
        $eur->addCurrentValue(50);
        $code = $eur->getCode();

        $this->expectExceptionMessage("Currency $code not available for this account");
        $account->addFunds($eur);
    }

    public function testConvertIncorrect()
    {
        $account = new Account();
        $usd = new UsdCurrency();

        $account->addCurrency($usd);

        $usd->addCurrentValue(10);
        $account->addFunds($usd);

        $this->expectExceptionMessage("Not enough funds to convert");
        $account->convert($usd->addCurrentValue(15), new RubCurrency());

        $rub = new RubCurrency();

        $this->expectExceptionMessage($rub->getCode() . " currency not available to convert from");
        $account->convert($rub, $usd);

        $this->expectExceptionMessage($rub->getCode() . " currency not available to convert");
        $account->convert($usd, $rub);
    }
}
