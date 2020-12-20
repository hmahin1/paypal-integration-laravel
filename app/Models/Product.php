<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable=[
        'user_id', 'product_type', 'coins', 'price', 'status', 'per_coin_price'
       ];

}
