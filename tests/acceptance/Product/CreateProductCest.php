<?php

namespace Tests\Acceptance\Product;

use AcceptanceTester;
use Faker\Factory;

class CreateProductCest
{
    private $faker;

    public function _before(AcceptanceTester $I)
    {
        $this->faker = Factory::create();
    }

    function testConfigureGuestCheckout(AcceptanceTester $I)
    {
        // TODO

        $I->amGoingTo('Create a Product');

        $sku = $this->faker->randomNumber(3);

        $nextInactiveAccordian = '//div[@class="accordian"]/div[@class="accordian-header"][1]';

        $I->loginAsAdmin();
        $I->amOnPage('/admin/catalog/products');
    }
}