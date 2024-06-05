<?php

namespace App\Http\Controllers;

use App\Models\PermataAS;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PermataController extends Controller
{

    public function test(Request $request){
        return response()->json(['responseCode'=>'01','resquest'=>$request->response]);
    }
    
    public function notif(Request $request){
        try {
            $validator = Validator::make($request->all(),[
                "NotificationTransactionRq.TransactionInfo.GroupID"=>"required",
                "NotificationTransactionRq.MsgRqHdr.CustRefID" => "required",
                "NotificationTransactionRq.TransactionInfo.SeqNum"=>"required",
                "NotificationTransactionRq.TransactionInfo.AccountNumber"=>"required",
                "NotificationTransactionRq.TransactionInfo.Currency"=>"required",
                "NotificationTransactionRq.TransactionInfo.ValueDate"=>"required",
                "NotificationTransactionRq.TransactionInfo.OpeningBalance"=>"required",
                "NotificationTransactionRq.TransactionInfo.Statements.DC"=>"required",
                "NotificationTransactionRq.TransactionInfo.Statements.CashValue"=>"required",
                "NotificationTransactionRq.MsgRqHdr.RequestTimestamp"=>"required"
                ]);
            
            $notifTrans = $request->NotificationTransactionRq;
            $msgRqHdr = $notifTrans['MsgRqHdr'];
            $transactionInfo = $notifTrans['TransactionInfo'];
            $statements = $transactionInfo['Statements'];

            if ($validator->fails()) {
            //     # code error respon
                $return = [
                    "NotificationTransactionRs" =>
                        [
                            "MsgRsHdr"=> [
                                "ResponseTimestamp"=> date(DATE_ATOM,time()),
                                "CustRefID"=> $msgRqHdr['CustRefID'],
                                "StatusCode"=> "02",
                                "StatusDesc"=> "Failed"
                            ]
                        ]
                ];
                return response()->json($return)->setStatusCode(Response::HTTP_UNAUTHORIZED);
             } else {
                $cekExtRef = PermataAS::where('cust_ref_id',$msgRqHdr['CustRefID'])->first();
                
                # code cek account number 
                $cekAccount = DB::connection('sqlsrv')
                    ->table('sas.dbo.subacc')
                    ->where('account_sub',$transactionInfo['AccountNumber'])
                    ->get();
                    
                if ($cekExtRef) {
                      
                    $return = [
                        "NotificationTransactionRs" =>
                            [
                                "MsgRsHdr"=> [
                                    "ResponseTimestamp"=> date(DATE_ATOM,time()),
                                    "CustRefID"=> $msgRqHdr['CustRefID'],
                                    "StatusCode"=> "01",
                                    "StatusDesc"=> "Failed"
                                ]
                            ]
                    ];

                    return response()->json($return)->setStatusCode(Response::HTTP_UNAUTHORIZED);;
                 } else {
                    $data = new PermataAS();                    
                    $data->cust_ref_id = $msgRqHdr['CustRefID'] ;
                    // $data->request_timestamp = Carbon::parse($msgRqHdr['RequestTimestamp'])->timestamp;
                    $data->status = $request['status'];
                    $data->group_id = $transactionInfo['GroupID'];
                    $data->seqnum = $transactionInfo['SeqNum'] ;
                    $data->account_number = $transactionInfo['AccountNumber'] ;
                    $data->currency = $transactionInfo['Currency'] ;
                    $data->value_date = $transactionInfo['ValueDate'] ;
                    $data->opening_balance = $transactionInfo['OpeningBalance'];
                        $data->extref = $statements['ExtRef'];
                        $data->trx_type = $statements['TrxType'] ;
                        $data->flag = $statements['Flag'] ;
                        $data->dc = $statements['DC'];
                        $data->cash_value = $statements['CashValue'];
                        $data->description = $statements['Description'];
                    $data->close_bal = $transactionInfo['CloseBal'] ;
                    $data->notes  = $transactionInfo['Notes'];    

                    $data->save();   

                    $return = [
                        "NotificationTransactionRs" =>
                            [
                                "MsgRsHdr"=> [
                                    "ResponseTimestamp"=> date(DATE_ATOM,time()),
                                    "CustRefID"=> $msgRqHdr['RequestTimestamp'],
                                    "StatusCode"=> "00",
                                    "StatusDesc"=> "Success"
                                ]
                            ]
                    ];

                    return response()->json($return)->setStatusCode(
                        Response::HTTP_OK);
                } 
            }
            
            $return = [
                "NotificationTransactionRs" =>
                    [
                        "MsgRsHdr"=> [
                            "ResponseTimestamp"=> date(DATE_ATOM,time()),
                            "CustRefID"=> $request->CustRefID,
                            "StatusCode"=> "01",
                            "StatusDesc"=> "Failed"
                        ]
                    ]
            ];

            return response()->json($return)->setStatusCode(Response::HTTP_UNAUTHORIZED);
            
        } catch (\Throwable $th) {
            //throw $th;
            $return = [
                "NotificationTransactionRs" =>
                    [
                        "MsgRsHdr"=> [
                            "ResponseTimestamp"=> date(DATE_ATOM,time()),
                            "CustRefID"=> 'trow',
                            "StatusCode"=> "01",
                            "StatusDesc"=> $th
                        ]
                    ]
            ];
            return  response()->json($return)->setStatusCode(Response::HTTP_UNAUTHORIZED);;
        }
    }
}
