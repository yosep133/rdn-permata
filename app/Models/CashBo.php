<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashBo extends Model
{
    use HasFactory;
    protected $connection = 'sasol';
    public $timestamps = false;
    protected $table = 'CashBo';
    protected $fillable = [
        'ClientNo','Amount','Reference','[Type]','Flag'
    ];
}
