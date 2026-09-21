<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Services\MenuService;
use App\Services\AdminMenuService;

class ViewComposerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(MenuService $menuService,AdminMenuService $adminMenuService)
    {
		View::composer(['admin.body.adminmenu','admin.admin_master'], function ($view) use ($menuService,$adminMenuService) {
			if(session()->has('userId'))
			{
				if(session('userType')=='ADMIN' && session('isMaster')==0)
				{
					$view->with('menuItems', $adminMenuService->getMenuItems());
				}
				else
				{
					$view->with('menuItems', $menuService->getMenuItems());
				}				
				//$view->with('menuItems', $menuService->getMenuItems());
			}
			else
			{
				//$view->with('ordItems', []);
			}
		});
    }
}
