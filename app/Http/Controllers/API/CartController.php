<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Store;
use App\InventoryStore;
use Illuminate\Support\Facades\Hash;
use DB;
use Illuminate\Support\Arr;
use Session;
use App\Cart;
use App\Debtor;
use App\Sale;
use App\User;
use App\Inventory;
use Carbon\Carbon;
use App\Account;

class CartController extends Controller
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

    public function addcart(Request $request, $id)
    {
        $product = Inventory::find($id); 
      
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->add($product, $product->id);
        $request->session()->put('cart', $cart);
        
        $request->session()->push('product_id', $id);

        Session::put('fridge_id', $request->fridge_id);
        return $product;
    }

    public function getCart()
    {
        $oldCart = Session::get('cart');
        $cart = new Cart($oldCart);

        $params = [];
        $params['products'] = $cart->items;
        $params['totalPrice'] = $cart->totalPrice;
        return $params;
    }

    public function addOne($id)
    {
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->addByOne($id);
      
        if (count($cart->items) > 0) {
            Session::put('cart', $cart);
        }
        else
        {
            Session::forget('cart');
        }

        return 'ok';
    }

    public function reduceOne($id)
    {
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->reduceByOne($id);
      
        if (count($cart->items) > 0) {
            Session::put('cart', $cart);
        }
        else
        {
            Session::forget('cart');
        }

        return 'ok';
    }

    public function reduceAll($id)
    {
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->removeItem($id);

        if (count($cart->items) > 0) {
            Session::put('cart', $cart);
        }
        else
        {
            Session::forget('cart');
        }

        return 'ok';
    }

    public function checkout(Request $request)
    {
        
        $oldCart = Session::get('cart');
        $cart = new Cart($oldCart);

        if ($request->name) {
            $new_user = new User();
            $new_user->name = $request->name;

            if ($request->phone) {
                $new_user->phone = $request->phone;
            }
            if ($request->email) {
                $new_user->email = $request->email;
            }

            if ($request->address) {
                $new_user->address = $request->address;
            }
            $new_user->role = 0;
            $new_user->save();

            $get_user = User::where('deleted_at', NULL)->latest()->first();

            $buyer = $get_user->id;
        }
        else
        {
            $buyer = $request->customer_id;
        }

        $sale = new Sale();
        $sale->cart = serialize($cart);  
      
        $user = auth('api')->user();
        
        //return $request;
        $all_product_id = session()->get('product_id');
        $arrlength = count($all_product_id);

        foreach ($all_product_id as $value) {
            $product = InventoryStore::where('deleted_at', NULL)
                ->where('store_id', $user->store)
                ->where('inventory_id', $value)
                ->first();

            $product->number = $product->number-1;
            $product->update();
        }
        $trans_id = rand(2,99887);
        $sale->initialPrice = $cart->totalPrice;
        $sale->discount = $request->discount;
        $sale->percent_discount = $request->percent_discount;
        $sale->totalPrice = $request->amount; 
        $sale->amount_paid = $request->paid; 
        $sale->user_id = $user->id;
        $sale->mop = $request->selected;
        $sale->sale_id = $trans_id;
        $sale->store_id = $user->store;
        $sale->main_date = Carbon::now()->addHour();

        $sale->buyer = $buyer;
        $sale->save();

        if ($request->paid != $request->amount) {
            $debtor = new Debtor();
            $debtor->user_id = $buyer;
            $debtor->trans_id = $trans_id;
            $debtor->amount = $request->amount - $request->paid;
            $debtor->status = 0;
            $debtor->save();
        }

        $ref_id = 'inc-'. rand(2,99999);

        $account = Account::where('category', 2)->where('type', 1)->where('deleted_at', NULL)->where('main_date', Carbon::today())->first();
        if ($account) {
            $account->amount = $account->amount + $request->paid;
            $account->update();
        }
        else
        {
            Account::create([
                'ref_id' => $ref_id,
                'category' => 2,
                'purpose' => 'Business Sales',
                'amount' => $request->paid,
                'main_date' => Carbon::today(),  
                'store' => auth('api')->user()->store,
                'user_id' => auth('api')->user()->store,
                'type' => 1,
            ]);
        }

        Session::forget('cart');
        session::forget('product_id');
     
        return 'ok';
    }
}
