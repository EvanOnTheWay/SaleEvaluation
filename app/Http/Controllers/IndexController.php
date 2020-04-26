<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    /**
     * 入口文件
     * @param Request $request
     * @return RedirectResponse|Redirector|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws InvalidConfigException
     */
    public function index(Request $request)
    {
        $wework = Factory::work(Config::get('wework'));

        if ($request->has('code')) {
            $user = $wework->oauth->user()->getOriginal();

            /**
             * 当用户为企业成员时返回 UserId，否则返回 OpenId
             * @see https://work.weixin.qq.com/api/doc#90000/90135/91023
             */
            if (!isset($user['UserId'])) {
                //TODO 待补全错误页面地址
                return redirect('非企业成员跳至错误页面');
            }

            /**
             * 根据openid获取手机号，查看当前登陆的人是否在白名单中
             */

            $userInfo = DB::select('SELECT TOP 1 * FROM user WHERE userId = ?', [$user['UserId']]);

            if (empty($userInfo)) {
                /**
                 * 为空说明当前userId数据库不存在，需获取手机号跟数据库白名单指定用户(userId相同)做绑定
                 */
                $data = $wework->user->get($user['UserId']);
                $userInfo = DB::select('SELECT TOP 1 * FROM user WHERE phone = ?', [$data['mobile']]);
                if (empty($userInfo)) {
                    //TODO 待补全错误页面地址
                    return redirect('不在用户白名单跳至错误页面');
                }

                /**
                 * 企业微信与白名单绑定操作
                 */
                DB::update('UPDATE user SET userId = ?,join_time = ?,status = ? WHERE phone = ?', [$user['UserId'], Carbon::now(), 1, $data['mobile']]);

            }

            //TODO 补全url
            return redirect('https://www.baidu.com?userid=' . $userInfo[0]['id'] . 'role=' . $userInfo[0]['role']);
        }

        return $wework->oauth
            ->scopes(['snsapi_base'])
            ->redirect($request->fullUrl());
    }
}
