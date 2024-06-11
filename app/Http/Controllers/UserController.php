<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //

    public function register(Request $request){

        try {
            //code...
            $validator = Validator::make($request->all(),[
                "name" => "required",
                "email" => "required",
                "password" => "required"
            ]);

            if ($validator) {
                # code...
                return response()->json(["Message"=> "field Null"],401);
            } else {
                $user = new User();
                $user->name  = $request->name ;
                $user->email  = $request->email ;
                $user->password  = $request->password ;
        
                $user->save();
                return response()->json(["Message"=> "User suseccesfull create"],201);
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
