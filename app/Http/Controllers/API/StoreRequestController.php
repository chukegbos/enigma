<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Store;
use App\StoreRequest;
use Illuminate\Support\Facades\Hash;
use DB;


class StoreRequestController extends Controller
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

    public function index()
    {
        $inventory = StoreRequest::where('store_request.deleted_at', NULL)
            ->join('inventories', 'store_request.product_id', '=', 'inventories.id')
            ->where('inventories.deleted_at', NULL)
            ->where('store_request.store_id', auth('api')->user()->store)
            
            ->orderBy('store_request.created_at', 'desc')
            ->select(
                'store_request.req_id as req_id',
                'store_request.id as id',
                'inventories.product_name as product_name',
                'store_request.qty as qty',
                'store_request.sender_id as sender_id',
                'store_request.created_at as created_at',
                'store_request.approved_by as approved_by',
                'store_request.reciever_id as reciever_id',
                'store_request.status as status',
            )
            ->paginate(10);
        return response()->json($inventory);
    }

    public function store(Request $request){
        $random_number = rand(2,988888888);

        $productRequest = new StoreRequest;
        $productRequest->req_id = $random_number;
        $productRequest->product_id = $request->product_id;
        $productRequest->store_id = auth('api')->user()->store;
        $productRequest->qty = $request->quantity;
        $productRequest->sender_id = auth('api')->user()->id;
        $productRequest->save();
        
        return $productRequest;
    }
}
