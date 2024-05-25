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
}
