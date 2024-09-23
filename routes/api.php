<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PermataController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/auth/register', [AuthController::class,'createUser']);
Route::post('/auth/login', [AuthController::class,'loginUser']);
Route::post('/auth/logout', [AuthController::class,'logoutUser']);

// regist user 
Route::post('/register/user',[UserController::class,'register']);
// Route::middleware('auth.basic')
//     ->post('/api/appldata_v2/appldata/trxnotify',function(Request $request){
        
// });

// Route::post('/appldata_v2/appldata/test',[PermataController::class,'test']);
Route::middleware(['basicAuth'])->group(function (){
    Route::post('/appldata_v2/appldata/test',[PermataController::class,'test']);    
    Route::post('/appldata_v2/appldata/trxnotify',[PermataController::class,'notif']);
});