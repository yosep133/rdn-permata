<?php

namespace App\Jobs;

use App\Models\PermataAS;
use App\Models\Tsms;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                        ->select('*')
                        ->where('account_sub','=',$transactionInfo['AccountNumber'])
                        ->get();

            $permata = PermataAS::where('cust_ref_id',$msgRqHdr['CustRefID'])
                    ->first();                        

            $sFlag = $this->setFlag($account,$permata);
            //
            if (!is_null($account)) {
                // send to cash bo 
                if($this->insertCashBo($account,$permata)){
                    // insert to tsms
                    if ($sFlag == '0' && $account->phone2 != '') {
                        $this->insertTSms($account[0],$permata);
                    }
                    if ($sFlag == '1' && $permata->dc == 'C'
                        && $permata->trx_type > 'NTRF' && $account->phone2 != '') {
                        $this->insertTSms($account,$this->permata);
                    //     # code...
                    }
                }
            }

        } catch (\Throwable $th) {
            //throw $th;
            Log::error($th);
        }
    }
    
    public function setFlag($account,$permata){
        $sFlag = '1';
        Log::info('setsFlag '. $sFlag.' - last balance '.$this->getLastCashBalance().' - recieve '. $permata->recv_time." tolower ".str_contains(strtolower($permata->trx_desc),'ipo'));
        $getLastBalance = Carbon::createFromFormat('Y-m-d H:i:s.u',$this->getLastCashBalance());
        $recvTime = Carbon::createFromFormat('Y-m-d H:i:s.u',$permata->recv_time);
         
        if ($permata->cash_value > 0 && (
            $permata->trx_type == 'NTRF' || $permata->trx_type == 'NINT' || $permata->trx_type == 'NREV' || $permata->trx_type == 'NTAX'
        ) ) {
            $sFlag = '0';
        }
        if ($recvTime->gt($getLastBalance)) {
            $sFlag = '0';    
        } else if (str_contains(strtolower($permata->trx_desc),'ipo')){
            if ($recvTime->gt($getLastBalance)) {
                $sFlag = '0';    
            }
        } /** */
        Log::info("setFlag ". $sFlag. " recv.get(last) ".$recvTime->gt($getLastBalance)." tolower ".str_contains(strtolower($permata->trx_desc),'ipo')." amount ". $permata->cash_value);
        return $sFlag;
    }

    public function getLastCashBalance(){
        $datebo = DB::connection('sasoldev')
                ->table('CashBO')
                ->select('datebo')
                ->where('datebo','<',Carbon::now())
                ->where('type','B')
                ->where('reference','LIKE','Permata%')
                ->orderby('datebo','desc')
                ->first();
        Log::info('get last cash balance '.$datebo);
        if (!is_null($datebo)) {
            return $datebo->datebo;
        }else {
            return Carbon::yesterday()->format('Y-m-d H:i:s.u');
        }
    }

    public function insertCashBo($account,$permata):bool 
    {
        
        $msgRqHdr = $this->permata['MsgRqHdr'];
        $transactionInfo = $this->permata['TransactionInfo'];
        $statements = $transactionInfo['Statements'];
        
        $date = Carbon::now();
        $amount ='' ;
        if ( $statements['DC'] == 'C') {
            $amount = '-'.$statements['CashValue'];
        } else if ( $statements['DC'] == 'D')  {
            $amount = $statements['CashValue'];
        }
        //  sending to cash bo 
        echo 'queue 2 ';
        $result = DB::connection('sasoldev')
        ->table('CashBo')
        ->insert([
            "DateBo" => Carbon::now(),
            "ClientNo" => $account[0]->no_cust,
            "Reference" => "Permata ".$date->format("Y/m/d H:m:s"),
            "Amount"=> $amount,
            "Type" => 'M',
            "Flag" => 0
        ]);
        if ($result) {
            
            Log::info('send to Cashbo '.
            "DateBo ".Carbon::now() .",ClientNo " .$account[0]->no_cust.
            ",Reference " ."Permata ".$date->format("Y/m/d H:m:s").
            ",Amount ".$amount.
            ",Type ".'M'.
            ",Flag "."0"
            );

            // update permataAs is to hero 
            $result = DB::connection('sasoldev')
                ->table('permataAS')
                ->where('cust_ref_id','=',$msgRqHdr['CustRefID'])
                ->update(['is_to_hero' => '1']);
            return true;
        } else {
            return false;
        }
        
    }

    
    public function insertTSms($account,$permata){   
        Log::info('insert sms '.$account->no_cust);     
        $tsms = new Tsms();
        $tsms->clientno = $account->no_cust;
        $tsms->name = $account->name;
        $tsms->amount = $permata->cash_value;
        $tsms->phone = $account->phone2;
        $tsms->datercv = date("Y/m/d");
        $tsms->flag = 0;
        $tsms->accname = $permata->acc_no;
        $tsms->account = $account->acc_namesub;
        $tsms->bank = 'Permata';
        $tsms->staclient = $account->lorf;
        $tsms->stainput = 1;
        if ($tsms->save()) {
            Log::info('Berhasil Insert TSMS ' . $tsms->toJson() );
        }else {
            Log::info('Gagal Insert TSMS ' . $tsms->toJson() );
        }
        
    }
    

    public function getCashBo($pTxType){
        if ($pTxType = 'NTRF') 
            $result = 'M';
        else if ($pTxType = 'NINT') 
            $result = 'I';
        else if ($pTxType = 'NREV') 
            $result = 'C';
        else if ($pTxType = 'NKOR') 
            $result = 'C';
        else if ($pTxType = 'NTAX') 
            $result = 'T';
        else if ($pTxType = 'NCHG') 
            $result = 'F';
        else if ($pTxType = 'NEXT') 
            $result = 'M';
        return $result;
    }
}
