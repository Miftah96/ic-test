<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public $table = 'products';
    protected $primaryKey = 'product_id';
    public $incrementing = false;
    protected $fillable = [
        'product_id',
        'product_name',
        'price',
    ];
}
