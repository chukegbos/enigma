<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Sale extends Model
{
    /*public function user(){
    	return $this->belongsTo('App\User');
    }
    */
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sale_id', 'cart', 'totalPrice', 'percent_discount', 'initialPrice', 'discount', 'amount_paid', 'user_id', 'mop', 'buyer', 'store_id', 'main_date'
    ];

    protected $dates = [
        'deleted_at', 'main_date',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
      'remember_token', 'deleted_at'
    ];

  }
