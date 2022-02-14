<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'IndexController@index');

//登录
Route::get('login','Admin\LoginController@login');
//登录处理
Route::get('loginAction','Admin\LoginController@loginAction');
//退出登录
Route::get('loginOut','Admin\LoginController@loginOut');
Route::group(['prefix' => 'admin',"middleware" => "checkLogin"], function (){
    //后台首页
    Route::get('index','Admin\IndexController@index');
    //用户列表
    Route::get('customerList','Admin\CustomerController@customerList');
    //新增用户
    Route::get('addCustomer','Admin\CustomerController@addCustomer');
    //新增用户处理
    Route::post('addCustomerAction','Admin\CustomerController@addCustomerAction');
    //Excel导入用户
    Route::post('import','Admin\CustomerController@import');
    //编辑用户
    Route::get('editCustomer','Admin\CustomerController@editCustomer');
    //编辑用户处理
    Route::post('editCustomerAction','Admin\CustomerController@editCustomerAction');
    //删除用户
    Route::get('deleteCustomer/{id}','Admin\CustomerController@deleteCustomer');
    //测评列表
    Route::get('evaluateList','Admin\EvaluateController@evaluateList');
    //添加测评等级
    Route::get('addEvaluate','Admin\EvaluateController@addEvaluate');
    //添加测评等级处理
    Route::post('addEvaluateAction','Admin\EvaluateController@addEvaluateAction');
    //编辑测评等级
    Route::get('editEvaluate','Admin\EvaluateController@editEvaluate');
    //编辑测评等级处理
    Route::post('editEvaluateAction','Admin\EvaluateController@editEvaluateAction');
    //删除测评等级
    Route::get('deleteEvaluate/{id}','Admin\EvaluateController@deleteEvaluate');
    //能力维度
    Route::get('ability','Admin\EvaluateController@ability');
    //新增能力维度
    Route::get('addAbility','Admin\EvaluateController@addAbility');
    //新增能力维度处理
    Route::post('addAbilityAction','Admin\EvaluateController@addAbilityAction');
    //编辑能力维度
    Route::get('editAbility','Admin\EvaluateController@editAbility');
    //编辑能力维度处理
    Route::post('editAbilityAction','Admin\EvaluateController@editAbilityAction');
    //删除能力维度
    Route::get('deleteAbility/{id}','Admin\EvaluateController@deleteAbility');
    //能力词条
    Route::get('entry','Admin\EvaluateController@entry');
    //新增能力词条
    Route::get('addEntry','Admin\EvaluateController@addEntry');
    //新增能力词条处理
    Route::post('addEntryAction','Admin\EvaluateController@addEntryAction');
    //编辑能力词条
    Route::get('editEntry','Admin\EvaluateController@editEntry');
    //编辑能力词条处理
    Route::post('editEntryAction','Admin\EvaluateController@editEntryAction');
    //删除能力词条
    Route::get('deleteEntry/{id}','Admin\EvaluateController@deleteEntry');
    //问题
    Route::get('question','Admin\EvaluateController@question');
    //新增问题
    Route::get('addQuestion','Admin\EvaluateController@addQuestion');
    //新增问题处理
    Route::post('addQuestionAction','Admin\EvaluateController@addQuestionAction');
    //编辑问题
    Route::get('editQuestion','Admin\EvaluateController@editQuestion');
    //编辑问题处理
    Route::post('editQuestionAction','Admin\EvaluateController@editQuestionAction');
    //删除问题
    Route::get('deleteQuestion/{id}','Admin\EvaluateController@deleteQuestion');
    //选项
    Route::get('option','Admin\EvaluateController@option');
    //新增选项
    Route::get('addOption','Admin\EvaluateController@addOption');
    //新增选项处理
    Route::post('addOptionAction','Admin\EvaluateController@addOptionAction');
    //编辑选项
    Route::get('editOption','Admin\EvaluateController@editOption');
    //编辑选项处理
    Route::post('editOptionAction','Admin\EvaluateController@editOptionAction');
    //删除选项
    Route::get('deleteOption/{id}','Admin\EvaluateController@deleteOption');
    //统计管理
    Route::get('report','Admin\EvaluateController@report');
    //导出测评报告
    Route::get('exportReport','Admin\EvaluateController@exportReport');
    //导自测评报告
    Route::get('exportUserReport','Admin\EvaluateController@exportUserReport');

    // TODO:: 20210812新增筛选导出
    Route::get('chooseIndex','Admin\ChooseController@index');
    Route::get('chooseExport','Admin\ChooseController@export');
    Route::post('chooseAbility','Admin\ChooseController@ability');

    // TODO:: 20210901导出指定选项
    Route::get('chooseOption','Admin\ChooseController@optionList');
    Route::get('chooseOptionStatus/{id}','Admin\ChooseController@optionStatus');
});
