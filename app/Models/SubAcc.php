<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubAcc extends Model
{
    use HasFactory;
    protected $connection = 'sasdev';
    public $timestamps = false;
    protected $table = 'SubAcc';
    protected $fillable = [
        'no_cust','account_sub','name','phone2','acc_namesub','lorf'
    ];
}
