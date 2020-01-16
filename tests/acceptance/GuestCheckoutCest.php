<?php

namespace Tests\Acceptance;

use AcceptanceTester;
use Faker\Factory;

class GuestCheckoutCest
{
    private $faker;

    public function _before(AcceptanceTester $I)
    {
        $this->faker = Factory::create();
    }

    function testToConfigureGlobalGuestCheckout(AcceptanceTester $I)
    {
        $I->loginAsAdmin();

        $I->amGoingTo('turn ON the global guest checkout configuration');
        $I->amOnPage('/admin/configuration/catalog/products');
        $I->see(__('admin::app.admin.system.allow-guest-checkout'));
        $I->selectOption('catalog[products][guest-checkout][allow-guest-checkout]', 1);
        $I->click('Save');
        $I->seeRecord('core_config', ['code' => 'catalog.products.guest-checkout.allow-guest-checkout', 'value' => 1]);

        $I->amGoingTo('assert that the product guest checkout configuration is shown');
        $I->amOnPage('admin/catalog/products');
        $I->click('Add Product');
        $I->selectOption('attribute_family_id', 1);
        $I->fillField('sku', $this->faker->randomNumber(3));
        $I->click('Save Product');
        $I->seeInCurrentUrl('admin/catalog/products/edit');
        $I->scrollTo('#new');
        $I->see('Guest Checkout');
        $I->seeInSource('<input type="checkbox" id="guest_checkout" name="guest_checkout"');

        $I->amGoingTo('turn OFF the global guest checkout configuration');
        $I->amOnPage('/admin/configuration/catalog/products');
        $I->see(__('admin::app.admin.system.allow-guest-checkout'));
        $I->selectOption('catalog[products][guest-checkout][allow-guest-checkout]', 0);
        $I->click('Save');
        $I->seeRecord('core_config', ['code' => 'catalog.products.guest-checkout.allow-guest-checkout', 'value' => 0]);

        $I->amGoingTo('assert that the product guest checkout configuration is not shown');
        $I->amOnPage('admin/catalog/products');
        $I->click('Add Product');
        $I->selectOption('attribute_family_id', 1);
        $I->fillField('sku', $this->faker->randomNumber(3));
        $I->click('Save Product');
        $I->seeInCurrentUrl('admin/catalog/products/edit');
        $I->scrollTo('#new');
        $I->dontSee('Guest Checkout');
        $I->dontSeeInSource('<input type="checkbox" id="guest_checkout" name="guest_checkout"');
    }
}