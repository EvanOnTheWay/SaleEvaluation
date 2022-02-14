<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::group(['namespace' => 'Front', 'middleware' => ['cross']], function () {
    //首页
    Route::post('index', 'IndexController@index');
    //个人信息
    Route::post('getUserInfo', 'IndexController@getUserInfo');
    //我的下属
    Route::post('myBranch', 'IndexController@myBranch');
    //测评等级预览
    Route::get('evaluationLevelList', 'EvaluateController@evaluationLevelList');
    //能力模型预览预览
    Route::post('evalModelPreview', 'EvaluateController@evalModelPreview');
    //互评邀请列表
    Route::post('mutualEval', 'EvaluateController@mutualEval');
    //所有测评列表
    Route::post('allEvaluateList', 'EvaluateController@allEvaluateList');
    //是否需要他评
    Route::post('needOtherEvaluation', 'EvaluateController@needOtherEvaluation');
    //从已评列表邀请他评
    Route::post('sendOffer', 'EvaluateController@sendOffer');

    /**
     * writing by roger start
     */
    Route::post('/report/index/', 'ReportController@index');
    Route::post('/report/create', 'ReportController@create');
    Route::post('/report/entryDetail', 'ReportController@entryDetail');
    Route::post('/report/optionDetail', 'ReportController@optionDetail');
    /**
     * writing by roger end
     */

    Route::get('image', 'IndexController@image');
    Route::get('help', 'IndexController@help');

    // TODO:: 202105同步第三方的用户信息
    Route::post('UserUpdate', 'IndexController@UserUpdate');
    Route::post('UserStatus', 'IndexController@UserStatus');
    Route::post('UserUpdateAll', 'IndexController@UserUpdateAll');
});


