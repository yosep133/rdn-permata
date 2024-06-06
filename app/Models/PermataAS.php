<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermataAS extends Model
{
    protected $connection = 'sqlsrv';
    public $timestamps = false;
    // public const UPDATED_AT = NULL;
    // public const CREATED_AT = NULL;
    protected $primaryKey = 'cust_ref_id';
    protected $table = 'PermataAs';
    protected $fillable = [
        'cust_ref_id','request_timestamp','group_id','seqnum','account_number','currency','value_date dat','opening_balance','extref','trx_type','flag','dc','cash_value','description','close_bal','notes','status'
    ];
    protected $casts = [ 
        'request_timestamp'=>'datetime',
        'opening_balance'=>'decimal:2',
        'close_bal'=>'decimal:2',
    ];


    
}
