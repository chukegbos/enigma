<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Inventory;
use App\User;
use App\Sale;
use App\Store;
use DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $inventories = Inventory::where('deleted_at', NULL)->get();
        $stores = Store::where('deleted_at', NULL)->get();
        foreach ($inventories as $inventory) {
            foreach ($stores as $store) {
                $product = DB::table('inventory_store')
                    ->where('deleted_at', NULL)
                    ->where('store_id', $store->id)
                    ->where('inventory_id', $inventory->id)
                    ->first();
                if (!$product) {
                    $store->inventory()->attach($inventory->id);
                }
            }
        }
        return view('dashboard');
    }

    public function getUser()
    {
        return Auth::user();
    }

    public function sign_off_fridge()
    {
        $report = DB::table('fridge_user')
                ->where('deleted_at', NULL)
                ->where('time_in', '!=', NULL)
                ->where('time_out', NULL)
                ->where('user_id', Auth::user()->id)
                ->first();

        if (!$report) {
            return redirect()->route('index');
        }


        $fridge = Fridge::find($report->fridge_id);

        DB::table('fridge_user')
            ->where('deleted_at', NULL)
            ->where('time_in', '!=', NULL)
            ->where('time_out', NULL)
            ->where('user_id', Auth::user()->id)
            ->update(['time_out' => Carbon::now(), 'bar' => $fridge->bar_code]);

        $set_fridge =  DB::table('fridge_user')
            ->where('deleted_at', NULL)
            ->where('time_in', '!=', NULL)
            ->where('time_out', '!=', NULL)
            ->where('user_id', Auth::user()->id)
            ->where('bar', $fridge->bar_code)
            ->first();


        $totalPrice = Sale::where('deleted_at', NULL)
                ->where('fridge_user', $set_fridge->id)
                ->sum('totalPrice');

        DB::table('fridge_user')
            ->where('deleted_at', NULL)
            ->where('time_in', '!=', NULL)
            ->where('time_out', '!=', NULL)
            ->where('user_id', Auth::user()->id)
            ->where('bar', $fridge->bar_code)
            ->update(['totalPrice' => $totalPrice]);


        return redirect()->route('index');
    


    }

    public function sign_in_fridge()
    {
        DB::table('fridge_user')
            ->where('deleted_at', NULL)
            ->where('time_in', '!=', NULL)
            ->where('time_out', '!=', NULL)
            ->where('manager_out', NULL)
            ->where('manager_id', NULL)
            ->where('user_id', Auth::user()->id)
            ->update(['time_out' => NULL, 'totalPrice' => NULL, 'bar' => NULL]);
        return redirect()->route('index');
    }

    public function sign_off_bar()
    {
        $report = DB::table('bar_user')
                ->where('deleted_at', NULL)
                ->where('time_in', '!=', NULL)
                ->where('time_out', NULL)
                ->where('user_id', Auth::user()->id)
                ->first();
        if (!$report) {
            return redirect()->route('index');
        }
        $bar = Bar::find($report->bar_id);

        DB::table('bar_user')
            ->where('deleted_at', NULL)
            ->where('time_in', '!=', NULL)
            ->where('time_out', NULL)
            ->where('user_id', Auth::user()->id)
            ->update(['time_out' => Carbon::now()]);

        $set_bar =  DB::table('bar_user')
            ->where('deleted_at', NULL)
            ->where('time_in', '!=', NULL)
            ->where('time_out', '!=', NULL)
            ->where('user_id', Auth::user()->id)
            ->first();


        $totalPrice = Sale::where('deleted_at', NULL)
                ->where('bar_id', $bar->code)
                ->whereBetween(DB::raw('DATE(sales.main_date)'), array(Carbon::parse($set_bar->time_in)->toDateString(), Carbon::parse($set_bar->time_out)->toDateString()))
                ->where('user_id', Auth::user()->id)
                ->sum('totalPrice');

        DB::table('bar_user')
            ->where('deleted_at', NULL)
            ->where('time_in', '!=', NULL)
            ->where('time_out', '!=', NULL)
            ->where('user_id', Auth::user()->id)
            ->update(['totalPrice' => $totalPrice]);

        return redirect()->route('index');
    }

    public function sign_in_bar()
    {
        DB::table('bar_user')
            ->where('deleted_at', NULL)
            ->where('time_in', '!=', NULL)
            ->where('time_out', '!=', NULL)
            ->where('manager_out', NULL)
            ->where('manager_id', NULL)
            ->where('user_id', Auth::user()->id)
            ->update(['time_out' => NULL, 'totalPrice' => NULL]);
        return redirect()->route('index');
    }
}
