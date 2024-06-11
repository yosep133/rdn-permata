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
            $account =DB::connection('sas')
                        ->table('subacc')
                        ->select('no_cust')
                        ->where('account_sub','=',$this->permata->account_number);

            $date = Carbon::now();
            //  sending to cash bo 
            DB::connection('sasol')
            ->table('CashBo')
            ->insert([
                "DateBo" => Carbon::now(),
                "ClientNo" => $account,
                "Reference" => "Permata ".$date->format("Y/M/D H:m:s"),
                "Type" => 'M',
                "Flag" => 0
            ]);
            
            // update permataAs is to hero 
            $resutl = DB::connection('sasol')
                ->table('permataAs')
                ->where('cust_ref_id','=',$this->permata->cust_ref_id)
                ->update(['istohero' => '1']);

        } catch (\Throwable $th) {
            //throw $th;
            echo 'error '.$th;
        }
    }
}
