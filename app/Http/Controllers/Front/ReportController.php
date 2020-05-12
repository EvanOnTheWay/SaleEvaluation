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

        // 评测等级小于、等于、大于标准
        $standard_less = array();
        $standard_equal = array();
        $standard_greater = array();
        // 定义不在评测等级范围内的数组
        $standard_outside = array();
        // 定义选项总数组，用于存答案表
        $option_array = array();

        // 获取接收到的报告数组
        $GetReport = $request->input('ReportInfo');

        // 报告数组处理，取出词条
        $entry_array = array();
        foreach ($GetReport as $value) {
            foreach ($value['entry_data'] as $vs) {
                $entry_array[] = $vs;
            }
        }

        /**
         * 如果当前提交为自评
         */
        if ($OnlineStatus == 1) {

            $RankId = $request->input('RankId');
            $Type = $request->input('Type');
            $OtherId = $request->input('OtherId');
            $EmployeeId = $request->input('EmployeeId');

            // 自评等级处理
            foreach ($entry_array as $key => $value) {

                // 定义选项数组，仅针对当前循坏
                $option_val_array = array();

                // 问题列表、选项列表遍历
                foreach ($value['question_data'] as $vs) {
                    foreach ($vs['option_data'] as $vx) {
                        if ($vx['Status'] == 2) {
                            $vx['QuestionId'] = $vs['Id'];
                            $vx['ChooseId'] = $vx['Id'];
                            $option_array[] = $vx;
                            $option_val_array[] = $vx;
                        }
                    }
                }

                // 统计当前词条勾选量
                $option_num = count($option_val_array);

                // 评分标准转数组
                $ScoreCriteria = $value['ScoreCriteria'];
                $ScoreArray = json_decode($ScoreCriteria, true);

                // 评测标准对应分值
                $ser_codes = config('services.ScoreCriteria');

                // 当前词条标准分数


                $arrivelLevel = explode('/', $value['ArrivelLevel']);

                $standard = $ser_codes[trim(end($arrivelLevel))];
                $entry_array[$key]['StandardSource'] = $standard;

                // 自评输入内容
                $UserContent = $request->input('UserContent');
                $UserContents = $request->input('UserContents');
                $UserContentx = $request->input('UserContentx');

                // 他评等级，分数
                $entry_array[$key]['OtherRank'] = "";
                $entry_array[$key]['OtherSource'] = "";
                $entry_array[$key]['OtherColor'] = "";

                // 如果小于最低评分标准，则自评等级为：标准范围之外
                $LowSource = explode("-", $ScoreArray['F']);
                if ($option_num < $LowSource[0]) {
                    $entry_array[$key]['UserRank'] = "!";
                    $entry_array[$key]['UserSource'] = "!";
                    $entry_array[$key]['UserColor'] = 4;
                    $standard_outside[] = "<span style='color: #fe6305'>" . $value['Title'] . "</span>";
                }

                // 计算自评等级，分数
                foreach ($ScoreArray as $kz => $vz) {
                    $scope = explode("-", $vz);
                    if ($option_num >= $scope[0] && $option_num <= $scope[1]) {
                        $entry_array[$key]['UserRank'] = $kz;
                        $UserSource = $ser_codes[$kz];
                        $entry_array[$key]['UserSource'] = $ser_codes[$kz];
                        // UserColor：1小于，2等于，3大于，4范围外
                        if ($UserSource > $standard) {
                            $entry_array[$key]['UserColor'] = 3;
                            $standard_greater[] = "<span style='color: #fe6305'>" . $value['Title'] . "</span>";
                        }
                        if ($UserSource == $standard) {
                            $entry_array[$key]['UserColor'] = 2;
                            $standard_equal[] = "<span style='color: #fe6305'>" . $value['Title'] . "</span>";
                        }
                        if ($UserSource < $standard) {
                            $entry_array[$key]['UserColor'] = 1;
                            $standard_less[] = "<span style='color: #fe6305'>" . $value['Title'] . "</span>";
                        }
                    }

                }
            }

            // 整合评测总结
            $Summary_less = "";
            if (!empty($standard_less)) {
                $standard_less = implode(",", $standard_less);
                $Summary_less = "还需加油：".$standard_less."<br>";
            }
            $Summary_equal = "";
            if (!empty($standard_equal)) {
                $standard_equal = implode(",", $standard_equal);
                $Summary_equal = "能力达标：".$standard_equal."<br>";
            }
            $Summary_greater = "";
            if (!empty($standard_greater)) {
                $standard_greater = implode(",", $standard_greater);
                $Summary_greater = "表现优异：".$standard_greater."<br>";
            }
            $Summary_outside = "";
            if (!empty($standard_outside)) {
                $standard_outside = implode(",", $standard_outside);
                $Summary_outside = "低于基础销售岗位最低标准：".$standard_outside;
            }
            $Summary = $Summary_less . $Summary_equal . $Summary_greater . $Summary_outside;

            // 评测记录数据入库
            $Reports = json_encode($entry_array);
            $result = ReportService::insertReport($RankId, $EmployeeId, $OtherId, $UserContent, $UserContents, $UserContentx, $Type, $Summary, $Reports);

            // 自评勾选选项入库
            foreach ($option_array as $va) {
                ReportService::insertAnswer($result, $EmployeeId, $va['QuestionId'], $va['ChooseId'], $OnlineStatus);
            }

            // 更新employee中lastReportId
            ReportService::updateLastReportId($result, $EmployeeId);

            return ResponseWrapper::success();
        }

        /**
         * 如果当前提交为他评
         */
        if ($OnlineStatus == 2) {

            $ReportId = $request->input('ReportId');

            // 获取已完成的自评报告数据
            $UserReportInfo = ReportService::getUserReport($ReportId);

            // 他评等级处理
            foreach ($entry_array as $key => $value) {

                // 定义选项数组，仅针对当前循坏
                $option_val_array = array();

                // 问题列表、选项列表遍历
                foreach ($value['question_data'] as $vs) {
                    foreach ($vs['option_data'] as $vx) {
                        if ($vx['Status'] == 1) {
                            $vx['QuestionId'] = $vs['Id'];
                            $vx['ChooseId'] = $vx['Id'];
                            $option_array[] = $vx;
                            $option_val_array[] = $vx;
                        }
                    }
                }

                // 统计勾选数量
                $option_num = count($option_val_array);

                // 评分标准转数组
                $ScoreCriteria = $value['ScoreCriteria'];
                $ScoreArray = json_decode($ScoreCriteria, true);

                // 评测标准对应分值
                $ser_codes = config('services.ScoreCriteria');

                $arrivelLevel = explode('/', $value['ArrivelLevel']);

                // 当前词条标准分数
                $standard = $ser_codes[trim(end($arrivelLevel))];
                $entry_array[$key]['StandardSource'] = $standard;

                // 他评输入内容
                $OtherContent = $request->input('OtherContent');
                $OtherContents = $request->input('OtherContents');
                $OtherContentx = $request->input('OtherContentx');

                // 自评等级，分数
                $entry_array[$key]['UserRank'] = "";
                $entry_array[$key]['UserSource'] = "";
                $entry_array[$key]['UserColor'] = "";

                // 如果小于最低评分标准，则他评等级为：标准范围之外
                $LowSource = explode("-", $ScoreArray['F']);
                if ($option_num < $LowSource[0]) {
                    // UserColor：他评等级颜色
                    $entry_array[$key]['OtherRank'] = "!";
                    $entry_array[$key]['OtherSource'] = "!";
                    $entry_array[$key]['OtherColor'] = 4;
                    $standard_outside[] = "<span style='color: #fe6305'>" . $value['Title'] . "</span>";
                }

                // 计算他评等级
                foreach ($ScoreArray as $kz => $vz) {
                    $scope = explode("-", $vz);
                    if ($option_num >= $scope[0] && $option_num <= $scope[1]) {
                        // 他评等级，分数
                        $entry_array[$key]['OtherRank'] = $kz;
                        $OtherSource = $ser_codes[$kz];
                        $entry_array[$key]['OtherSource'] = $ser_codes[$kz];
                        // 自评等级和标准对比：1小于，2等于，3大于
                        if ($OtherSource > $standard) {
                            $entry_array[$key]['OtherColor'] = 3;
                            $standard_greater[] = "<span style='color: #fe6305'>" . $value['Title'] . "</span>";
                        }
                        if ($OtherSource == $standard) {
                            $entry_array[$key]['OtherColor'] = 2;
                            $standard_equal[] = "<span style='color: #fe6305'>" . $value['Title'] . "</span>";
                        }
                        if ($OtherSource < $standard) {
                            $entry_array[$key]['OtherColor'] = 1;
                            $standard_less[] = "<span style='color: #fe6305'>" . $value['Title'] . "</span>";
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
                foreach ($value['question_data'] as $kv => $vv) {
                    foreach ($vv['option_data'] as $kz => $vz) {
                        foreach ($UserAnswerArray as $km => $vm) {
                            if ($vz['Id'] == $vm->ChooseId) {
                                // 自评勾选，他评未勾选，Status = 2
                                if ($vz['Status'] == 0) {
                                    $entry_array[$key]['question_data'][$kv]['option_data'][$kz]['Status'] = 2;
                                }
                                // 自评、他评都勾选
                                if ($vz['Status'] == 1) {
                                    $entry_array[$key]['question_data'][$kv]['option_data'][$kz]['Status'] = 3;
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
                    if ($value['Id'] == $vs->Id && isset($vs->UserRank)) {
                        $entry_array[$key]['UserRank'] = $vs->UserRank;
                        $entry_array[$key]['UserSource'] = $vs->UserSource;
                        //$entry_array[$key]['UserColor'] = $vs->UserColor;
                        $entry_array[$key]['UserColor'] = 0;
                    }
                }
            }

            // 整合评测总结
            $Summary_less = "";
            if (!empty($standard_less)) {
                $standard_less = implode(",", $standard_less);
                $Summary_less = "还需加油：".$standard_less."<br>";
            }
            $Summary_equal = "";
            if (!empty($standard_equal)) {
                $standard_equal = implode(",", $standard_equal);
                $Summary_equal = "能力达标：".$standard_equal."<br>";
            }
            $Summary_greater = "";
            if (!empty($standard_greater)) {
                $standard_greater = implode(",", $standard_greater);
                $Summary_greater = "表现优异：".$standard_greater."<br>";
            }
            $Summary_outside = "";
            if (!empty($standard_outside)) {
                $standard_outside = implode(",", $standard_outside);
                $Summary_outside = "低于基础销售岗位最低标准：".$standard_outside;
            }
            $Summary = $Summary_less . $Summary_equal . $Summary_greater . $Summary_outside;

            // 评测记录数据更新
            $ReportInfo = json_encode($entry_array);
            ReportService::updateReport($ReportId, $OtherContent, $OtherContents, $OtherContentx, $Summary, $OtherReport, $ReportInfo);


            // 他评勾选选项入库
            foreach ($option_array as $va) {
                ReportService::insertAnswer($ReportId, $UserReportInfo->OtherId, $va['QuestionId'], $va['ChooseId'], $OnlineStatus);
            }

            // 更新employee中lastReportId
            ReportService::updateLastReportId($ReportId, $UserReportInfo->EmployeeId);

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

        foreach ($info as $key => $value) {
            unset($value->question_data);
        }

        // 重新赋值新数组
        $data['entry_array'] = $info;
        $data['OtherId'] = $result->OtherId;
        $data['RankId'] = $result->RankId;
        $data['UserContent'] = $result->UserContent;
        $data['UserContents'] = $result->UserContents;
        $data['UserContentx'] = $result->UserContentx;
        $data['OtherContent'] = $result->OtherContent;
        $data['OtherContents'] = $result->OtherContents;
        $data['OtherContentx'] = $result->OtherContentx;
        $data['Summary'] = $result->Summary;

        return ResponseWrapper::success($data);
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
        foreach ($info as $key => $value) {
            if ($value->Id == $EntryId) {
                $data[] = $value;
            }
        }

        return ResponseWrapper::success($data);
    }
}
