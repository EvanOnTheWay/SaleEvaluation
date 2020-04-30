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
            $Type = $request->input('Type');
            $OtherId = $request->input('OtherId');
            $EmployeeId = $request->input('EmployeeId');

            // 模拟自评报告数据
            $UserReport = ReportService::getRankInfos($RankId);

            // 自评报告处理，取出词条
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

            // 定义选项总数组，存入答案表
            $option_array = array();

            // 自评等级处理
            foreach ($entry_array as $key => $value) {

                // 定义选项数组，仅针对当前循坏
                $option_val_array = array();

                // 问题列表、选项列表遍历
                foreach ($value->question_data as $vs) {
                    foreach ($vs->option_data as $vx) {
                        if ($vx->Status == 1) {
                            $vx->QuestionId = $vs->Id;
                            $vx->ChooseId = $vx->Id;
                            $option_array[] = $vx;
                            $option_val_array[] = $vx;
                        }
                    }
                }

                // 统计当前词条勾选量
                $option_num = count($option_val_array);

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
                    $value->UserRank = "!";
                    $value->UserSource = "!";
                    $value->UserColor = 4;
                }

                // 计算自评等级，分数
                foreach ($ScoreArray as $kz => $vz) {
                    $scope = explode("-", $vz);
                    if ($option_num >= $scope[0] && $option_num <= $scope[1]) {
                        $value->UserRank = $kz;
                        $UserSource = $ser_codes[$kz];
                        $value->UserSource = $ser_codes[$kz];
                        // UserColor：1小于，2等于，3大于，4范围外
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
            $UserSummary = $standard_less . "：还需加油；" . $standard_equal . "：能力达标;" . $standard_greater . "：表现优异！";
            $Reports = json_encode($entry_array, JSON_UNESCAPED_UNICODE);

            // 评测记录数据入库
            $result = ReportService::insertReport($RankId, $EmployeeId, $OtherId, $UserContent, $UserContents, $UserContentx, $Type, $UserSummary, $Reports);

            // 自评勾选选项入库
            foreach ($option_array as $va) {
                ReportService::insertAnswer($result, $EmployeeId, $va->QuestionId, $va->ChooseId, $OnlineStatus);
            }

            return ResponseWrapper::success();
        }

        /**
         * 如果当前提交为他评
         */
        if ($OnlineStatus == 2) {

            $ReportId = $request->input('ReportId');

            // 获取已完成的自评报告数据
            $UserReportInfo = ReportService::getUserReport($ReportId);

            // 模拟他评报告数据
            $OtherReport = ReportService::getRankInfos($UserReportInfo->RankId);

            // 他评报告处理，取出词条
            $entry_array = array();
            foreach ($OtherReport as $value) {
                foreach ($value->entry_data as $vs) {
                    $entry_array[] = $vs;
                }
            }

            // 定义他评等级小于、等于、大于标准数组
            $standard_less = array();
            $standard_equal = array();
            $standard_greater = array();

            // 定义选项总数组，存入答案表
            $option_array = array();

            // 他评等级处理
            foreach ($entry_array as $key => $value) {

                // 定义选项数组，仅针对当前循坏
                $option_val_array = array();

                // 问题列表、选项列表遍历
                foreach ($value->question_data as $vs) {
                    foreach ($vs->option_data as $vx) {
                        if ($vx->Status == 1) {
                            $vx->QuestionId = $vs->Id;
                            $vx->ChooseId = $vx->Id;
                            $option_array[] = $vx;
                            $option_val_array[] = $vx;
                        }
                    }
                }

                // 统计勾选数量
                $option_num = count($option_val_array);

                // 评分标准转数组
                $ScoreCriteria = $value->ScoreCriteria;
                $ScoreArray = json_decode($ScoreCriteria, true);

                // 评测标准对应分值
                $ser_codes = config('services.ScoreCriteria');

                // 当前词条标准分数
                $standard = $ser_codes[trim($value->ArrivelLevel)];
                $value->StandardSource = $standard;

                // 他评输入内容
                $OtherContent = $request->input('OtherContent');
                $OtherContents = $request->input('OtherContents');
                $OtherContentx = $request->input('OtherContentx');

                // 自评等级，分数
                $value->UserRank = "";
                $value->UserSource = "";

                // 如果小于最低评分标准，则他评等级为：标准范围之外
                $LowSource = explode("-", $ScoreArray['f']);
                if ($option_num < $LowSource[0]) {
                    // UserColor：他评等级颜色
                    $value->OtherRank = "!";
                    $value->OtherSource = "!";
                    $value->OtherColor = 4;
                }

                // 计算他评等级
                foreach ($ScoreArray as $kz => $vz) {
                    $scope = explode("-", $vz);
                    if ($option_num >= $scope[0] && $option_num <= $scope[1]) {
                        // 他评等级，分数
                        $value->OtherRank = $kz;
                        $OtherSource = $ser_codes[$kz];
                        $value->OtherSource = $ser_codes[$kz];
                        // 自评等级和标准对比：1小于，2等于，3大于
                        if ($OtherSource > $standard) {
                            $value->OtherColor = 3;
                            $standard_greater[] = $value->Title;
                        }
                        if ($OtherSource == $standard) {
                            $value->OtherColor = 2;
                            $standard_equal[] = $value->Title;
                        }
                        if ($OtherSource < $standard) {
                            $value->OtherColor = 1;
                            $standard_less[] = $value->Title;
                        }
                    }
                }
            }

            // 他评报告JSON
            $OtherReport = json_encode($entry_array, JSON_UNESCAPED_UNICODE);

            /**
             * 根据自评报告、他评报告，推算最终的报告
             */
            $UserAnswerArray = ReportService::getAnswerArray($ReportId, 1);
            foreach ($entry_array as $key => $value) {
                foreach ($value->question_data as $kv => $vv) {
                    foreach ($vv->option_data as $kz => $vz) {
                        foreach ($UserAnswerArray as $km => $vm) {
                            if($vz->Id == $vm->ChooseId) {
                                // 自评勾选，他评未勾选，Status = 2
                                if($vz->Status == 0) {
                                    $vz->Status = 2;
                                }
                                // 自评、他评都勾选
                                if($vz->Status == 1) {
                                    $vz->Status = 3;
                                }
                            }
                        }
                        // 都未勾选，Status = 0；他评勾选，自评未勾选，Status = 1
                    }
                }
            }

            // 最终报告中，加入之前自评的等级和分数
            foreach ($entry_array as $key => $value) {
                foreach (json_decode($UserReportInfo->UserReport) as $ks => $vs) {
                    if ($value->Id == $vs->Id && isset($vs->UserRank)) {
                        $value->UserRank = $vs->UserRank;
                        $value->UserSource = $vs->UserSource;
                        $value->UserColor = $vs->UserColor;
                    }
                }
            }

            // 整合评测总结
            $standard_less = implode(",", $standard_less);
            $standard_equal = implode(",", $standard_equal);
            $standard_greater = implode(",", $standard_greater);
            $OtherSummary = $standard_less . "：还需加油；" . $standard_equal . "：能力达标;" . $standard_greater . "：表现优异！";

            // 最终报告JSON
            $ReportInfo = json_encode($entry_array, JSON_UNESCAPED_UNICODE);

            // 评测记录数据更新
            ReportService::updateReport($ReportId, $OtherContent, $OtherContents, $OtherContentx, $OtherSummary, $OtherReport, $ReportInfo);

            // 他评勾选选项入库
            foreach ($option_array as $va) {
                ReportService::insertAnswer($ReportId, $UserReportInfo->OtherId, $va->QuestionId, $va->ChooseId, $OnlineStatus);
            }

            return ResponseWrapper::success();
        }
    }

    /**
     * 报告查看，摒弃题目及选项
     */
    public function entryDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ReportId' => 'required',
        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid();
        }

        $ReportId = $request->input('ReportId');
        $result = ReportService::getUserReport($ReportId);
        $info = json_decode($result->ReportInfo);

        foreach ($info as $key=>$value){
            unset($value->question_data);
        }

        return ResponseWrapper::success($info);
    }

    /**
     * 报告查看，显示题目及选项
     */
    public function optionDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'EntryId' => 'required',
            'ReportId' => 'required',
        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid();
        }

        $EntryId = $request->input('EntryId');
        $ReportId = $request->input('ReportId');
        $result = ReportService::getUserReport($ReportId);
        $info = json_decode($result->ReportInfo);

        $data = array();
        foreach ($info as $key=>$value){
            if($value->Id == $EntryId) {
                $data[] = $value;
            }
        }

        return ResponseWrapper::success($data);
    }
}
