<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Account extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ref_id', 'store', 'user_id', 'type', 'category', 'purpose', 'amount', 'main_date'
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
