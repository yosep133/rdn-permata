<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class NotifJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $permata ;
    /**
     * Create a new job instance.
     */
    public function __construct($permata )
    {
        $this->permata = $permata;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // get client no Carbon::now();
            
            $msgRqHdr = $this->permata['MsgRqHdr'];
            $transactionInfo = $this->permata['TransactionInfo'];
            $statements = $transactionInfo['Statements'];

            $account =DB::connection('sasdev')
                        ->table('subacc')
                        ->select('no_cust')
                        ->where('account_sub','=',$transactionInfo['AccountNumber'])
                        ->get();

            $date = Carbon::now();
            $amount ='' ;
            if ( $statements['DC'] == 'C') {
                $amount = '-'.$statements['CashValue'];
            } else {
                $amount = $statements['CashValue'];
            }
            //  sending to cash bo 
            echo 'queue 2 ';
            DB::connection('sasoldev')
            ->table('CashBo')
            ->insert([
                "DateBo" => Carbon::now(),
                "ClientNo" => $account[0]->no_cust,
                "Reference" => "Permata ".$date->format("Y/m/d H:m:s"),
                "Amount"=> $amount,
                "Type" => 'M',
                "Flag" => 0
            ]);
            // update permataAs is to hero 
            $resutl = DB::connection('sasoldev')
                ->table('permataAS')
                ->where('cust_ref_id','=',$msgRqHdr['CustRefID'])
                ->update(['is_to_hero' => '1']);

        } catch (\Throwable $th) {
            //throw $th;
            echo 'error '.$th;
        }
    }
}
