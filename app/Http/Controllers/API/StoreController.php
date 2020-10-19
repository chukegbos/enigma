<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Store;
use App\StoreRequest;
use App\Sale;
use App\Debtor;
use App\User;
use App\Inventory;
use App\StoreUser;
use Illuminate\Support\Facades\Hash;
use DB;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('api');
    }

    public function index(Request $request)
    {
        $query = Store::where('deleted_at', NULL);

        if ($request->store_code) {
            $query->where('code', $request->store_code)->orWhere('name', $request->store_code);
        }      
        return $query->latest()->paginate(10);
    }

    public function getInventory(Request $request)
    {
        $query = DB::table('inventory_store')->where('inventory_store.deleted_at', NULL);

            if ($request->store_code) {
                $get_store = Store::where('deleted_at', NULL)->where('code', $request->store_code)->first();
                $query->where('inventory_store.store_id', $get_store->id);
            }
            else
            {
                $query->where('inventory_store.store_id', auth('api')->user()->store);
            }

            $query->join('inventories', 'inventory_store.inventory_id', '=', 'inventories.id')
            ->where('inventories.deleted_at', NULL);
            
        $inventory = $query->paginate(10);
        return response()->json($inventory);
    }


    public function discharge()
    {
        $inventory = StoreRequest::where('store_request.deleted_at', NULL)
            ->join('inventories', 'store_request.product_id', '=', 'inventories.id')
            ->where('inventories.deleted_at', NULL)
            ->join('stores', 'store_request.store_id', '=', 'stores.id')
            ->where('stores.deleted_at', NULL)
            ->orderBy('store_request.created_at', 'desc')
            ->select(
                'store_request.req_id as req_id',
                'stores.name as store_name',
                'store_request.id as id',
                'inventories.product_name as product_name',
                'inventories.quantity as quantity',
                'store_request.qty as qty',
                'store_request.sender_id as sender_id',
                'store_request.created_at as created_at',
                'store_request.approved_by as approved_by',
                'store_request.reciever_id as reciever_id',
                'store_request.status as status'
            )
        ->paginate(10);
        return response()->json($inventory);
    }  

    public function approve(Request $request, $id)
    {
        $store_request = StoreRequest::where('deleted_at')->find($id);
        $this->validate($request, [
            'qty' => 'required',
        ]);

        if ($request->quantity < $request->qty) {
            return ['error' => "You cannot disburse more that you dont have in your warehouse"];
        }
        $store_request->update([
            'qty' => $request->qty,
            'approved_by' => auth('api')->user()->id,
            'status' => 'approved',
        ]);
        return ['message' => "Success"];
    }

    public function decline($id)
    {
        $store_request = StoreRequest::where('deleted_at')->find($id);
    
        $store_request->update([
            'status' => 'declined',
        ]);
        return ['message' => "Success"];
    }

    public function accept($id)
    {
        $req = StoreRequest::where('deleted_at')->find($id);

        $inventory = Inventory::where('deleted_at', NULL)->find($req->product_id);
        if ($inventory) {
            $inventory->quantity = $inventory->quantity - $req->qty;
            $inventory->update();

            $store_inventory = DB::table('inventory_store')
                ->where('deleted_at', NULL)
                ->where('store_id', $req->store_id)
                ->where('inventory_id', $req->product_id)
                ->first();

            $store_inventory_update = DB::table('inventory_store')
                ->where('deleted_at', NULL)
                ->where('store_id', $req->store_id)
                ->where('inventory_id', $req->product_id)
                ->update(['number' => $store_inventory->number + $req->qty]);

            //Updating Status
            $req->reciever_id = auth('api')->user()->id;
            $req->status = 'concluded';
            $req->update();
            return $req;
        }
        else
        {
            return ['error' => "Product does not exist!!!"];
        }
        
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        return Store::create([
            'name' => ucwords($request->name),
            'address' => $request->address,
            'code' => rand(3,99888888),
        ]);
    }

    public function show($code)
    {
        $params = [];
        $params['store'] = Bar::where('deleted_at', NULL)->where('code', $code)->first();

        if ($params['store']) {
            return $params;
        }
        else
        {
            return response()->json(['error' => 'Store does not exist'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $bar = Store::where('deleted_at')->find($id);
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        $bar->update([
            'address' => $request->address,
            'name' => ucwords($request->name),
        ]);
        return ['message' => "Success"];
    }

    public function destroy($id)
    {
        Store::Destroy($id);
    }

    public function reports(Request $request)
    {
        $params = [];

        $query = Sale::where('sales.deleted_at', NULL);

        if ($request->store_code) {
            $store = Store::where('deleted_at', NULL)->where('code', $request->store_code)->first();
            if ($store) {
                $query->where('sales.store_id', $store->id);
            }
            else{
                return 'Store not found';
            }       
        }

        if ($request->store) {
            $query->where('sales.store_id', $request->store);      
        }
        
        if (auth('api')->user()->role != 3) {
            $query->where('sales.store_id', auth('api')->user()->store);
        }

        //Optional where
        if ($request->start_date) {
            $query->where('sales.main_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('sales.main_date', '<=', $request->end_date . ' 23:59');
        }

        if ($request->mode_of_payment) {
            $query->where('sales.mop', $request->mode_of_payment);
        }

        $report_data = $query->orderBy('sales.main_date', 'Desc')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->join('stores', 'sales.store_id', '=', 'stores.id')
            ->select(
                'stores.name as store_name',
                'sales.id as id',
                'sales.sale_id as sale_id',
                'users.name as user_name',
                'sales.mop as mop',
                'sales.cart as cart',
                'sales.initialPrice as initialPrice',
                'sales.totalPrice as totalPrice',
                'sales.discount as discount',
                'sales.percent_discount as percent_discount',
                'sales.amount_paid as amount_paid',
                'sales.main_date as created_at'  
            )->paginate(10);

        $report_data->transform(function($order, $key){
            $order->cart = unserialize($order->cart);
            return $order;
        });

        $params['report_data'] = $report_data;
        $params['stores'] = Store::where('deleted_at', NULL)->get();
        $query->select(DB::raw("Count(*) As total_orders, SUM(totalPrice) As total_amount"));
        $params['summary'] = $query->first();

        return $params;
    }

    public function debtors(Request $request)
    {
        $params = [];

        $query = Debtor::where('debtors.deleted_at', NULL)->where('debtors.status', 0);

        if (auth('api')->user()->role != 3) {
            $store = Store::where('deleted_at', NUll)->find(auth('api')->user()->store);
            $sale = Sale::where('deleted_at', NUll)->where('store_id', $store->id)->first();
                
            $query->where('debtors.trans_id', $sale->sale_id);
        }

        $report_data = $query->orderBy('debtors.created_at', 'Desc')
            ->join('sales', 'debtors.trans_id', '=', 'sales.sale_id')
            ->join('users', 'debtors.user_id', '=', 'users.id')
            ->select(
                'sales.sale_id as sale_id',
                'users.name as customer',
                'users.phone as phone',
                'users.address as address',
                'users.updated_at as updated_at',
                'debtors.amount as amount',
                'debtors.status as status', 
            )->paginate(10);

        $report_data->transform(function($order, $key){
            $order->cart = unserialize($order->cart);
            return $order;
        });

        $params['report_data'] = $report_data;

        return $params;
    }

    public function debtorview($sale_id)
    {
        $params = [];
        $query = Debtor::where('debtors.deleted_at', NULL)->where('debtors.trans_id', $sale_id);

        $report_data = $query->orderBy('debtors.created_at', 'Desc')
            ->join('sales', 'debtors.trans_id', '=', 'sales.sale_id')
            ->join('users', 'debtors.user_id', '=', 'users.id')
            ->select(
                'sales.sale_id as sale_id',
                'users.name as customer',
                'sales.cart as cart',
                'sales.totalPrice as totalPrice',
                'sales.amount_paid as amount_paid',
                'debtors.amount as amount',
                'debtors.amount_paid as amount_paid',
                'debtors.status as status', 
            )->paginate(10);

        $report_data->transform(function($order, $key){
            $order->cart = unserialize($order->cart);
            return $order;
        });

        $params['report_data'] = $report_data;
        return $params;
    }

    public function addDebt(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required',
        ]);

        $first_debt = Debtor::where('deleted_at', NULL)->where('trans_id', $request->sale_id)->where('status', 0)->first();
        $first_debt->amount_paid = $request->amount;
        $first_debt->update();


        $first_debt_id = $first_debt->id;
        $first_debt_amount = $first_debt->amount;

        $debts = Debtor::where('deleted_at', NULL)->where('trans_id', $request->sale_id)->get();
        foreach ($debts as $debt) {
            $debtor = Debtor::where('deleted_at', NULL)->find($debt->id);
            $debtor->status = 1;
            $debtor->update();
        }

        if ($first_debt_amount != $request->amount) {
            $set_debt = new Debtor();
            $set_debt->user_id = $first_debt->user_id;
            $set_debt->trans_id = $request->sale_id;
            $set_debt->amount = $first_debt_amount - $request->amount;
            $set_debt->status = 0;
            $set_debt->save();
        }
        return 'ok';
    }
}
