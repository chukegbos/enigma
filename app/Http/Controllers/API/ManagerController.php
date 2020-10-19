<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Store;
use App\Setting;
use Illuminate\Support\Facades\Hash;

class ManagerController extends Controller
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
        $params = [];
        $params['stores'] = Store::where('deleted_at', NULL)->get();
        $query = User::where('stores.deleted_at', NULL)
            ->where('users.role', 2)
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
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
            'sale_percent' => $setting->manager_percent,
            'role' => 2,
            'password' => Hash::make($request['password']),
            'next_of_kin' => $request['next_of_kin'],
            'next_of_kin_address' => $request['next_of_kin_address'],
            'next_of_kin_phone' => $request['next_of_kin_phone'],
            'image' => $name,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        User::destroy($id);
    }
}
