<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Store;
use App\Setting;
use Illuminate\Support\Facades\Hash;
use Auth;
use DB;
use App\Bank;
use Carbon\Carbon;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('api');
    }

    public function index(Request $request)
    {
        $params = [];
        $params['stores'] = Store::where('deleted_at', NULL)->get();
        $query = User::where('stores.deleted_at', NULL)
            ->where('users.role', 3)
            ->join('stores', 'users.store', '=', 'stores.id');

        if ($request->name) {
            $query->where('users.name', 'like', '%' . $request->name . '%');
        }

        if($request->store)
        {
            $query->where('users.store', $request->store);
        }

        $query->select(
            'users.name as name',
            'users.email as email',
            'users.phone as phone',
            'users.address as address',
            'users.image as image',
            'users.next_of_kin as next_of_kin',
            'users.next_of_kin_phone as next_of_kin_phone',
            'users.next_of_kin_address as next_of_kin_address',
            'users.id as id',
            'users.store as store',
            'stores.name as store_name'
        );

        $params['users'] =  $query->paginate(6);
        return $params;
    }

    public function allusers()
    {
        $users = User::where('deleted_at', NULL)->latest()->get();
        return response()->json($users);
    }

    public function setting()
    {
        return Setting::find(1); 
    }

    public function updateSetting(Request $request, $id)
    {
        $setting = Setting::findOrFail($id);
        $this->validate($request, [
            'sitename' => 'required|string|max:191',
            'email' => 'required|string',
            'phone' => 'required|string|max:19',
            'admin_percent' => 'required|string|max:2',
            'manager_percent' => 'required|string|max:2',
            'cashier_percent' => 'required|string|max:3',
            'naira_value' => 'required|string|max:255',
        ]);

        $setting->update([
            'sitename' => $request['sitename'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'address' => $request['address'],
            'admin_percent' => $request['admin_percent'],
            'cashier_percent' => $request['cashier_percent'],
            'manager_percent' => $request['manager_percent'],
            'naira_value' => $request['naira_value'],
        ]);

        $users = User::where('deleted_at', NULL)->get();
        foreach ($users as $user) {
            $get_user = User::find($user->id);
            if ($get_user->role == 1) {
                $get_user->sale_percent = $request['cashier_percent'];
            }
            elseif ($get_user->role == 2) {
                $get_user->sale_percent = $request['manager_percent'];
            }
            else{
                $get_user->sale_percent = $request['admin_percent'];
            }
            $get_user->update();
            
        }

        return ['Message' => 'Updated'];
    }

    public function bank()
    {
        return Bank::where('deleted_at', NULL)->get(); 
    }

    public function fetchbank(Request $request)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.paystack.co/bank/resolve?account_number='.$request->bank_account.'&bank_code='.$request->bank_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
              "Authorization: Bearer sk_test_2b3f7792faa550ac09dd009b1788b89f3286c3a4",
              "Cache-Control: no-cache",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } 
        else {
            return $response;
        }
    }

    public function getUser(Request $request)
    {
        $params = [];
        $store_id = auth('api')->user()->store; 

        $params['store'] = Store::where('deleted_at', NULL)->find($store_id);
        $params['customers'] = User::where('deleted_at', NULL)->where('role', 0)->get();
        $params['user'] = auth('api')->user();

        return $params;
    }

    public function viewUser($id)
    {
        $user = User::where('users.deleted_at', NULL)
            ->join('stores', 'users.store', '=', 'stores.id')
            ->select(
                'users.name as name',
                'users.email as email',
                'users.phone as phone',
                'users.role as role',
                'users.address as address',
                'users.image as image',
                'users.next_of_kin as next_of_kin',
                'users.next_of_kin_phone as next_of_kin_phone',
                'users.next_of_kin_address as next_of_kin_address',
                'users.id as id',
                'users.store as store',
                'stores.name as store_name'
            )
            ->find($id);
        return  $user;
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:191',
            'email' => 'required|string|max:191|email|unique:users',
            'phone' => 'required|string|max:19',
            'store' => 'required',
            'address' => 'required|string|max:191',
            'password' => 'required|string|min:8',
            'next_of_kin' => 'required|string|max:191',
            'next_of_kin_address' => 'required|string|max:191',
            'next_of_kin_phone' => 'required|string|max:19'
        ]);

        $setting = Setting::findOrFail(1);

        $name = "";
        if ($request->image) {
            $name = time().'.' . explode('/', explode(':', substr($request->image, 0, strpos($request->image, ';')))[1])[1];

            \Image::make($request->image)->save(public_path('img/photos/').$name);
        }

        return User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'address' => $request['address'],
            'store' => $request['store'],
            'sale_percent' => $setting->admin_percent,
            'role' => 3,
            'password' => Hash::make($request['password']),
            'next_of_kin' => $request['next_of_kin'],
            'next_of_kin_address' => $request['next_of_kin_address'],
            'next_of_kin_phone' => $request['next_of_kin_phone'],
            'image' => $name,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $this->validate($request, [
            'name' => 'required|string|max:191',
            'email' => 'required|string|max:191|unique:users,email,'.$user->id,
            'phone' => 'required|string|max:19',
            'address' => 'required|string|max:191',
            'store' => 'required',
            'next_of_kin' => 'required|string|max:191',
            'next_of_kin_address' => 'required|string|max:191',
            'next_of_kin_phone' => 'required|string|max:19'
        ]);

        if ($request->password) {
            $password = Hash::make($request['password']);
        }
        else
        {
            $password = $user->password;
        }

        if ($request->image) {
            $name = time().'.' . explode('/', explode(':', substr($request->image, 0, strpos($request->image, ';')))[1])[1];

            \Image::make($request->image)->save(public_path('img/photos/').$name);
        }
        else
        {
            $name = $user->image;
        }

        $user->update([
            'name' => $request['name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'address' => $request['address'],
            'store' => $request['store'],
            'password' => $password,
            'next_of_kin' => $request['next_of_kin'],
            'next_of_kin_address' => $request['next_of_kin_address'],
            'next_of_kin_phone' => $request['next_of_kin_phone'],
            'image' => $name,
        ]);
        return ['Message' => 'Updated'];
    }

    public function destroy($id)
    {
        User::destroy($id);
    }
}
