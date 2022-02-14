<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\ResponseStatus;
use App\Http\ResponseWrapper;
use App\Services\EvaluateService;
use App\Services\ReportService;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Exceptions\RuntimeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EvaluateController extends Controller
{

    /**
     * 评测等级列表
     * @return array
     */
    public function evaluationLevelList()
    {
        $res = EvaluateService::newInstance()->list();
        return ResponseWrapper::success($res);
    }

    /**
     * 互评邀请列表
     * @param Request $request
     * @return array
     */
    public function mutualEval(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Id' => 'required',

        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid();
        }
        $id = $request->input('Id');
        $res = EvaluateService::newInstance()->mutual($id);
        return ResponseWrapper::success($res);
    }


    /**
     * 所有测评列表
     * @param Request $request
     * @return array
     */
    public function allEvaluateList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Id' => 'required',
            'Positions' => 'required'

        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid();
        }
        $id = $request->input('Id');
        $positions = $request->input('Positions');
        $res = EvaluateService::newInstance()->evalList($id, $positions);
        return ResponseWrapper::success($res);
    }

    /**
     * 是否需要他评
     * @param Request $request
     * @return array
     */
    public function needOtherEvaluation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Id' => 'required',

        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid();
        }
        $id = $request->input('Id');
        $res = EvaluateService::newInstance()->otherEvaluate($id);
        return ResponseWrapper::success($res);
    }

    /**
     * 能力模型预览
     * @param Request $request
     * @return array
     */
    public function evalModelPreview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'RankId' => 'required',

        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid();
        }
        $rankId = $request->input('RankId');
        $res = EvaluateService::newInstance()->preview($rankId);
        return ResponseWrapper::success($res);
    }

    /**
     * 发送邀请
     * @param Request $request
     * @return array
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function sendOffer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Id' => 'required',
            'ReportId' => 'required',
            'OtherId' => 'required'

        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid();
        }
        $id = $request->input('Id');
        $reportId = $request->input('ReportId');
        $otherId = $request->input('OtherId');
        $res = EvaluateService::newInstance()->offer($id, $reportId, $otherId);
        if ($res) {
            // 推送
            $sendRes = ReportService::newInstance()->selectEmployeeUserId($id);

            $otherRes = ReportService::newInstance()->selectEmployeeUserId($otherId);

            if ($otherRes->UserId == NULL || $otherRes->UserId == '') {
                return ResponseWrapper::success();
            }

            $object = new \App\Http\Controllers\IndexController();

            $object->sendMessage('您收到一条来自' . $sendRes->Name . '的测评邀请', '请点击查看', env('APP_URL') . '/dist/index.html?id=' . $otherRes->Id . '&position=' . $otherRes->Positions . '#/home/notice', $otherRes->UserId);

            return ResponseWrapper::success();

        } else {
            return ResponseWrapper::failure(ResponseStatus::REFER_ERROR);
        }

    }
}
