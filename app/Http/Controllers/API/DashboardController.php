<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Purchase;
use App\Inventory;
use App\Category;
use DB;
use App\Store;
use App\Sale;
use App\Debtor;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{

    public function __construct()
    {
        $this->middleware('api');
    }

    public function totalSales()
    {
        $sales = Sale::where('deleted_at', NULL)->where('fridge_id', $this->myFridge()->id)->latest();
        return response()->json($sales);
    }

    public function stat(Request $request)
    {
        $params = [];

        $query_user = User::where('deleted_at', NULL)->where('role', '!=', 0);
        if (auth('api')->user()->role != 3) {
            $query_user->where('users.store', auth('api')->user()->store);
        }

        $params['users'] = $query_user->count();


        $params['customers'] = User::where('deleted_at', NULL)->where('role', 0)->count();
        $params['stores'] = Store::where('deleted_at', NULL)->count();
        
        $query_debtor = Debtor::where('deleted_at', NULL)->where('status', 0);
        if (auth('api')->user()->role != 3) {
            $store = Store::where('deleted_at', NUll)->find(auth('api')->user()->store);
            $sale = Sale::where('deleted_at', NUll)->where('store_id', $store->id)->first();
            $query_debtor->where('trans_id', $sale->sale_id);
        }
        $params['debtors'] = $query_debtor->count();

        $params['inventories'] = Inventory::where('deleted_at', NULL)->count();
        $params['categories'] = Category::where('deleted_at', NULL)->count();
        return $params;
    }
}
