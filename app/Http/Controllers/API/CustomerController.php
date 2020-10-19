<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Setting;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
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
        $query = User::where('deleted_at', NULL)->where('users.role', 0);

        if ($request->name) {
            $query->where('users.name', 'like', '%' . $request->name . '%');
        }

        $query->select(
            'users.name as name',
            'users.email as email',
            'users.phone as phone',
            'users.address as address',
            'users.id as id',
        );

        $params['users'] =  $query->paginate(10);
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
            'address' => 'required|string|max:191',
        ]);

        $setting = Setting::findOrFail(1);

        return User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'address' => $request['address'],
            'role' => 0,
            'password' => Hash::make('Father@1989'),
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
        ]);

        $user->update([
            'name' => $request['name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'address' => $request['address'],
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
