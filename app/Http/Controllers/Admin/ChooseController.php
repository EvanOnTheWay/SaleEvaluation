<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ChooseController extends Controller
{
    /**
     * 筛选导出主页
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $username = session('username');

        // 部门选择
        $department_info = DB::table('snets_emp_Employee')
            ->select('Department')
            ->where('Status', '<', 2)
            ->groupBy('Department')
            ->get()
            ->toArray();

        // 评测等级
        $level_info = DB::table('snets_tst_Rank')
            ->select(['Id', 'Title'])
            ->where('Status', '=', 1)
            ->get()
            ->toArray();

        // 能力此条（默认P1）
        $ability_info = DB::table('snets_emp_Ability')
            ->select(['Id', 'Title'])
            ->where('RankId', '=', 1)
            ->where('Depth', '=', 2)
            ->get()
            ->toArray();

        return view('choose', [
            'title' => '筛选导出主页',
            'username' => $username,
            'department_info' => $department_info,
            'level_info' => $level_info,
            'ability_info' => $ability_info,
            'url' => 'choose'
        ]);
    }

    /**
     * 获取能力词条
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rank_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['code' => 201, 'message' => '职级ID不能为空！']);
        }

        $ability_info = DB::table('snets_emp_Ability')
            ->select(['Id', 'Title'])
            ->where('RankId', '=', $request->input('rank_id'))
            ->where('Depth', '=', 2)
            ->get()
            ->toArray();

        return response()->json(['code' => 200, 'data' => $ability_info]);
    }

    /**
     * 选项列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function optionList(Request $request)
    {
        $search_info = $request->input('search_info');

        $username = session('username');

        $query = DB::table('snets_tst_OptionItem as A')
            // 关联题目表
            ->leftJoin('snets_tst_Question as B', 'A.QuestionId', '=', 'B.Id')
            // 关联词条表
            ->leftJoin('snets_emp_Ability as C', 'B.AbilityId', '=', 'C.Id')
            // 关联职级表
            ->leftJoin('snets_tst_Rank as D', 'C.RankId', '=', 'D.Id')
            ->select([
                'D.Title as RankTitle',     // 职级名称
                'C.Title as AbilityTitle',  // 词条名称
                'B.Title as QuestionTitle', // 题目名称
                'A.Title as OptionTitle',   // 选项名称
                'A.IsDisplay',              // 选项在导出时是否显示
                'A.Id',
            ]);

        if(!empty($search_info)){
            $query->where(function ($query) use ($search_info) {
                $query->where('A.Title', 'like', '%' . $search_info . '%');
                $query->orWhere('B.Title', 'like', '%' . $search_info . '%');
                $query->orWhere('C.Title', 'like', '%' . $search_info . '%');
                $query->orWhere('D.Title', 'like', '%' . $search_info . '%');
            });
        }
        $data = $query->orderBy('A.Id', 'asc')->paginate(20);

        return view('chooseOption', ['data' => $data, 'search_info' => $search_info, 'title' => '指定导出选项', 'username' => $username, 'url' => 'chooseOption']);
    }

    /**
     * 更改选项导出显示状态
     * @param int $id
     * @return false|string
     */
    public function optionStatus(int $id)
    {
        $info = DB::table('snets_tst_OptionItem')->where('Id', '=', $id)->first();

        // 数据库是隐藏，点击改成显示
        if ($info->IsDisplay == 0) {
            $update['IsDisplay'] = 1;

        // 数据库是显示，点击改成隐藏
        } else {
            $update['IsDisplay'] = 0;
        }

        $result = DB::table('snets_tst_OptionItem')->where('Id', '=', $id)->update($update);

        if ($result) {
            $data['uses'] = 1;
            return json_encode($data);
        } else {
            $data['uses'] = 0;
            return json_encode($data);
        }
    }

    /**
     * 报告导出
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function export(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'report_type' => 'required',
            'department' => 'required',
            'is_standard' => 'required',
            'rank_id' => 'required',
            'ability' => 'required',
            'time_start' => 'required',
            'time_end' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['code' => 201, 'message' => "有选项尚未填写完整，请核对后再提交！"]);
        }

        // SQL查询开始
        $query = DB::table('snets_tst_Report as A')
            ->leftjoin('snets_emp_Employee AS B', 'A.EmployeeId', '=', 'B.Id')
            ->leftjoin('snets_tst_Rank AS C', 'A.RankId', '=', 'C.Id');

        // 查询报告类型（1=>自评报告，2=>他评报告）
        if ($request->input('report_type') == 1) {
            $query->select([
                'B.Name',
                'B.Phone',
                'B.Department',
                'B.Positions',
                'B.Station',
                'B.Email',
                'B.CompanyId',
                'B.Location',
                'C.Title',
                'A.SeltTime as Time',
                'A.UserReport as ReportInfo',
            ]);
        } else {
            $query->select([
                'B.Name',
                'B.Phone',
                'B.Department',
                'B.Positions',
                'B.Station',
                'B.Email',
                'B.CompanyId',
                'B.Location',
                'C.Title',
                'A.OtherTime as Time',
                'A.OtherReport as ReportInfo'
            ]);
        }

        // 部门筛选
        $query->whereIn('B.Department', $request->input('department'));

        // 职级筛选
        $query->where('A.RankId', '=', $request->input('rank_id'));

        // 日期筛选
        if ($request->input('report_type') == 1) {
            $query->whereBetween('A.SeltTime', [$request->input('time_start'), $request->input('time_end')]);
        } else {
            $query->whereBetween('A.OtherTime', [$request->input('time_start'), $request->input('time_end')]);
        }

        $report_info = $query->get()->toArray();

        // 报告转数组
        foreach ($report_info as $key => $value) {
            if ($request->input('report_type') == 1) {
                $report_info[$key]->ReportInfo = json_decode($value->ReportInfo);
            } else {
                $report_info[$key]->ReportInfo = json_decode($value->ReportInfo);
            }
        }

        // TODO:: 词条筛选
        foreach ($report_info as $key => $value) {
            foreach ($value->ReportInfo as $ks => $vs) {
                if ($vs->Id != $request->input('ability')) {
                    // 如果词条不匹配的话，直接移除
                    unset($value->ReportInfo[$ks]);
                }
            }
        }

        // TODO:: 达标筛选
        foreach ($report_info as $key => $value) {
            if (!empty($value->ReportInfo)) {
                $first_key = key($value->ReportInfo);
                // 如果是未达标，移除已达标、已超标数据
                if ($request->input('is_standard') == 1) {
                    // 自评
                    if ($request->input('report_type') == 1) {
                        if ($value->ReportInfo[$first_key]->UserColor == 2 || $value->ReportInfo[$first_key]->UserColor == 3) {
                            unset($report_info[$key]);
                        }
                    }
                    // 他评
                    if ($request->input('report_type') == 2) {
                        if ($value->ReportInfo[$first_key]->OtherColor == 2 || $value->ReportInfo[$first_key]->OtherColor == 3) {
                            unset($report_info[$key]);
                        }
                    }
                }
                // 如果是已达标，移除未达标、范围外、已超标数据
                if ($request->input('is_standard') == 2) {
                    // 自评
                    if ($request->input('report_type') == 1) {
                        if ($value->ReportInfo[$first_key]->UserColor == 1 || $value->ReportInfo[$first_key]->UserColor == 3 || $value->ReportInfo[$first_key]->UserColor == 4) {
                            unset($report_info[$key]);
                        }
                    }
                    // 他评
                    if ($request->input('report_type') == 2) {
                        if ($value->ReportInfo[$first_key]->OtherColor == 1 || $value->ReportInfo[$first_key]->OtherColor == 3 || $value->ReportInfo[$first_key]->OtherColor == 4) {
                            unset($report_info[$key]);
                        }
                    }
                }
                // 如果是已超标，移除未达标、范围外、已达标数据
                if ($request->input('is_standard') == 3) {
                    // 自评
                    if ($request->input('report_type') == 1) {
                        if ($value->ReportInfo[$first_key]->UserColor == 1 || $value->ReportInfo[$first_key]->UserColor == 2 || $value->ReportInfo[$first_key]->UserColor == 4) {
                            unset($report_info[$key]);
                        }
                    }
                    // 他评
                    if ($request->input('report_type') == 2) {
                        if ($value->ReportInfo[$first_key]->OtherColor == 1 || $value->ReportInfo[$first_key]->OtherColor == 2 || $value->ReportInfo[$first_key]->OtherColor == 4) {
                            unset($report_info[$key]);
                        }
                    }
                }
            }
        }

        // TODO:: 主数组重新排键
        $report_info_x = array();
        foreach ($report_info as $value) {
            $report_info_x[] = $value;
        }
        $report_info = $report_info_x;

        // TODO:: 拿取出所有选项及答题情况
        foreach ($report_info as $key => $value) {
            if (!empty($value->ReportInfo)) {
                $first_key = key($value->ReportInfo);
                $option_info = $value->ReportInfo[$first_key]->question_data;
                foreach ($option_info as $ks => $vs) {
                    foreach ($vs->option_data as $kx => $vx) {
                        $report_info[$key]->option_lists[] = $vx;
                    }
                }
                // 去除后台未开启显示配置的选项
                foreach ($report_info[$key]->option_lists as $kz => $vz) {
                    $have_open = DB::table('snets_tst_OptionItem')->where('Id', '=', $vz->Id)->value('IsDisplay');
                    if($have_open == 0){
                        unset($report_info[$key]->option_lists[$kz]);
                    }
                }
                foreach ($report_info[$key]->option_lists as $vc){
                    $report_info[$key]->option_list[] = $vc;
                }
            }
        }

        /**
         * 导出开始
         */
        $newExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $objSheet = $newExcel->getActiveSheet();

        // excel命名调整
        $rank_title = DB::table('snets_tst_Rank')->where('Id', '=', $request->input('rank_id'))->value('Title');
        $ability_title = DB::table('snets_emp_Ability')->where('Id', '=', $request->input('ability'))->value('Title');
        $standard_title = '';
        if ($request->input('is_standard') == 1) {
            $standard_title = "未达标";
        }
        if ($request->input('is_standard') == 2) {
            $standard_title = "已达标";
        }
        if ($request->input('is_standard') == 3) {
            $standard_title = "已超标";
        }

        $sheet_title = $ability_title . " -- " . $rank_title . " -- " . $standard_title;
        $objSheet->setTitle($sheet_title);

        //设置宽度
        $newExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $newExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $newExcel->getActiveSheet()->getColumnDimension('C')->setWidth(45);
        $newExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $newExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18);
        $newExcel->getActiveSheet()->getColumnDimension('F')->setWidth(35);
        $newExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $newExcel->getActiveSheet()->getColumnDimension('H')->setWidth(12);
        $newExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $newExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
        $newExcel->getActiveSheet()->getColumnDimension('K')->setWidth(45);
        $newExcel->getActiveSheet()->getColumnDimension('L')->setWidth(45);
        $newExcel->getActiveSheet()->getColumnDimension('M')->setWidth(45);
        $newExcel->getActiveSheet()->getColumnDimension('N')->setWidth(45);
        $newExcel->getActiveSheet()->getColumnDimension('O')->setWidth(45);
        $newExcel->getActiveSheet()->getColumnDimension('P')->setWidth(45);
        $newExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(45);
        $newExcel->getActiveSheet()->getColumnDimension('R')->setWidth(45);
        $newExcel->getActiveSheet()->getColumnDimension('S')->setWidth(45);
        $newExcel->getActiveSheet()->getColumnDimension('T')->setWidth(45);
        $newExcel->getActiveSheet()->getColumnDimension('U')->setWidth(45);
        $newExcel->getActiveSheet()->getColumnDimension('V')->setWidth(45);
        $newExcel->getActiveSheet()->getColumnDimension('W')->setWidth(45);
        $newExcel->getActiveSheet()->getColumnDimension('X')->setWidth(45);
        $newExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(45);
        $newExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(45);

        //设置第一栏的标题
        $objSheet
            ->setCellValue('A1', 'Name')
            ->setCellValue('B1', 'Phone')
            ->setCellValue('C1', 'Department')
            ->setCellValue('D1', 'Position')
            ->setCellValue('E1', 'Station')
            ->setCellValue('F1', 'Email')
            ->setCellValue('G1', 'CompanyId')
            ->setCellValue('H1', 'Location')
            ->setCellValue('I1', '测评时间')
            ->setCellValue('J1', '评估等级');

        foreach ($report_info as $key => $value) {
            if (!empty($value->option_list[0])) {
                $objSheet->setCellValue('K1', $value->option_list[0]->Title);
            }
            if (!empty($value->option_list[1])) {
                $objSheet->setCellValue('L1', $value->option_list[1]->Title);
            }
            if (!empty($value->option_list[2])) {
                $objSheet->setCellValue('M1', $value->option_list[2]->Title);
            }
            if (!empty($value->option_list[3])) {
                $objSheet->setCellValue('N1', $value->option_list[3]->Title);
            }
            if (!empty($value->option_list[4])) {
                $objSheet->setCellValue('O1', $value->option_list[4]->Title);
            }
            if (!empty($value->option_list[5])) {
                $objSheet->setCellValue('P1', $value->option_list[5]->Title);
            }
            if (!empty($value->option_list[6])) {
                $objSheet->setCellValue('Q1', $value->option_list[6]->Title);
            }
            if (!empty($value->option_list[7])) {
                $objSheet->setCellValue('R1', $value->option_list[7]->Title);
            }
            if (!empty($value->option_list[8])) {
                $objSheet->setCellValue('S1', $value->option_list[8]->Title);
            }
            if (!empty($value->option_list[9])) {
                $objSheet->setCellValue('T1', $value->option_list[9]->Title);
            }
            if (!empty($value->option_list[10])) {
                $objSheet->setCellValue('U1', $value->option_list[10]->Title);
            }
            if (!empty($value->option_list[11])) {
                $objSheet->setCellValue('V1', $value->option_list[11]->Title);
            }
            if (!empty($value->option_list[12])) {
                $objSheet->setCellValue('W1', $value->option_list[12]->Title);
            }
            if (!empty($value->option_list[13])) {
                $objSheet->setCellValue('X1', $value->option_list[13]->Title);
            }
            if (!empty($value->option_list[14])) {
                $objSheet->setCellValue('Y1', $value->option_list[14]->Title);
            }
            if (!empty($value->option_list[15])) {
                $objSheet->setCellValue('Z1', $value->option_list[15]->Title);
            }
        }

        // 第二栏开始赋值
        foreach ($report_info as $key => $value) {
            $time_x = date('Y-m-d H:i:s', strtotime($value->Time));

            $key = $key + 2;
            $objSheet
                ->setCellValue('A' . $key, $value->Name)
                ->setCellValue('B' . $key, $value->Phone)
                ->setCellValue('C' . $key, $value->Department)
                ->setCellValue('D' . $key, $value->Positions)
                ->setCellValue('E' . $key, $value->Station)
                ->setCellValue('F' . $key, $value->Email)
                ->setCellValue('G' . $key, $value->CompanyId)
                ->setCellValue('H' . $key, $value->Location)
                ->setCellValue('I' . $key, $time_x)
                ->setCellValue('J' . $key, $value->Title);

            if (!empty($value->option_list[0])) {
                $objSheet->setCellValue('K' . $key, $value->option_list[0]->Status);
            }
            if (!empty($value->option_list[1])) {
                $objSheet->setCellValue('L' . $key, $value->option_list[1]->Status);
            }
            if (!empty($value->option_list[2])) {
                $objSheet->setCellValue('M' . $key, $value->option_list[2]->Status);
            }
            if (!empty($value->option_list[3])) {
                $objSheet->setCellValue('N' . $key, $value->option_list[3]->Status);
            }
            if (!empty($value->option_list[4])) {
                $objSheet->setCellValue('O' . $key, $value->option_list[4]->Status);
            }
            if (!empty($value->option_list[5])) {
                $objSheet->setCellValue('P' . $key, $value->option_list[5]->Status);
            }
            if (!empty($value->option_list[6])) {
                $objSheet->setCellValue('Q' . $key, $value->option_list[6]->Status);
            }
            if (!empty($value->option_list[7])) {
                $objSheet->setCellValue('R' . $key, $value->option_list[7]->Status);
            }
            if (!empty($value->option_list[8])) {
                $objSheet->setCellValue('S' . $key, $value->option_list[8]->Status);
            }
            if (!empty($value->option_list[9])) {
                $objSheet->setCellValue('T' . $key, $value->option_list[9]->Status);
            }
            if (!empty($value->option_list[10])) {
                $objSheet->setCellValue('U' . $key, $value->option_list[10]->Status);
            }
            if (!empty($value->option_list[11])) {
                $objSheet->setCellValue('V' . $key, $value->option_list[11]->Status);
            }
            if (!empty($value->option_list[12])) {
                $objSheet->setCellValue('W' . $key, $value->option_list[12]->Status);
            }
            if (!empty($value->option_list[13])) {
                $objSheet->setCellValue('X' . $key, $value->option_list[13]->Status);
            }
            if (!empty($value->option_list[14])) {
                $objSheet->setCellValue('Y' . $key, $value->option_list[14]->Status);
            }
            if (!empty($value->option_list[15])) {
                $objSheet->setCellValue('Z' . $key, $value->option_list[15]->Status);
            }
        }

        $fileName = $sheet_title . '.xls';

        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($newExcel, 'Xls');

        $objWriter->save($fileName);

        return response()->json(['code' => 200, 'data' => ['filePath' => env('APP_URL').'/' . $fileName]]);
    }
}
