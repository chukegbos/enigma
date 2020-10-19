<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use App\User;

class InventoryStore extends Model
{
    use SoftDeletes;
    protected $table = 'inventory_store';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'store_id', 'inventory_id', 'number'
    ];


    protected $dates = [
        'deleted_at', 
    ];
}
