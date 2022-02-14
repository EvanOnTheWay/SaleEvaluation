<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;


/**
 * 业务服务:首页
 * @package App\Services
 */
class IndexService extends BaseService
{
    /**
     * 获取个人信息
     * @param int $id
     * @return array
     */
    public function getUserInfo(int $id)
    {
        //用户个人信息
        $res = DB::table('snets_emp_Employee')->select([
            'Id', 'Name', 'HeadImg', 'Department', 'Positions', 'Status','Station'
        ])
            ->where('Status', '<>', 2)
            ->where('Id', '=', $id)
            ->first();

        $response = [];

        if (!is_null($res)) {
            $response['Id'] = $res->Id;
            $response['Name'] = $res->Name;
            $response['HeadImg'] = $res->HeadImg;
            $response['Department'] = $res->Department;
            $response['Station'] = $res->Station;
            $response['Positions'] = $res->Positions;
            $response['Status'] = $res->Status;
        }

        return $response;
    }

    /**
     * 首页
     * @param int $id
     * @return array
     */
    public function getUser(int $id)
    {
        //用户个人信息
        $res = DB::table('snets_emp_Employee')->select([
            'Id', 'Name', 'HeadImg', 'Department', 'Positions', 'Status'
        ])
            ->where('Id', '=', $id)
            ->first();

        if (is_null($res)) {
            return [];
        }

        //首页P1~M3职级最新测评成绩及最近测评报告
        if (in_array($res->Positions, ['P1', 'P2', 'P3', 'M1', 'M2', 'M3'])) {
            //最新测评成绩
            $sql1 = " SELECT TOP 1
              r.Id
              ,r.Summary
              ,l.Title AS RankName
             FROM snets_tst_Report r
             LEFT JOIN snets_tst_Rank l ON r.RankId = l.Id
             WHERE r.EmployeeId = $id
             order by r.CreatedTime DESC";
            $nwe_report = DB::select($sql1);
            if (!empty($nwe_report)) {
                $data['new_evaluation_report'] = $nwe_report;
            } else {
                $data['new_evaluation_report'] = [];
            }
            //最近测评报告取3条
            $sql2 = " SELECT TOP 3
              r.Id
              ,r.RankId
              ,r.Type
              ,r.CreatedTime
              ,l.Title AS RankName
             FROM snets_tst_Report r
             LEFT JOIN snets_tst_Rank l ON r.RankId = l.Id
             WHERE r.EmployeeId = $id
             order by r.CreatedTime DESC";
            $recent_evaluation = DB::select($sql2);
            if (!empty($recent_evaluation)) {
                foreach ($recent_evaluation as $k => $v) {
                    $recent_evaluation[$k]->Id = $v->Id;
                    if ($v->Type == 2) {
                        $recent_evaluation[$k]->EvaluationType = '自我评估';
                    }
                    if ($v->Type == 1) {
                        $recent_evaluation[$k]->EvaluationType = '双方评估';
                    }
                    $recent_evaluation[$k]->Type = $v->Type;
                    $recent_evaluation[$k]->RankName = $v->RankName;
                    $time = date('Y-n-j', strtotime($v->CreatedTime));
                    $recent_evaluation[$k]->Month = explode('-', $time)[1] . '月' . explode('-', $time)[2] . '日';
                    $recent_evaluation[$k]->Year = explode('-', $time)[0]. '年';
                }
            }
            $data['new_recent_evaluation'] = $recent_evaluation;

        }
        //M1~M4下属人数
        if (in_array($res->Positions, ['M1', 'M2', 'M3', 'M4'])) {
            $num = DB::table('snets_emp_Office')
                ->where('LeaderId', '=', $id)
                ->count('EmployeeId');
            $data['Num'] = $num;
        }
        //TODO M4完成测评各级下属人数 M3自评列表
        if ($res->Positions == 'M4') {
            $sql = " SELECT TOP 3
                      r.Id
                      ,r.RankId
                      ,u.Name
                      ,u.Id AS EmployeeId
                      ,r.Type
                      ,r.CreatedTime
                      ,l.Title AS RankName
                     FROM snets_tst_Report r
                     LEFT JOIN snets_tst_Rank l ON r.RankId = l.Id
                     LEFT JOIN [snets_emp_Employee] u ON r.EmployeeId = u.Id
                     WHERE r.OtherId = $id
                     order by r.CreatedTime DESC ";
            $mutualList = DB::select($sql);
            if (!empty($mutualList)) {
                foreach ($mutualList as $k => $v) {
                    $time = date('Y-n-j', strtotime($v->CreatedTime));
                    $mutualList[$k]->Month = explode('-', $time)[1] . '月' . explode('-', $time)[2] . '日';
                    $mutualList[$k]->Year = explode('-', $time)[0] . '年';
                }
            } else {
                $data['new_recent_evaluation'] = '';
            }
            $data['new_recent_evaluation'] = $mutualList;
            //P1~M3完成自评人数
            //获取P1~M3Id 去重
            $sql1 = "SELECT 
                        employee.Positions,COUNT(*) AS Number 
                        FROM (
                        SELECT 
                        report.EmployeeId,ran.Title 
                        FROM snets_tst_Report AS report 
                        LEFT JOIN snets_tst_Rank AS ran ON ran.Id = report.RankId 
                        AND report.Status = 1
                        GROUP BY report.EmployeeId,ran.Title
                        ) AS t 
                        LEFT JOIN snets_emp_Employee AS employee ON t.EmployeeId = employee.Id
                         AND t.Title = employee.Positions 
                        WHERE employee.Id IS NOT NULL AND employee.Positions IS NOT NULL
                        GROUP BY employee.Positions";
            $employee = DB::select($sql1);
            $arr[0] = ['Number' => 0, 'Positions' => 'P1'];
            $arr[1] = ['Number' => 0, 'Positions' => 'P2'];
            $arr[2] = ['Number' => 0, 'Positions' => 'P3'];
            $arr[3] = ['Number' => 0, 'Positions' => 'M1'];
            $arr[4] = ['Number' => 0, 'Positions' => 'M2'];
            $arr[5] = ['Number' => 0, 'Positions' => 'M3'];
            foreach ($arr as $k => $value) {
                foreach ($employee as $val) {
                    if ($value['Positions'] == $val->Positions) {
                        $arr[$k]['Number'] = $val->Number;
                    }
                }
            }
            $data['employee'] = $arr;
        }
        return $data;
    }


    /**
     * 我的下属
     * @param int $id
     * @return array
     */
    public function getBranch(int $id)
    {
        $sql = "SELECT 
                 employee.Name
                 ,employee.Positions
                 ,employee.Id
                 ,employee.HeadImg
                 ,employee.Department
                 ,ra.Title AS RankName
                 ,r.Summary
                 ,r.CreatedTime
                FROM 
                (
                    SELECT
                        DISTINCT 
                        e.Name
                        ,e.HeadImg
                        ,e.Positions
                        ,e.Department
                        ,o.LeaderId
                        ,e.Id
                        ,e.LastReportId
                        ,o.EmployeeId
                    FROM snets_emp_Employee e
                    LEFT JOIN snets_emp_Office o ON o.EmployeeId = e.Id
                    WHERE o.LeaderId = $id AND e.Status <> 2
                ) AS employee
                LEFT JOIN snets_tst_Report r ON employee.LastReportId = r.Id
                LEFT JOIN snets_tst_Rank ra ON r.RankId = ra.Id";
        $report = DB::select($sql);
        return $report;
    }

}
