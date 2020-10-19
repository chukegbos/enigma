<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ExpenditureCategory;
use App\Account;
use App\Store;
use Illuminate\Support\Str;

class ExpenditureController extends Controller
{
    public function __construct()
    {
        $this->middleware('api');
    }

    public function index(Request $request)
    {
        $params = [];
        $params['stores'] = Store::where('deleted_at', NULL)->get();
        $params['categories'] = ExpenditureCategory::where('deleted_at', NULL)->get();
        $query =  Account::where('accounts.deleted_at', NULL)
            ->where('accounts.type', 2)
            ->join('expenditure_categories', 'accounts.category', '=', 'expenditure_categories.id')
            ->join('stores', 'accounts.store', '=', 'stores.id')
            ->join('users', 'accounts.user_id', '=', 'users.id');
            if(auth('api')->user()->role != 3)
            {
                $query->where('accounts.store', auth('api')->user()->store);
            }

            if($request->due_date)
            {
                $query->where('accounts.main_date', $request->due_date);
            }

            if($request->category)
            {
                $query->where('accounts.category', $request->category);
            }

            if($request->store)
            {
                $query->where('accounts.store', $request->store);
            }

            $params['accounts'] = $query->select(
                'accounts.ref_id as ref_id',
                'accounts.id as id',
                'users.name as user_id',
                'expenditure_categories.name as category',
                'stores.name as store',
                'accounts.amount as amount',
                'accounts.purpose as purpose',
                'accounts.main_date as main_date'
            )
            ->paginate(10);
        return $params;
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required|integer',
            'main_date' => 'required',
        ]);

        $ref_id = rand(2,999999);

        return Account::create([
            'ref_id' => $ref_id,
            'category' => $request->category,
            'purpose' => $request->purpose,
            'amount' => $request->amount,
            'main_date' => $request->main_date,  
            'store' => auth('api')->user()->store,
            'user_id' => auth('api')->user()->id,
            'type' => 2,
        ]);
    }

    public function destroy($id)
    {
        $account = Account::where('deleted_at', NULL)->find($id);
        $account->delete();
    }
}
