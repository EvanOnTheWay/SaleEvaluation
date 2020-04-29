<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * 业务服务: 评测报告
 */
class ReportService extends BaseService
{
    /**
     * 获取评测信息(对应等级下能力维度、词条、题库)
     *
     * @param $RankId
     * @return array
     */
    public static function getRankInfo($RankId)
    {
        $rank_id = intval($RankId);

        // 能力维度
        $ability_data = DB::table('snets_emp_Ability')->select([
            'Id', 'Title', 'Intro'
        ])
            ->where('RankId', '=', $rank_id)
            ->where('Depth', '=', '1')
            ->where('Pid', '=', '0')
            ->get()
            ->toArray();

        // 对应词条
        foreach ($ability_data as $key => $value) {
            $entry_data = DB::table('snets_emp_Ability')->select([
                'Id', 'Title', 'Pid', 'Intro', 'ScoreCriteria', 'ArrivelLevel'
            ])
                ->where('Depth', '=', '2')
                ->where('Pid', '=', $value->Id)
                ->get()
                ->toArray();
            $ability_data[$key]->entry_data = $entry_data;

            // 对应问题
            foreach ($entry_data as $ks => $vs) {
                $question_data = DB::table('snets_tst_Question')->select([
                    'Id', 'Title'
                ])
                    ->where('AbilityId', '=', $vs->Id)
                    ->get()
                    ->toArray();
                $vs->PTitle = $value->Title;
                $vs->question_data = $question_data;

                // 对应选项
                foreach ($question_data as $kx => $vx) {
                    $option_data = DB::table('snets_tst_OptionItem')->select([
                        'Id', 'Title'
                    ])
                        ->where('QuestionId', '=', $vx->Id)
                        ->get()
                        ->toArray();

                    // 这里status 随机给0，1 测试用
                    foreach ($option_data as $kz => $vz) {
                        if ($kz%2 == 0){
                            $vz->status = 0;
                        }else{
                            $vz->status = 1;
                        }
                    }

                    $vx->option_data = $option_data;
                }
            }
        }

        return $ability_data;
    }

    /**
     * 根据Id获取能力维度
     *
     * @param $AbilityId
     * @return array
     */
    public static function getAbilityInfo($AbilityId)
    {
        $ability_data = DB::table('snets_emp_Ability')->select([
            'Id', 'Title'
        ])
            ->where('id', '=', $AbilityId)
            ->get()
            ->toArray();

        return $ability_data;
    }

    /**
     * 自评信息入库
     *
     * @param $RankId
     * @param $EmployeeId
     * @param $OtherId
     * @param $UserContent
     * @param $UserContents
     * @param $UserContentx
     * @param $Type
     * @param $Summary
     * @param $UserReport
     * @return bool
     */
    public static function insertReport($RankId, $EmployeeId, $OtherId, $UserContent, $UserContents, $UserContentx, $Type, $Summary, $UserReport)
    {
        $result = DB::table('snets_tst_Report')->insert([
            'RankId' => $RankId,
            'EmployeeId' => $EmployeeId,
            'OtherId' => $OtherId,
            'UserContent' => $UserContent,
            'UserContents' => $UserContents,
            'UserContentx' => $UserContentx,
            'Type' => $Type,
            'UserSummary' => $Summary,
            'UserReport' => $UserReport,
            'ReportInfo' => $UserReport,
            'Status' => 0,
            'OnlineStatus' => 1,
            'SeltTime' => Carbon::now(),
            'CreatedTime' => Carbon::now(),
        ]);
        $id = DB::getPdo()->lastInsertId();

        return $id;
    }

    /**
     * 他评信息入库（update）
     *
     * @param $EmployeeId
     * @param $OtherId
     * @param $OtherContent
     * @param $OtherContents
     * @param $OtherContentx
     * @param $Summary
     * @param $Reports
     * @return void
     */
    public static function updateReport($EmployeeId, $OtherId, $OtherContent, $OtherContents, $OtherContentx, $Summary, $Reports)
    {

    }

    /**
     * 勾选选项循环入库
     *
     * @param $ReportId
     * @param $EmployeeId
     * @param $QuestionId
     * @param $ChooseId
     * @param $OnlineStatus
     * @return bool
     */
    public static function insertAnswer($ReportId, $EmployeeId, $QuestionId, $ChooseId, $OnlineStatus)
    {
        $result = DB::table('snets_tst_Answer')->insert([
            'ReportId' => $ReportId,
            'EmployeeId' => $EmployeeId,
            'QuestionId' => $QuestionId,
            'ChooseId' => $ChooseId,
            'status' => $OnlineStatus,
            'CreatedTime' => Carbon::now(),
        ]);

        return $result;
    }
}
