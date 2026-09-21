<?php

namespace App\Providers;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }


    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
		if(config('app.env')==='production1')
		{
			URL::forceScheme('https');
		}        

		Blade::if('permission', function ($routeName) {
			return app(\App\Services\PermissionService::class)
				->hasPermission($routeName);
		});		
    }
}
