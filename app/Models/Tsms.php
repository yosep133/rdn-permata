<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tsms extends Model
{
    use HasFactory;
    protected $connection = 'sasoldev';
    public $timestamps = false;
    protected $table = 't_sms';
    protected $fillable = [
        'clientno','name','amount','phone','datercv','flag','accname'
        ,'account','bank','staclient','stainput'
    ];
}
