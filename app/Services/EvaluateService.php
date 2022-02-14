<?php

namespace App\Services;


use Illuminate\Support\Facades\DB;


/**
 * 业务服务:测评
 * @package App\Services
 */
class EvaluateService extends BaseService
{
    /**
     * 评测等级列表
     * @return array
     */
    public function list()
    {
        $eval = DB::table('snets_tst_Rank')->select([
            'Id as RankId', 'Title as RankName'
        ])
            ->where('Status', '=', 1)
            ->get()
            ->toArray();

        return $eval;
    }

    /**
     * 互评邀请列表
     * @param int $id
     * @return array
     */
    public function mutual(int $id)
    {
        $sql = " SELECT 
                      r.Id
                      ,r.RankId
                      ,u.Name
                      ,u.Id AS EmployeeId
                      ,r.Type
                      ,r.Status
                      ,r.CreatedTime
                      ,l.Title AS RankName
                     FROM snets_tst_Report r
                     LEFT JOIN snets_tst_Rank l ON r.RankId = l.Id
                     LEFT JOIN [snets_emp_Employee] u ON r.EmployeeId = u.Id
                     WHERE r.OtherId = $id
                     AND r.Status <> 0
                     AND r.TYPE = 1
                     order by r.CreatedTime DESC ";
        $mutualList = DB::select($sql);
        $isEvaluated = [];
        $noEvaluated = [];
        if (!empty($mutualList)) {
            foreach ($mutualList as $v) {
                //上级已评
                if ($v->Status == 1) {
                    $row['Id'] = $v->Id;
                    $row['RankId'] = $v->RankId;
                    $row['EmployeeId'] = $v->EmployeeId;
                    $row['Type'] = $v->Type;
                    $row['RankName'] = $v->RankName;
                    $row['Name'] = $v->Name;
                    $time = date('Y-n-j', strtotime($v->CreatedTime));
                    $date = explode('-', $time);
                    $row['Month'] = $date[1] . '月' . $date[2] . '日';
                    $row['Year'] = $date[0] . '年';
                    $isEvaluated[] = $row;
                }
                //上级未评
                if ($v->Status == 2) {
                    $rows['Id'] = $v->Id;
                    $rows['RankId'] = $v->RankId;
                    $rows['EmployeeId'] = $v->EmployeeId;
                    $rows['Type'] = $v->Type;
                    $rows['RankName'] = $v->RankName;
                    $rows['Name'] = $v->Name;
                    $time = date('Y-n-j', strtotime($v->CreatedTime));
                    $date = explode('-', $time);
                    $rows['Month'] = $date[1] . '月' . $date[2] . '日';
                    $rows['Year'] = $date[0] . '年';
                    $noEvaluated[] = $rows;
                }
            }
        }
        $data['noEvaluatedList'] = $noEvaluated;
        $data['isEvaluatedList'] = $isEvaluated;
        return $data;
    }


    /**
     * 所有测评列表
     * @param int $id
     * @param string $positions
     * @return mixed
     */
    public function evalList(int $id, string $positions)
    {
        //P1~P3的所有测评
        if (in_array($positions, ['P1', 'P2', 'P3'])) {
            $sql = " SELECT
                      r.Id
                      ,r.Type
                      ,r.RankId
                      ,r.EmployeeId
                      ,l.Title AS RankName
                      ,r.CreatedTime
                     FROM snets_tst_Report r
                     LEFT JOIN snets_tst_Rank l ON r.RankId = l.Id
                     WHERE r.EmployeeId = $id
                     order by r.CreatedTime DESC";
            $report = DB::select($sql);
            $data['selfEvalList'] = [];
            if (!empty($report)) {
                foreach ($report as $k => $v) {
                    if ($v->Type == 2) {
                        $report[$k]->EvaluationType = '自我评估';
                    }
                    if ($v->Type == 1) {
                        $report[$k]->EvaluationType = '双方评估';
                    }
                    $time = date('Y-n-j', strtotime($v->CreatedTime));
                    $date = explode('-', $time);
                    $report[$k]->Month = $date[1] . '月' . $date[2] . '日';
                    $report[$k]->Year = $date[0] . '年';
                }
            }
            $data['selfEvalList'] = $report;
        }
        //M1~M3 分为自我评估和下属评估
        if (in_array($positions, ['M1', 'M2', 'M3'])) {
            //自我评估
            $sql = " SELECT
                      r.Id
                      ,r.Type
                      ,r.RankId
                      ,r.EmployeeId
                      ,l.Title AS RankName
                      ,r.CreatedTime
                     FROM snets_tst_Report r
                     LEFT JOIN snets_tst_Rank l ON r.RankId = l.Id
                     WHERE r.EmployeeId = $id
                     order by r.CreatedTime DESC";
            $report = DB::select($sql);
            $data['selfEvalList'] = [];
            if (!empty($report)) {
                foreach ($report as $k => $v) {
                    if ($v->Type == 2) {
                        $report[$k]->EvaluationType = '自我评估';
                    }
                    if ($v->Type == 1) {
                        $report[$k]->EvaluationType = '双方评估';
                    }
                    $time = date('Y-n-j', strtotime($v->CreatedTime));
                    $date = explode('-', $time);
                    $report[$k]->Month = $date[1] . '月' . $date[2] . '日';
                    $report[$k]->Year = $date[0] . '年';
                }
            }
            $data['selfEvalList'] = $report;
            //下属评估
            $empId = DB::table('snets_emp_Office')
                ->select(['EmployeeId'])
                ->where('LeaderId', '=', $id)
                ->get()
                ->toArray();
            foreach ($empId as $v) {
                $employee_id[] = $v->EmployeeId;
            }
            $employeeId = implode(',', $employee_id);
            $sql1 = " SELECT
                      r.Id
                      ,r.Type
                      ,r.RankId
                      ,u.Name
                      ,r.EmployeeId
                      ,l.Title AS RankName
                      ,r.CreatedTime
                     FROM snets_tst_Report r
                     LEFT JOIN snets_tst_Rank l ON r.RankId = l.Id
                     LEFT JOIN [snets_emp_Employee] u ON r.EmployeeId = u.Id
                     WHERE r.EmployeeId IN ($employeeId)
                     AND r.Type = 1
                     AND r.Status = 1
                     order by r.CreatedTime DESC";
            $res = DB::select($sql1);
            $data['branchEvalList'] = [];
            if (!empty($res)) {
                foreach ($res as $key => $val) {
                    $time = date('Y-n-j', strtotime($val->CreatedTime));
                    $date = explode('-', $time);
                    $res[$key]->Month = $date[1] . '月' . $date[2] . '日';
                    $res[$key]->Year = $date[0] . '年';
                }
            }
            $data['branchEvalList'] = $res;
        }
        //M4下属评估
        if ($positions == 'M4') {
            $empId = DB::table('snets_emp_Office')
                ->select(['EmployeeId'])
                ->where('LeaderId', '=', $id)
                ->get()
                ->toArray();
            foreach ($empId as $v) {
                $employee_id[] = $v->EmployeeId;
            }
            $employeeId = implode(',', $employee_id);
            $sql = " SELECT
                      r.Id
                      ,u.Name
                      ,u.Id AS EmployeeId
                      ,r.Type
                      ,r.CreatedTime
                      ,l.Title AS RankName
                     FROM snets_tst_Report r
                     LEFT JOIN snets_tst_Rank l ON r.RankId = l.Id
                     LEFT JOIN [snets_emp_Employee] u ON r.EmployeeId = u.Id
                     WHERE r.EmployeeId IN ($employeeId)
                     AND r.Type = 1
                     AND r.Status = 1
                     order by r.CreatedTime DESC";
            $data['branchEvalList'] = [];
            $report = DB::select($sql);
            if (!empty($report)) {
                foreach ($report as $key => $value) {
                    $time = date('Y-n-j', strtotime($value->CreatedTime));
                    $date = explode('-', $time);
                    $report[$key]->Month = $date[1] . '月' . $date[2] . '日';
                    $report[$key]->Year = $date[0] . '年';
                }
            }
            $data['branchEvalList'] = $report;
        }
        return $data;
    }


    /**
     * 是否需要他评
     * @param int $id
     * @return array
     */
    public function otherEvaluate(int $id)
    {
        $sql = " SELECT
                      o.LeaderId
                      ,e.Name
                     FROM snets_emp_Office o
                     LEFT JOIN snets_emp_Employee e ON o.LeaderId = e.Id
                     WHERE o.EmployeeId = $id";
        $leaderList = DB::select($sql);
        $data['leaderList'] = [];
        foreach ($leaderList as $k => $v) {
            $leaderList[$k]->LeaderId = $v->LeaderId;
            $leaderList[$k]->Name = $v->Name;
        }
        $data['leaderList'] = $leaderList;
        return $data;
    }

    /**
     * 发送邀请
     * @param int $id
     * @param int $reportId
     * @param int $otherId
     * @return bool
     */
    public function offer(int $id, int $reportId, int $otherId)
    {
        $data['EmployeeId'] = $id;
        $data['OtherId'] = $otherId;
        $data['Type'] = 1;
        $data['Status'] = 2;
        $res = DB::table('snets_tst_Report')->where('id', '=', $reportId)->update($data);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 能力模型
     * @param int $rankId
     * @return array
     */
    public function preview(int $rankId)
    {

        $res = DB::table('snets_emp_Ability')
            ->select([
                'Id', 'Pid', 'Title', 'EntryLevelDes'
            ])
            ->where('RankId', '=', $rankId)
            ->where('Status', '=', 1)
            ->get()
            ->toArray();

        foreach ($res as $val) {
            $row['Id'] = $val->Id;
            $row['Pid'] = $val->Pid;
            $row['Title'] = $val->Title;
            if ($val->Pid != 0) {
                $row['EntryLevelDes'] = $val->EntryLevelDes;
            }

            $array[] = $row;
        }

        $treeData = $this->getTree($array, 'Id', 'Pid');
        return $treeData;
    }

    /**
     * @description
     * @param array $arr 二维数组
     * @param string $pk 主键id
     * @param string $upid 表示父级id的字段
     * @param string $child 子目录的键
     * @return array
     */
    protected function getTree(array $arr, string $pk, string $upid, string $child = 'child')
    {
        $items = array();
        foreach ($arr as $val) {
            $items[$val[$pk]] = $val;
        }
        $tree = array();
        foreach ($items as $k => $val) {
            if (isset($items[$val[$upid]])) {
                $items[$val[$upid]][$child][] =& $items[$k];
            } else {
                $tree[] = &$items[$k];
            }
        }
        return $tree;
    }

}
