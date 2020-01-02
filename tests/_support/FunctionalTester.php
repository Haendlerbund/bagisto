<?php

use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Webkul\User\Models\Admin;


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
 */
class FunctionalTester extends \Codeception\Actor
{
    use _generated\FunctionalTesterActions;

    /**
     * Define custom actions here
     */

    public function loginAsAdmin()
    {
        $I = $this;

        Auth::guard('admin')->login($I->grabRecord(Admin::class, ['email' => 'admin@example.com']));
        $I->seeAuthentication('admin');
    }

    public function amOnAdminRoute(string $name)
    {
        $I = $this;
        $I->amOnRoute($name);
        $I->seeCurrentRouteIs($name);

        /** @var RouteCollection $routes */
        $routes = Route::getRoutes();
        $middlewares = $routes->getByName($name)->middleware();
        $I->assertContains('admin', $middlewares, 'check that admin middleware is applied');
    }

    public function setConfigData($data) {
        // TODO: change method as soon as there is a method to set core config data

        foreach ($data as $key => $value) {
            DB::table('core_config')->where('code', '=', $key)->update(['value' => $value]);
        }
    }
}
