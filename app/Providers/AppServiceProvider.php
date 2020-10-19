<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\User;
use View;
use App\Providers;
use App\Setting;
use App\Debtor;
use App\Store;
use App\Sale;
use Auth;
use DB;


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
        Schema::defaultStringLength(191);
        View::composer('*', function($view)
        {
            $view->with('setting', Setting::find(1));

            $view->with('stores', Store::where('deleted_at', NULL)->get());
            $view->with('all_stores', Store::where('deleted_at', NULL)->count());

            

            if (Auth::guard('web')->check()) {
                if (Auth::user()->role != 3) {
                    $store = Store::where('deleted_at', NUll)->find(Auth::user()->store);
                    $sale = Sale::where('deleted_at', NUll)->where('store_id', $store->id)->first();
                    $view->with('all_depts', Debtor::where('deleted_at', NULL)->where('status', 0)->where('trans_id', $sale->sale_id)->count());
                }
                else{
                    $view->with('all_depts', Debtor::where('deleted_at', NULL)->where('status', 0)->count());
                }
            

                $role = Auth()->user()->role;
                $store_id = Auth()->user()->store;
                $view->with('my_store', Store::where('deleted_at', NULL)->find($store_id));
            }
        });
    }
}
