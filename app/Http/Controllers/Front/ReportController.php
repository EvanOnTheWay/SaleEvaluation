<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\ResponseWrapper;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class ReportController extends Controller
{
    /**
     * 根据等级ID,获取评测内容
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'RankId' => 'required',
        ]);

        if ($validator->fails()) {
            return ResponseWrapper::invalid();
        }

        $RankId = $request->input('RankId');
        $result = ReportService::getRankInfo($RankId);

        return ResponseWrapper::success($result);
    }


    /**
     * 评测完成，数据提交保存Api
     */
    public function create()
    {

        // 预定义评测数据
//        $tst_array = array(
//
//            'OnlineStatus' => 1,        // 当前评测类型,1用户的自评,2上级的他评
//            'Type' => 2,                // 是否勾选他评: 1已勾选,2未勾选
//            'UserId' => 1,              // 评测人ID
//            'OtherId' => 2,             // 如果是他评,他评人ID
//            'RankId' => 1,              // 评测等级ID,例:p1
//            'Contents' => "还不错!"      // 输入的评价
//
//        );


    }
}
