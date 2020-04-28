<?php

namespace App\Services;

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
                'Id', 'Title', 'Intro', 'ScoreCriteria', 'ArrivelLevel'
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

                $vs->question_data = $question_data;

                // 对应选项
                foreach ($question_data as $kx => $vx) {
                    $option_data = DB::table('snets_tst_OptionItem')->select([
                        'Id', 'Title'
                    ])
                        ->where('QuestionId', '=', $vx->Id)
                        ->get()
                        ->toArray();

                    $vx->option_data = $option_data;
                }
            }
        }

        return $ability_data;
    }
}
