<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    public $table = 'customers';
    protected $primaryKey = 'customer_id';
    protected $fillable = [
        'customer_id',
        'customer_name',
        'address',
    ];
}
