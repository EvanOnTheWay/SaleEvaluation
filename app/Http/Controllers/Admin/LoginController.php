<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login()
    {
        return view('login');
    }
    /**
     * 后台登陆处理
     * @param Request $request
     * @return false|string
     */
    public function loginAction(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');
        if($username == 'admin' && $password == 123456){
            //登陆成功存session
            $request->session()->put('username',$username);
            $data['uses'] = 1;
        }else{
            $data['uses'] = -1;
        }
        return json_encode($data);
    }

    /**
     * 退出登陆
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function loginOut(Request $request)
    {
        $request->session()->forget('username');
        return redirect('/login');
    }
}
