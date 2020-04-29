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
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'OnlineStatus' => 'required',
        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid();
        }
        $OnlineStatus = $request->input('OnlineStatus');

        /**
         * 如果当前提交为自评
         */
        if ($OnlineStatus == 1) {

            $RankId = $request->input('RankId');
            $EmployeeId = $request->input('EmployeeId');
            $Type = $request->input('Type');
            $OtherId = $request->input('OtherId');

            // 模拟报告数据
            $UserReport = ReportService::getRankInfo(1);

            // 报告处理，取出词条
            $entry_array = array();
            foreach ($UserReport as $value) {
                foreach ($value->entry_data as $vs) {
                    $entry_array[] = $vs;
                }
            }

            // 定义自评等级小于、等于、大于标准数组
            $standard_less = array();
            $standard_equal = array();
            $standard_greater = array();

            // 定义选项数组
            $option_array = array();

            // 自评等级处理
            foreach ($entry_array as $key => $value) {

                // 问题列表、选项列表遍历
                foreach ($value->question_data as $vs) {
                    foreach ($vs->option_data as $vx) {
                        if ($vx->status == 1) {
                            $vx->QuestionId = $vs->Id;
                            $vx->ChooseId = $vx->Id;
                            $option_array[] = $vx;
                        }
                    }
                }

                // 统计勾选数量
                $option_num = count($option_array);

                // 评分标准转数组
                $ScoreCriteria = $value->ScoreCriteria;
                $ScoreArray = json_decode($ScoreCriteria, true);

                // 评测标准对应分值
                $ser_codes = config('services.ScoreCriteria');

                // 当前词条标准分数
                $standard = $ser_codes[trim($value->ArrivelLevel)];
                $value->StandardSource = $standard;

                // 自评输入内容
                $UserContent = $request->input('UserContent');
                $UserContents = $request->input('UserContents');
                $UserContentx = $request->input('UserContentx');

                // 他评等级，分数
                $value->OtherRank = "";
                $value->OtherSource = "";

                // 如果小于最低评分标准，则自评等级为：标准范围之外
                $LowSource = explode("-", $ScoreArray['f']);
                if ($option_num < $LowSource[0]) {
                    // UserColor：自评等级颜色
                    $value->UserRank = "!";
                    $value->UserSource = "!";
                    $value->UserColor = 4;
                }

                // 计算自评等级
                foreach ($ScoreArray as $kz => $vz) {
                    $scope = explode("-", $vz);
                    if ($option_num >= $scope[0] && $option_num <= $scope[1]) {
                        // 自评等级，分数
                        $value->UserRank = $kz;
                        $UserSource = $ser_codes[$kz];
                        $value->UserSource = $ser_codes[$kz];
                        // 自评等级和标准对比：1小于，2等于，3大于
                        if ($UserSource > $standard) {
                            $value->UserColor = 3;
                            $standard_greater[] = $value->Title;
                        }
                        if ($UserSource == $standard) {
                            $value->UserColor = 2;
                            $standard_equal[] = $value->Title;
                        }
                        if ($UserSource < $standard) {
                            $value->UserColor = 1;
                            $standard_less[] = $value->Title;
                        }
                    }
                }
            }

            // 整合评测总结
            $standard_less = implode(",", $standard_less);
            $standard_equal = implode(",", $standard_equal);
            $standard_greater = implode(",", $standard_greater);
            $Summary = $standard_less . "：还需加油；" . $standard_equal . "：能力达标;" . $standard_greater . "：表现优异！";
            $Reports = json_encode($entry_array, JSON_UNESCAPED_UNICODE);

            $result = ReportService::insertReport($RankId, $EmployeeId, $OtherId, $UserContent, $UserContents, $UserContentx, $Type, $Summary, $Reports);

            // 勾选的选项入库
            foreach ($option_array as $va) {
                ReportService::insertAnswer($result, $EmployeeId, $va->QuestionId, $va->ChooseId, $OnlineStatus);
            }

            return ResponseWrapper::success();
        }

        /**
         * 如果当前提交为他评
         */
        if ($OnlineStatus == 2) {


        }
    }
}
