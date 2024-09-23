<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    
    public function createUser(Request $request) {
        try {
            
            $validatorUser = Validator::make(
                $request->all(),
                    [
                        'name'=>'required',
                        'email'=>'required|email|unique:users,email',
                        'password'=> 'required'
                    ]
                );
            if ($validatorUser->fails()) {
                return response()->json([
                    ''=> false,
                    'message' => 'validation error',
                    'errors' => $validatorUser->errors()
                ],404);
            }

            $user = User::create([
                'name'=>$request->name,
                'email'=>$request->email,
                'password'=> Hash::make($request->password)
            ]);
            
            return response()->json([
                'status'=> true,
                'message'=> 'User Created Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ],200);

        } catch (\Throwable $th) {
            return response()->json([
                'status'=> false,
                'message'=> $th->getMessage()
            ],500);
        }
    }

    public function loginUser(Request $request) {
        try {
            $validatorUser = Validator::make(
                $request->all(),
                    [
                        'email'=>'required|email',
                        'password'=> 'required'
                    ]
                );
            
                if ($validatorUser->fails()) {
                    return response() ->json([
                        'status'=>false,
                        'message' => 'email & password does not match with our record'
                    ],401);
                }

                $user = User::where('email',$request->email)->first();

                return response()->json([
                    'status'=>true,
                    'message'=> 'User Logged In successfully',
                    'token' => $user->createToken("API TOKEN")->plainTextToken
                ], 200);

        } catch (\Throwable $th) {            
            return response()->json([
                'status'=> false,
                'message'=> $th->getMessage()
            ],500);
        }
    }

    public function logoutUser(Request $request) {
        $accessToken = $request->bearerToken();

        $token= PersonalAccessToken::findToken($accessToken);

        $token->delete();
        return[
            'message'=>'user logged out'
        ];
    }

}
