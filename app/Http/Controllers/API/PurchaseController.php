<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Bar;
use App\Purchase;
use App\Inventory;
use Illuminate\Support\Facades\Hash;
use DB;

class PurchaseController extends Controller
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
        $query = Purchase::where('purchases.deleted_at', NULL)
            ->where('inventories.deleted_at', NULL)
            ->join('inventories', 'purchases.product', '=', 'inventories.id');

            if ($request->start_date) {
                $query->where('purchases.purchase_date', '>=', $request->start_date);
            }
            if ($request->end_date) {
                $query->where('purchases.purchase_date', '<=', $request->end_date . ' 23:59');
            }

            if ($request->name) {
                 $query->where('purchases.purchase_id', 'like', '%' . $request->name . '%');
            }

            return $query->orderBy('purchases.created_at', 'Desc')
                ->select(
                    'purchases.id as id',
                    'purchases.purchase_id as purchase_id',
                    'purchases.quantity as quantity',
                    'inventories.product_name as product_name',
                    'purchases.total_price as total_price',
                    'purchases.amount_paid as amount_paid',
                    'purchases.purchase_date as purchase_date'
                )
                ->paginate(10);
    }

    public function store(Request $request)
    {
        $purchaseId = 'enip-'.rand(9,99999);
        $products = $request->purchaseDetails;
        
  
        $purchases = new Purchase ();
        $purchases->purchase_id = $purchaseId;
        $purchases->product = $request->product_id;
        $purchases->quantity = $request->quantity;
        $purchases->total_price = $request->cost_price;
        $purchases->cost_price_yuan = $request->cost_price_yuan;
        $purchases->purchase_date = $request->purchase_date;
        $purchases->save();

        $inventory = Inventory::find($request->product_id);
        $inventory->quantity = $inventory->quantity + $request->quantity;
        $inventory->cost_price = $request->unit_cost_price;
        $inventory->price = $request->unit_selling_price;
        $inventory->update();
        
        return $products;

    }

    public function show($purchaseId){
        $purchases =  Purchase::where('purchases.deleted_at', NULL)
            ->where('purchases.purchase_id', $purchaseId)
            ->where('inventories.deleted_at', NULL)
            ->join('inventories', 'purchases.product', '=', 'inventories.id')
            ->orderBy('purchases.created_at', 'Desc')
            ->select(
                'purchases.id as id',
                'purchases.purchase_id as purchase_id',
                'purchases.quantity as quantity',
                'inventories.product_name as product_name',
                'purchases.total_price as total_price',
                'purchases.amount_paid as amount_paid',
                'purchases.purchase_date as purchase_date'
            )
            ->get();

        return  response()->json($purchases);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'quantity' => 'required',
            'total_amount' => 'required',
            'total_pay' => 'required',
            'supplier' => 'required',
            'product' => 'required',
        ]);
        $purchase = Purchase::where('deleted_at')->find($id);
        $purchase_quantity = $purchase->quantity;
        $product = Product::findOrFail($request->product);
        $product->quantity = $product->quantity - $purchase_quantity;
        $product->quantity = $product->quantity + $request->quantity;
        $product->update();

        return $purchase->update($request->all());
        //return ['message' => "Success"];
    }

    public function destroy($id)
    {
        $purchase = Purchase::where('deleted_at', NULL)->find($id);
        $product = Inventory::where('deleted_at', NULL)->find($purchase->product);

        if ($product->quantity > $purchase->quantity) {
            $product->quantity = $product->quantity - $purchase->quantity;
            $product->update();
            Purchase::Destroy($id);
            return 'ok';
        }
        else
        {
            return 'You cannot delete this purchase detail because the product has decreased in the Inventory';
        }
        
    }
}
