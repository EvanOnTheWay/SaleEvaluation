<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EvaluateController extends Controller
{
    /**
     * 测评列表
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function evaluateList(Request $request)
    {
        $username = session('username');
        $name = $request->input('title');
        $query = DB::table('snets_tst_Rank')->select([
            'Id','Title', 'Status', 'CreatedTime'
        ])
            ->where('Status', '=', 1);
        if ($name != '') {
            $query->where('Title', 'LIKE', '%' . $name . '%');
        }
        $data = $query->get()->toArray();
        foreach ($data as $key => $value) {
            $data[$key]->Status = '已发布';
        }
        return view(
            'evaluateList',
            [
                'data' => $data,
                'title' => '测评管理> 测评列表',
                'username' => $username,
                'url' => 'evaluateList'
            ]);
    }

    /**
     * 删除测评
     * @param int $id
     * @return false|string
     */
    public function deleteEvaluate(int $id)
    {
        //开启事务
        DB::beginTransaction();

        try {
            //删除测评等级
            DB::table('snets_tst_Rank')->where('Id', '=', $id)->update(['status' => 0]);
            //删除词条表中词条
            DB::table('snets_emp_Ability')->where('RankId','=',$id)->update(['status' => 0]);
            //查出所选中词条的问题的id
            $questionId = DB::table('snets_emp_Ability')
                ->select(['Id'])
                ->where('RankId','=',$id)
                ->where('Pid','<>',0)
                ->get()
                ->toArray();

            foreach ($questionId as $value){
                $qId[] = $value->Id;
            }
            //删除问题
            DB::table('snets_tst_Question')->whereIn('AbilityId',$qId)->update(['status' => 0]);
            //查出选项id
            $optionId = DB::table('snets_tst_Question')
                ->select(['Id'])
                ->whereIn('AbilityId',$qId)
                ->get()
                ->toArray();
            foreach ($optionId as $val){
                $oId[] = $val->Id;
            }
            //删除选项
            DB::table('snets_tst_OptionItem')->whereIn('QuestionId',$oId)->update(['status' => 0]);
            //提交事务
            DB::commit();
            $data['uses'] = 1;
            return json_encode($data);
        } catch (\Exception $e) {
            //事务回滚
            DB::rollBack();
            $data['uses'] = 0;
            return json_encode($data);
        }

    }

    /**
     * 新增测评等级
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addEvaluate()
    {
        $username = session('username');
        return view(
            'addEvaluate',
            [
                'title' => '测评管理> 新增测评等级',
                'username' => $username,
                'url' => 'evaluateList'
            ]);
    }

    /**
     * 添加测评等级处理
     * @param Request $request
     * @return false|string
     */
    public function addEvaluateAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
        ]);
        if ($validator->fails()) {
            $data['uses'] = -1;
            return json_encode($data);
        }
        $data['Title'] = $request->input('title');
        $data['Status'] = 1;
        $data['CreatedTime'] = Carbon::now();
        $res = DB::table('snets_tst_Rank')->insert($data);
        if ($res){
            $data['uses'] = 1;
            return json_encode($data);
        }else{
            $data['uses'] = -2;
            return json_encode($data);
        }
    }

    /**
     * 编辑测评等级
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editEvaluate(Request $request)
    {
        $id = $request->input('id');
        $username = session('username');
        $data = DB::table('snets_tst_Rank')->select(['Title','Id'])->where('id','=',$id)->first();
        return view(
            'editEvaluate',
            [
                'data' => $data,
                'title' => '测评管理> 编辑测评等级',
                'username' => $username,
                'url' => 'evaluateList'
            ]);
    }

    /**
     * 编辑测评等级处理
     * @param Request $request
     * @return false|string
     */
    public function editEvaluateAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
        ]);
        if ($validator->fails()) {
            $data['uses'] = -1;
            return json_encode($data);
        }
        $id = $request->input('rankId');
        $data['Title'] = $request->input('title');
        $data['ModifiedTime'] = Carbon::now();
        $res = DB::table('snets_tst_Rank')->where('Id','=',$id)->update($data);
        if ($res){
            $data['uses'] = 1;
            return json_encode($data);
        }else{
            $data['uses'] = -2;
            return json_encode($data);
        }
    }

    /**
     * 能力维度
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ability(Request $request)
    {
        $id = $request->input('id');
        $username = session('username');
        $sql = "SELECT
                      a.Id
                      ,a.Title
                      ,a.Status
                      ,l.Title AS RankName
                      ,a.CreatedTime
                     FROM snets_emp_Ability a
                     LEFT JOIN snets_tst_Rank l ON a.RankId = l.Id
                     WHERE a.RankId = $id
                     AND a.Pid = 0
                     AND a.Status = 1";
        $data = DB::select($sql);
        return view(
            'ability',
            [
                'rankId' => $id,
                'data' => $data,
                'title' => '测评管理> 编辑能力词条',
                'username' => $username,
                'url' => 'evaluateList'
            ]);
    }

    /**
     * 新增能力维度
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addAbility(Request $request)
    {
        $rankId = $request->input('rankId');
        $username = session('username');
        return view(
            'addAbility',
            [
                'rankId' => $rankId,
                'title' => '测评管理> 新增能力维度',
                'username' => $username,
                'url' => 'evaluateList'
            ]);
    }


    /**
     * 新增能力维度处理
     * @param Request $request
     * @return false|string
     */
    public function addAbilityAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
        ]);
        if ($validator->fails()) {
            $data['uses'] = -1;
            return json_encode($data);
        }
        $id = $request->input('rankId');
        $data['Title'] = $request->input('title');
        $data['Pid'] = 0;
        $data['Depth'] = 1;
        $data['CreatedTime'] = Carbon::now();
        $data['RankId'] = $id;
        $res = DB::table('snets_emp_Ability')->insert($data);
        if ($res){
            $data['uses'] = 1;
            return json_encode($data);
        }else{
            $data['uses'] = -2;
            return json_encode($data);
        }
    }


    /**
     * 编辑能力维度
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editAbility(Request $request)
    {
        $id = $request->input('id');
        $username = session('username');
        $data = DB::table('snets_emp_Ability')->select(['Title','Id'])->where('id','=',$id)->first();
        return view(
            'editAbility',
            [
                'data' => $data,
                'title' => '测评管理> 编辑能力维度',
                'username' => $username,
                'url' => 'evaluateList'
            ]);
    }

    /**
     * 编辑能力维度处理
     * @param Request $request
     * @return false|string
     */
    public function editAbilityAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
        ]);
        if ($validator->fails()) {
            $data['uses'] = -1;
            return json_encode($data);
        }
        $id = $request->input('titleId');
        $data['Title'] = $request->input('title');
        $data['CreatedTime'] = Carbon::now();
        $res = DB::table('snets_emp_Ability')->where('Id','=',$id)->update($data);
        if ($res){
            $data['uses'] = 1;
            return json_encode($data);
        }else{
            $data['uses'] = -2;
            return json_encode($data);
        }
    }

    /**
     * 删除能力维度
     * @param int $id
     * @return false|string
     */
    public function deleteAbility(int $id)
    {
        //开启事务
        DB::beginTransaction();
        try {
            //删除能力维度
            DB::table('snets_emp_Ability')->where('Id','=',$id)->update(['status' => 0]);
            //删除能力词条
            DB::table('snets_emp_Ability')->where('Pid','=',$id)->update(['status' => 0]);
            //查出所选中词条的问题的id
            $questionId = DB::table('snets_emp_Ability')
                ->select(['Id'])
                ->where('Pid','=',$id)
                ->get()
                ->toArray();

            foreach ($questionId as $value){
                $qId[] = $value->Id;
            }
            //删除问题
            DB::table('snets_tst_Question')->whereIn('AbilityId',$qId)->update(['status' => 0]);
            //查出选项id
            $optionId = DB::table('snets_tst_Question')
                ->select(['Id'])
                ->whereIn('AbilityId',$qId)
                ->get()
                ->toArray();
            foreach ($optionId as $val){
                $oId[] = $val->Id;
            }
            //删除选项
            DB::table('snets_tst_OptionItem')->whereIn('QuestionId',$oId)->update(['status' => 0]);
            //提交事务
            DB::commit();
            $data['uses'] = 1;
            return json_encode($data);
        } catch (\Exception $e) {
            //事务回滚
            DB::rollBack();
            $data['uses'] = 0;
            return json_encode($data);
        }
    }

    /**
     * 能力词条
     */
    public function entry(Request $request)
    {
        $id = $request->input('id');
        $username = session('username');
        $ability = $request->input('title');
        $rankId = DB::table('snets_emp_Ability')
            ->select(['RankId'])
            ->where('Id','=',$id)
            ->where('Status','=',1)
            ->first();
        $data = DB::table('snets_emp_Ability')
            ->select(['Id','Title','CreatedTime','Status'])
            ->where('Pid','=',$id)
            ->where('Status','=',1)
            ->get()
            ->toArray();
        return view(
            'entry',
            [
                'pId' => $id,
                'rankId' => $rankId,
                'ability' => $ability,
                'data' => $data,
                'title' => '测评管理> 能力词条',
                'username' => $username,
                'url' => 'evaluateList'
            ]);
    }

    /**
     * 新增能力词条
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addEntry(Request $request)
    {
        $pId = $request->input('pId');
        $rankId = $request->input('rankId');
        $username = session('username');
        return view(
            'addEntry',
            [
                'pId' => $pId,
                'rankId' => $rankId,
                'title' => '测评管理> 新增能力词条',
                'username' => $username,
                'url' => 'evaluateList'
            ]);

    }

    /**
     * 新增能力词条处理
     * @param Request $request
     * @return false|string
     */
    public function addEntryAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pId' => 'required',
            'rankId' => 'required',
            'title' => 'required',
            'score' => 'required',
            'level' => 'required',
            'intro' => 'required',
            'entryDes' => 'required',
        ]);
        if ($validator->fails()) {
            $data['uses'] = -1;
            return json_encode($data);
        }
        $data['RankId'] = $request->input('rankId');
        $data['Pid'] = $request->input('pId');
        $data['Title'] = $request->input('title');
        $data['Depth'] = 2;
        $data['Intro'] = $request->input('intro');
        $data['EntryLevelDes'] = $request->input('entryDes');
        $data['ScoreCriteria'] = $request->input('score');
        $data['ArrivelLevel'] = $request->input('level');
        $data['Status'] = 1;
        $data['CreatedTime'] = Carbon::now();
        $res = DB::table('snets_emp_Ability')->insert($data);
        if ($res){
            $data['uses'] = 1;
            return json_encode($data);
        }else{
            $data['uses'] = -2;
            return json_encode($data);
        }

    }


    /**
     * 编辑能力词条
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editEntry(Request $request)
    {
        $id = $request->input('id');
        $data = DB::table('snets_emp_Ability')
            ->select([
                'Title','Id','Intro','EntryLevelDes','ScoreCriteria','ArrivelLevel'
            ])
            ->where('id','=',$id)
            ->first();
        $username = session('username');
        return view(
            'editEntry',
            [
                'data' => $data,
                'title' => '测评管理> 编辑能力词条',
                'username' => $username,
                'url' => 'evaluateList'
            ]);

    }

    /**
     * 编辑能力词条处理
     * @param Request $request
     * @return false|string
     */
    public function editEntryAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titleId' => 'required',
            'title' => 'required',
            'score' => 'required',
            'level' => 'required',
            'intro' => 'required',
            'entryDes' => 'required',
        ]);
        if ($validator->fails()) {
            $data['uses'] = -1;
            return json_encode($data);
        }
        $id = $request->input('titleId');
        $data['Title'] = $request->input('title');
        $data['Intro'] = $request->input('intro');
        $data['EntryLevelDes'] = $request->input('entryDes');
        $data['ScoreCriteria'] = $request->input('score');
        $data['ArrivelLevel'] = $request->input('level');
        $data['ModifiedTime'] = Carbon::now();
        $res = DB::table('snets_emp_Ability')->where('Id','=',$id)->update($data);
        if ($res){
            $data['uses'] = 1;
            return json_encode($data);
        }else{
            $data['uses'] = -2;
            return json_encode($data);
        }

    }

    /**
     * 删除能力词条
     * @param int $id
     * @return false|string
     */
    public function deleteEntry(int $id)
    {
        //开启事务
        DB::beginTransaction();
        try {
            //删除能力词条
            DB::table('snets_emp_Ability')->where('Id','=',$id)->update(['status' => 0]);
            //删除问题
            DB::table('snets_tst_Question')->where('AbilityId','=',$id)->update(['status' => 0]);
            //查出选项id
            $optionId = DB::table('snets_tst_Question')
                ->select(['Id'])
                ->where('AbilityId','=',$id)
                ->get()
                ->toArray();
            foreach ($optionId as $val){
                $oId[] = $val->Id;
            }
            //删除选项
            DB::table('snets_tst_OptionItem')->whereIn('QuestionId',$oId)->update(['status' => 0]);
            //提交事务
            DB::commit();
            $data['uses'] = 1;
            return json_encode($data);
        } catch (\Exception $e) {
            //事务回滚
            DB::rollBack();
            $data['uses'] = 0;
            return json_encode($data);
        }
    }

    /**
     * 问题
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function question(Request $request)
    {
        $id = $request->input('id');
        $title = $request->input('title');
        $data = DB::table('snets_tst_Question')
            ->select([
                'Title','Id','CreatedTime','Status'
            ])
            ->where('AbilityId','=',$id)
            ->where('Status','=',1)
            ->get()
            ->toArray();
        $username = session('username');
        return view(
            'question',
            [
                'abilityId' => $id,
                'titles' => $title,
                'data' => $data,
                'title' => '测评管理> 问题',
                'username' => $username,
                'url' => 'evaluateList'
            ]);
    }

    /**
     * 新增问题
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addQuestion(Request $request)
    {
        $abilityId = $request->input('abilityId');
        $username = session('username');
        return view(
            'addQuestion',
            [
                'abilityId' => $abilityId,
                'title' => '测评管理> 新增问题',
                'username' => $username,
                'url' => 'evaluateList'
            ]);
    }

    /**
     * 新增问题处理
     * @param Request $request
     * @return false|string
     */
    public function addQuestionAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'abilityId' => 'required',
            'title' => 'required',
        ]);
        if ($validator->fails()) {
            $data['uses'] = -1;
            return json_encode($data);
        }
        $data['abilityId'] = $request->input('abilityId');
        $data['Title'] = $request->input('title');
        $data['Status'] = 1;
        $data['CreatedTime'] = Carbon::now();
        $res = DB::table('snets_tst_Question')->insert($data);
        if ($res){
            $data['uses'] = 1;
            return json_encode($data);
        }else{
            $data['uses'] = -2;
            return json_encode($data);
        }

    }

    /**
     * 编辑问题
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editQuestion(Request $request)
    {
        $id = $request->input('id');
        $data = DB::table('snets_tst_Question')
            ->select([
                'Title','Id'
            ])
            ->where('id','=',$id)
            ->first();
        $username = session('username');
        return view(
            'editQuestion',
            [
                'data' => $data,
                'title' => '测评管理> 编辑问题',
                'username' => $username,
                'url' => 'evaluateList'
            ]);
    }

    /**
     * 编辑问题处理
     * @param Request $request
     * @return false|string
     */
    public function editQuestionAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titleId' => 'required',
            'title' => 'required',
        ]);
        if ($validator->fails()) {
            $data['uses'] = -1;
            return json_encode($data);
        }
        $id = $request->input('titleId');
        $data['Title'] = $request->input('title');
        $data['ModifiedTime'] = Carbon::now();
        $res = DB::table('snets_tst_Question')->where('Id','=',$id)->update($data);
        if ($res){
            $data['uses'] = 1;
            return json_encode($data);
        }else{
            $data['uses'] = -2;
            return json_encode($data);
        }
    }

    /**
     * 删除问题
     * @param int $id
     * @return false|string
     */
    public function deleteQuestion(int $id)
    {
        //开启事务
        DB::beginTransaction();
        try {
            //删除问题
            DB::table('snets_tst_Question')->where('Id','=',$id)->update(['status' => 0]);
            //删除选项
            DB::table('snets_tst_OptionItem')->where('QuestionId','=',$id)->update(['status' => 0]);
            //提交事务
            DB::commit();
            $data['uses'] = 1;
            return json_encode($data);
        } catch (\Exception $e) {
            //事务回滚
            DB::rollBack();
            $data['uses'] = 0;
            return json_encode($data);
        }
    }


    /**
     * 选项
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function option(Request $request)
    {
        $questionId = $request->input('id');
        $title = $request->input('title');
        $data = DB::table('snets_tst_OptionItem')
            ->select([
                'Title','Id','CreatedTime','Status'
            ])
            ->where('QuestionId','=',$questionId)
            ->where('Status','=',1)
            ->get()
            ->toArray();
        $username = session('username');
        return view(
            'option',
            [
                'questionId' => $questionId,
                'titles' => $title,
                'data' => $data,
                'title' => '测评管理> 问题',
                'username' => $username,
                'url' => 'evaluateList'
            ]);
    }


    /**
     * 新增选项
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addOption(Request $request)
    {
        $questionId = $request->input('questionId');
        $username = session('username');
        return view(
            'addOption',
            [
                'questionId' => $questionId,
                'title' => '测评管理> 新增能力描述',
                'username' => $username,
                'url' => 'evaluateList'
            ]);
    }

    /**
     * 新增选项处理
     * @param Request $request
     * @return false|string
     */
    public function addOptionAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'questionId' => 'required',
            'title' => 'required',
        ]);
        if ($validator->fails()) {
            $data['uses'] = -1;
            return json_encode($data);
        }
        $data['questionId'] = $request->input('questionId');
        $data['Title'] = $request->input('title');
        $data['Status'] = 1;
        $data['CreatedTime'] = Carbon::now();
        $res = DB::table('snets_tst_OptionItem')->insert($data);
        if ($res){
            $data['uses'] = 1;
            return json_encode($data);
        }else{
            $data['uses'] = -2;
            return json_encode($data);
        }
    }

    /**
     * 编辑选项
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editOption(Request $request)
    {
        $id = $request->input('id');
        $data = DB::table('snets_tst_OptionItem')
            ->select([
                'Title','Id'
            ])
            ->where('id','=',$id)
            ->first();
        $username = session('username');
        return view(
            'editOption',
            [
                'data' => $data,
                'title' => '测评管理> 编辑能力描述',
                'username' => $username,
                'url' => 'evaluateList'
            ]);
    }


    /**
     * 编辑选项处理
     * @param Request $request
     * @return false|string
     */
    public function editOptionAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titleId' => 'required',
            'title' => 'required',
        ]);
        if ($validator->fails()) {
            $data['uses'] = -1;
            return json_encode($data);
        }
        $id = $request->input('titleId');
        $data['Title'] = $request->input('title');
        $data['ModifiedTime'] = Carbon::now();
        $res = DB::table('snets_tst_OptionItem')->where('Id','=',$id)->update($data);
        if ($res){
            $data['uses'] = 1;
            return json_encode($data);
        }else{
            $data['uses'] = -2;
            return json_encode($data);
        }
    }

    /**
     * 删除选项
     * @param int $id
     * @return false|string
     */
    public function deleteOption(int $id)
    {
        $res = DB::table('snets_tst_OptionItem')->where('Id','=',$id)->update(['status' => 0]);
        if ($res){
            $data['uses'] = 1;
            return json_encode($data);
        }else{
            $data['uses'] = 0;
            return json_encode($data);
        }
    }

    /**
     * 统计管理
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function report()
    {
        $username = session('username');
        return view(
            'report',
            [
                'title' => '统计管理> 测评报告',
                'username' => $username,
                'url' => 'report'
            ]);
    }



    /**
     * 自评报告导出
     * @return JsonResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportUserReport()
    {
        $rankConf = [
            1 => 0,
            2 => 1,
            3 => 2,
            4 => 'N/A'
        ];
        /**
         * 用户信息
         */

        $sql = "select 
                      r2.EmployeeId
                      ,u2.Name
                      ,u2.Phone
                      ,u2.Email
                      ,u2.Department
                      ,u2.Positions
                      ,u2.Station
                      ,u2.Location
                      ,u2.CompanyId
                      ,l2.Title AS RankName,
                      r2.UserReport,
                      r2.Status,
                      r2.SeltTime
                  FROM (
                        SELECT
                         r.EmployeeId,
                         max(r.SeltTime) AS maxSeltTime
                         FROM snets_tst_Report r
                         LEFT JOIN snets_tst_Rank l ON r.RankId = l.Id
                         LEFT JOIN snets_emp_Employee u ON r.EmployeeId = u.Id 
                         WHERE u.Positions = l.Title
                         group by r.EmployeeId
                ) AS t 
                LEFT JOIN snets_tst_Report AS r2 ON t.EmployeeId = r2.EmployeeId AND t.maxSeltTime = r2.SeltTime
                LEFT JOIN snets_tst_Rank l2 ON r2.RankId = l2.Id
                LEFT JOIN snets_emp_Employee u2 ON r2.EmployeeId = u2.Id
                LEFT JOIN snets_emp_Office o ON u2.Id = o.EmployeeId
                WHERE u2.Positions = l2.Title";
        $employee = DB::select($sql);
        foreach ($employee as $value) {
            $rows['employeeId'] = $value->EmployeeId;
            $rows['name'] = $value->Name;
            $rows['phone'] = $value->Phone;
            $rows['department'] = $value->Department;
            $rows['position'] = $value->Positions;
            $rows['station'] = $value->Station;
            $rows['email'] = $value->Email;
            $rows['companyId'] = $value->CompanyId;
            $rows['location'] = $value->Location;
            $rows['time'] = '';
            $rows['rankName'] = $value->RankName;
            $rows['产品知识'] = '';
            $rows['临床知识'] = '';
            $rows['时间管理'] = '';
            $rows['终端管理'] = '';
            $rows['区域管理'] = '';
            $rows['渠道管理'] = '';
            $rows['市场敏锐'] = '';
            $rows['协作共赢'] = '';
            $rows['辅导培育'] = '';
            $rows['团队建设'] = '';
            $rows['引领方向'] = '';
            $rows['驱动变革'] = '';
            $reportArr = json_decode($value->UserReport, true);
            $rows['time'] = date('Y-m-d H:i:s', strtotime($value->SeltTime));
                foreach ($reportArr as $reportValue) {
                    if ($reportValue['Title'] == '产品知识') {
                        $rows['产品知识'] = $rankConf[$reportValue['UserColor']];
                    } else if ($reportValue['Title'] == '临床知识') {
                        $rows['临床知识'] = $rankConf[$reportValue['UserColor']];
                    } else if ($reportValue['Title'] == '时间管理') {
                        $rows['时间管理'] = $rankConf[$reportValue['UserColor']];
                    } else if ($reportValue['Title'] == '终端管理') {
                        $rows['终端管理'] = $rankConf[$reportValue['UserColor']];
                    } else if ($reportValue['Title'] == '区域管理') {
                        $rows['区域管理'] = $rankConf[$reportValue['UserColor']];
                    } else if ($reportValue['Title'] == '渠道管理') {
                        $rows['渠道管理'] = $rankConf[$reportValue['UserColor']];
                    } else if ($reportValue['Title'] == '市场敏锐') {
                        $rows['市场敏锐'] = $rankConf[$reportValue['UserColor']];
                    } else if ($reportValue['Title'] == '协作共赢') {
                        $rows['协作共赢'] = $rankConf[$reportValue['UserColor']];
                    } else if ($reportValue['Title'] == '辅导培育') {
                        $rows['辅导培育'] = $rankConf[$reportValue['UserColor']];
                    } else if ($reportValue['Title'] == '团队建设') {
                        $rows['团队建设'] = $rankConf[$reportValue['UserColor']];
                    } else if ($reportValue['Title'] == '引领方向') {
                        $rows['引领方向'] = $rankConf[$reportValue['UserColor']];
                    } else if ($reportValue['Title'] == '驱动变革') {
                        $rows['驱动变革'] = $rankConf[$reportValue['UserColor']];
                    }
            }
            $response[] = $rows;
        }
        $newExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();  //创建一个新的excel文档
        $objSheet = $newExcel->getActiveSheet();  //获取当前操作sheet的对象
        $objSheet->setTitle('自评能力报告');  //设置当前sheet的标题

        //设置宽度为true,不然太窄了
        $newExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        //$newExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('T')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('U')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('V')->setAutoSize(true);

        //设置第一栏的标题
        $objSheet->setCellValue('A1', 'Name')
            ->setCellValue('B1', 'Phone')
            ->setCellValue('C1', 'Department')
            ->setCellValue('D1', 'Position')
            //->setCellValue('E1', '直接上属')
            ->setCellValue('E1', 'station')
            ->setCellValue('F1', '邮箱')
            ->setCellValue('G1', 'companyId')
            ->setCellValue('H1', 'location')
            ->setCellValue('I1', '评估完成时间')
            ->setCellValue('J1', '评估等级')
            ->setCellValue('K1', '产品知识')
            ->setCellValue('L1', '临床知识')
            ->setCellValue('M1', '时间管理')
            ->setCellValue('N1', '终端管理')
            ->setCellValue('O1', '区域管理')
            ->setCellValue('P1', '渠道管理')
            ->setCellValue('Q1', '市场敏锐')
            ->setCellValue('R1', '协作共赢')
            ->setCellValue('S1', '辅导培育')
            ->setCellValue('T1', '团队建设')
            ->setCellValue('U1', '引领方向')
            ->setCellValue('V1', '驱动变革');



        //第二行起，每一行的值,setCellValueExplicit是用来导出文本格式的。
        //->setCellValueExplicit('C' . $k, $val['admin_password']PHPExcel_Cell_DataType::TYPE_STRING),可以用来导出数字不变格式
        foreach ($response as $k => $val) {
            $k = $k + 2;
            $objSheet->setCellValue('A' . $k, $val['name'])
                ->setCellValue('B' . $k, $val['phone'])
                ->setCellValue('C' . $k, $val['department'])
                ->setCellValue('D' . $k, $val['position'])
                //->setCellValue('E' . $k, $val['leaderName'])
                ->setCellValue('E' . $k, $val['station'])
                ->setCellValue('F' . $k, $val['email'])
                ->setCellValue('G' . $k, $val['companyId'])
                ->setCellValue('H' . $k, $val['location'])
                ->setCellValue('I' . $k, $val['time'])
                ->setCellValue('J' . $k, $val['rankName'])
                ->setCellValue('K' . $k, $val['产品知识'])
                ->setCellValue('L' . $k, $val['临床知识'])
                ->setCellValue('M' . $k, $val['时间管理'])
                ->setCellValue('N' . $k, $val['终端管理'])
                ->setCellValue('O' . $k, $val['区域管理'])
                ->setCellValue('P' . $k, $val['渠道管理'])
                ->setCellValue('Q' . $k, $val['市场敏锐'])
                ->setCellValue('R' . $k, $val['协作共赢'])
                ->setCellValue('S' . $k, $val['辅导培育'])
                ->setCellValue('T' . $k, $val['团队建设'])
                ->setCellValue('U' . $k, $val['引领方向'])
                ->setCellValue('V' . $k, $val['驱动变革']);

        }


        $fileName = '自评能力报告_' . date('Y-m-d', time()) . '_' . rand(0, 999) . '.xlsx';

        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($newExcel, 'Xls');

        $objWriter->save($fileName);

        return response()->json(['code' => 100, 'data' => ['filePath' => env('APP_URL').'/' . $fileName]]);


    }

    /**
     * 他评报告导出
     * @return JsonResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportReport()
    {
        $rankConf = [
            1 => 0,
            2 => 1,
            3 => 2,
            4 => 'N/A'
        ];
        /**
         * 用户信息
         */

        $sql = "select r2.OtherId
                      ,r2.EmployeeId
                      ,u2.Name
                      ,u2.Phone
                      ,u2.Email
                      ,u2.Department
                      ,u2.Positions
                      ,u2.Station
                      ,u2.Location
                      ,u2.CompanyId
                      ,l2.Title AS RankName,
                      r2.ReportInfo,
                      r2.Status,
                      r2.SeltTime,
                      r2.OtherTime,
                      u3.Name AS LeaderName 
                  FROM (
                        SELECT
                         r.EmployeeId,
                         max(r.OtherTime) AS maxOtherTime
                         FROM snets_tst_Report r
                         LEFT JOIN snets_tst_Rank l ON r.RankId = l.Id
                         LEFT JOIN snets_emp_Employee u ON r.EmployeeId = u.Id 
                         WHERE u.Positions = l.Title AND r.Status = 1
                         group by r.EmployeeId
                ) AS t 
                LEFT JOIN snets_tst_Report AS r2 ON t.EmployeeId = r2.EmployeeId AND t.maxOtherTime = r2.OtherTime
                LEFT JOIN snets_tst_Rank l2 ON r2.RankId = l2.Id
                LEFT JOIN snets_emp_Employee u2 ON r2.EmployeeId = u2.Id
                LEFT JOIN snets_emp_Office o ON u2.Id = o.EmployeeId
                LEFT JOIN snets_emp_Employee u3 ON o.LeaderId = u3.Id
                WHERE u2.Positions = l2.Title AND r2.Status = 1";
        $employee = DB::select($sql);
        foreach ($employee as $value) {
            $rows['employeeId'] = $value->EmployeeId;
            $rows['name'] = $value->Name;
            $rows['phone'] = $value->Phone;
            $rows['department'] = $value->Department;
            $rows['position'] = $value->Positions;
            $rows['leaderName'] = $value->LeaderName;
            $rows['station'] = $value->Station;
            $rows['email'] = $value->Email;
            $rows['companyId'] = $value->CompanyId;
            $rows['location'] = $value->Location;
            $rows['time'] = '';
            $rows['rankName'] = $value->RankName;
            $rows['产品知识'] = '';
            $rows['临床知识'] = '';
            $rows['时间管理'] = '';
            $rows['终端管理'] = '';
            $rows['区域管理'] = '';
            $rows['渠道管理'] = '';
            $rows['市场敏锐'] = '';
            $rows['协作共赢'] = '';
            $rows['辅导培育'] = '';
            $rows['团队建设'] = '';
            $rows['引领方向'] = '';
            $rows['驱动变革'] = '';
            $reportArr = json_decode($value->ReportInfo, true);
            $rows['time'] = date('Y-m-d H:i:s', strtotime($value->OtherTime));
            foreach ($reportArr as $reportValue) {
                if ($reportValue['Title'] == '产品知识') {
                    $rows['产品知识'] = $rankConf[$reportValue['OtherColor']];
                } else if ($reportValue['Title'] == '临床知识') {
                    $rows['临床知识'] = $rankConf[$reportValue['OtherColor']];
                } else if ($reportValue['Title'] == '时间管理') {
                    $rows['时间管理'] = $rankConf[$reportValue['OtherColor']];
                } else if ($reportValue['Title'] == '终端管理') {
                    $rows['终端管理'] = $rankConf[$reportValue['OtherColor']];
                } else if ($reportValue['Title'] == '区域管理') {
                    $rows['区域管理'] = $rankConf[$reportValue['OtherColor']];
                } else if ($reportValue['Title'] == '渠道管理') {
                    $rows['渠道管理'] = $rankConf[$reportValue['OtherColor']];
                } else if ($reportValue['Title'] == '市场敏锐') {
                    $rows['市场敏锐'] = $rankConf[$reportValue['OtherColor']];
                } else if ($reportValue['Title'] == '协作共赢') {
                    $rows['协作共赢'] = $rankConf[$reportValue['OtherColor']];
                } else if ($reportValue['Title'] == '辅导培育') {
                    $rows['辅导培育'] = $rankConf[$reportValue['OtherColor']];
                } else if ($reportValue['Title'] == '团队建设') {
                    $rows['团队建设'] = $rankConf[$reportValue['OtherColor']];
                } else if ($reportValue['Title'] == '引领方向') {
                    $rows['引领方向'] = $rankConf[$reportValue['OtherColor']];
                } else if ($reportValue['Title'] == '驱动变革') {
                    $rows['驱动变革'] = $rankConf[$reportValue['OtherColor']];
                }
            }
            $response[] = $rows;
        }
        $newExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();  //创建一个新的excel文档
        $objSheet = $newExcel->getActiveSheet();  //获取当前操作sheet的对象
        $objSheet->setTitle('他评能力报告');  //设置当前sheet的标题

        //设置宽度为true,不然太窄了
        $newExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('T')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('U')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('V')->setAutoSize(true);
        $newExcel->getActiveSheet()->getColumnDimension('W')->setAutoSize(true);

        //设置第一栏的标题
        $objSheet->setCellValue('A1', 'Name')
            ->setCellValue('B1', 'Phone')
            ->setCellValue('C1', 'Department')
            ->setCellValue('D1', 'Position')
            ->setCellValue('E1', '直接上属')
            ->setCellValue('F1', 'Business Title')
            ->setCellValue('G1', '邮箱')
            ->setCellValue('H1', 'ID')
            ->setCellValue('I1', 'Location')
            ->setCellValue('J1', '评估完成时间')
            ->setCellValue('K1', '双方评估等级')
            ->setCellValue('L1', '产品知识')
            ->setCellValue('M1', '临床知识')
            ->setCellValue('N1', '时间管理')
            ->setCellValue('O1', '终端管理')
            ->setCellValue('P1', '区域管理')
            ->setCellValue('Q1', '渠道管理')
            ->setCellValue('R1', '市场敏锐')
            ->setCellValue('S1', '协作共赢')
            ->setCellValue('T1', '辅导培育')
            ->setCellValue('U1', '团队建设')
            ->setCellValue('V1', '引领方向')
            ->setCellValue('W1', '驱动变革');



        //第二行起，每一行的值,setCellValueExplicit是用来导出文本格式的。
        //->setCellValueExplicit('C' . $k, $val['admin_password']PHPExcel_Cell_DataType::TYPE_STRING),可以用来导出数字不变格式
        foreach ($response as $k => $val) {
            $k = $k + 2;
            $objSheet->setCellValue('A' . $k, $val['name'])
                ->setCellValue('B' . $k, $val['phone'])
                ->setCellValue('C' . $k, $val['department'])
                ->setCellValue('D' . $k, $val['position'])
                ->setCellValue('E' . $k, $val['leaderName'])
                ->setCellValue('F' . $k, $val['station'])
                ->setCellValue('G' . $k, $val['email'])
                ->setCellValue('H' . $k, $val['companyId'])
                ->setCellValue('I' . $k, $val['location'])
                ->setCellValue('J' . $k, $val['time'])
                ->setCellValue('K' . $k, $val['rankName'])
                ->setCellValue('L' . $k, $val['产品知识'])
                ->setCellValue('M' . $k, $val['临床知识'])
                ->setCellValue('N' . $k, $val['时间管理'])
                ->setCellValue('O' . $k, $val['终端管理'])
                ->setCellValue('P' . $k, $val['区域管理'])
                ->setCellValue('Q' . $k, $val['渠道管理'])
                ->setCellValue('R' . $k, $val['市场敏锐'])
                ->setCellValue('S' . $k, $val['协作共赢'])
                ->setCellValue('T' . $k, $val['辅导培育'])
                ->setCellValue('U' . $k, $val['团队建设'])
                ->setCellValue('V' . $k, $val['引领方向'])
                ->setCellValue('W' . $k, $val['驱动变革']);

        }


        $fileName = '他评能力报告_' . date('Y-m-d', time()) . '_' . rand(0, 999) . '.xlsx';

        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($newExcel, 'Xls');

        $objWriter->save($fileName);

        return response()->json(['code' => 100, 'data' => ['filePath' => env('APP_URL').'/' . $fileName]]);


    }

}
