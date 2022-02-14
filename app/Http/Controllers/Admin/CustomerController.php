<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;


class CustomerController extends Controller
{
    /**
     * 用户列表
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function customerList(Request $request)
    {
        $name = $request->input('title');
        $username = session('username');
        $query = DB::table('snets_emp_Employee')->select([
            'Id', 'Name', 'Phone', 'Department', 'Positions', 'Status','Station'
        ])
            ->where('Status','<>',2);
        if ($name != '') {
            $query->where('Name', 'LIKE', '%' . $name . '%');
        }
        $data = $query->get()->toArray();
        foreach ($data as $k => $v){
            if ($v->Status == 0){
                $data[$k]->Status = '未绑定';
            }
            if ($v->Status == 1){
                $data[$k]->Status = '已绑定';
            }
        }
        return view(
            'customerList',
            [
                'title' => '用户管理> 用户列表',
                'data' => $data,
                'username' => $username,
                'url' => 'customerList'
            ]);
    }

    /**
     * 新增用户
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addCustomer()
    {
        $username = session('username');
        $positions = ['M1','M2','M3','M4'];
        $data = DB::table('snets_emp_Employee')->select([
            'Id', 'Name',
        ])
            ->whereIn('Positions',$positions)
            ->where('Status','<>',2)
            ->get()
            ->toArray();
        return view(
            'addCustomer',
            [
                'data' => $data,
                'title' => '用户管理> 新增用户',
                'username' => $username,
                'url' => 'customerList'
            ]);
    }

    /**
     * 新增用户处理
     * @param Request $request
     * @return false|string
     */
    public function addCustomerAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'names' => 'required',
            'phone' => 'required',
            'department' => 'required',
            'station' => 'required',
            'positions' => 'required',
        ]);
        if ($validator->fails()) {
            $data['uses'] = -1;
            return json_encode($data);
        }
        $data['Name'] = $request->input('names');
        $data['Phone'] = $request->input('phone');
        $data['Department'] = $request->input('department');
        $data['Station'] = $request->input('station');
        $data['Positions'] = $request->input('positions');
        $data['Email'] = $request->input('email');
        $data['CompanyId'] = $request->input('companyId');
        $data['Location'] = $request->input('locations');
        $data['Status'] = 0;
        $data['CreatedTime'] = Carbon::now();
        //开启事务
        DB::beginTransaction();
        try {
            //插入用户并获取id
            $employeeId = DB::table('snets_emp_Employee')->insertGetId($data);
            $office['LeaderId'] = $request->input('leaderId');
            $office['EmployeeId'] = $employeeId ;
            $office['CreatedTime'] = Carbon::now();
            DB::table('snets_emp_Office')->insert($office);
            //提交事务
            DB::commit();
            $data['uses'] = 1;
            return json_encode($data);
        } catch (\Exception $e) {
            //事务回滚
            DB::rollBack();
            $data['uses'] = -2;
            return json_encode($data);
        }

    }


    /**
     * 编辑用户
     * @param int $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editCustomer(Request $request)
    {
        $id = $request->input('id');
        $username = session('username');
        //用户信息
        $user = DB::table('snets_emp_Employee')->select([
            'Id', 'Name', 'Phone', 'Department', 'Positions', 'Email','Station',
            'UserId','HeadImg','CompanyId','Location'
        ])
            ->where('id','=',$id)
            ->first();
        $positions = ['M1','M2','M3','M4'];
        //M1~M4所有人员
        $allLeader = DB::table('snets_emp_Employee')->select([
            'Id', 'Name',
        ])
            ->whereIn('Positions',$positions)
            ->where('Status','<>',2)
            ->get()
            ->toArray();
        //领导
        $res = DB::table('snets_emp_Office')
            ->select(['LeaderId'])
            ->where('EmployeeId','=',$id)
            ->first();

        // TODO:: 20210421补丁 如果职位过高没有上级，增加逻辑处理
        $leaders = [];

        if (empty($res) || $res == null) {
            foreach ($allLeader as $value) {
                $row['select'] = 0;
                $row['id'] = $value->Id;
                $row['name'] = $value->Name;
                $leaders[] = $row;
            }
        } else {
            foreach ($allLeader as $value) {
                if ($res->LeaderId == $value->Id) {
                    $row['select'] = $value->Id;
                } else {
                    $row['select'] = 0;
                }
                $row['id'] = $value->Id;
                $row['name'] = $value->Name;
                $leaders[] = $row;
            }
        }

        return view(
            'editCustomer',
            [
                'user' => $user,
                'leaders' => $leaders,
                'title' => '用户管理> 编辑用户',
                'username' => $username,
                'url' => 'customerList'
            ]);
    }


    /**
     * 一键导入用户
     * @param Request $request
     * @return bool|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function import(Request $request)
    {
        $file = $request->file();
//        $name = $file['customer_excel']->getClientOriginalName();
//        $uploadName = $file['customer_excel']->getRealPath();
        $name = $file['customer_excel']->getClientOriginalName();
        $uploadName = $file['customer_excel']->getPathName();

        /*dd($file);
        dd($uploadName);*/
        //获取表格的大小，限制上传表格的大小5M
        $file_size = $file['customer_excel']->getSize();
        if ($file_size > 5 * 1024 * 1024) {
            return false;
        }
        //限制上传表格类型
        $fileExtendName = substr(strrchr($name, '.'), 1);
        //application/vnd.ms-excel  为xls文件类型
        if ($fileExtendName != 'xls') {
            return false;
        }

        if (is_uploaded_file($uploadName)) {

            // 有Xls和Xlsx格式两种
            $objReader = IOFactory::createReader('Xls');
            $objPHPExcel = $objReader->load($uploadName);  //$filename可以是上传的表格，或者是指定的表格
            $sheet = $objPHPExcel->getSheet(0);   //excel中的第一张sheet
            $highestRow = $sheet->getHighestRow();       // 取得总行数

            //循环读取excel表格，整合成数组。如果是不指定key的二维，就用$data[i][j]表示。
            for ($j = 2; $j <= $highestRow; $j++) {
                $data[$j-2] = [
                    'Name' => $objPHPExcel->getActiveSheet()->getCell("A" . $j)->getValue(),
                    'Phone' => $objPHPExcel->getActiveSheet()->getCell("B" . $j)->getValue(),
                    'Department' => $objPHPExcel->getActiveSheet()->getCell("C" . $j)->getValue(),
                    'Station' => $objPHPExcel->getActiveSheet()->getCell("F" . $j)->getValue(),
                    'Email' => $objPHPExcel->getActiveSheet()->getCell("G" . $j)->getValue(),
                    'CompanyId' => $objPHPExcel->getActiveSheet()->getCell("H" . $j)->getValue(),
                    'Location' => $objPHPExcel->getActiveSheet()->getCell("I" . $j)->getValue(),
                    'Positions' => $objPHPExcel->getActiveSheet()->getCell("D" . $j)->getValue(),
                    'CreatedTime' => Carbon::now(),
                ];
                $phone[] = $data[$j-2]['Phone'];
                $email[] = $objPHPExcel->getActiveSheet()->getCell("E" . $j)->getValue();
            }

            for ($i = 2; $i <= $highestRow; $i++) {
                $handelData[$i-2] = [
                    'Email' => $objPHPExcel->getActiveSheet()->getCell("E" . $i)->getValue(),
                    'Phone' => $objPHPExcel->getActiveSheet()->getCell("B" . $i)->getValue(),
                ];
            }

            //开启事务
            DB::beginTransaction();
            try {
                //插入Employee数据库
                DB::table('snets_emp_Employee')->insert($data);
                //查出插入数据库中的用户
                $user = DB::table('snets_emp_Employee')
                    ->select(['Id','Phone'])
                    ->whereIn('Phone',$phone)
                    ->get()
                    ->toArray();
                //查出上级的Id
                $leader = DB::table('snets_emp_Employee')
                    ->select(['Id','Email'])
                    ->whereIn('Email',$email)
                    ->get()
                    ->toArray();
                foreach ($user as $key => $value){
                    foreach ($handelData as $val){
                        if ($value->Phone == $val['Phone']){
                            $user[$key]->Email = $val['Email'];
                        }
                    }
                }
                $insertOffice = [];
                foreach ($user as  $v){
                    foreach ($leader as $va){
                        if ($v->Email == $va->Email){
                            $row['EmployeeId'] = $v->Id;
                            $row['LeaderId'] = $va->Id;
                            $row['CreatedTime'] = Carbon::now();
                            $insertOffice[] = $row;
                        }
                    }
                }
                //插入Office数据库
                DB::table('snets_emp_Office')->insert($insertOffice);
                //提交事务
                DB::commit();
                return redirect('admin/customerList');
            } catch (\Exception $e) {
                //事务回滚
                DB::rollBack();
                return false;
            }

        }

    }


    /**
     * 修改用户处理
     * @param Request $request
     * @return false|string
     */
    public function editCustomerAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'names' => 'required',
            'phone' => 'required',
            'department' => 'required',
            'station' => 'required',
            'positions' => 'required',
        ]);
        if ($validator->fails()) {
            $data['uses'] = -1;
            return json_encode($data);
        }
        $id = $request->input('employeeId');
        $data['Name'] = $request->input('names');
        $data['Phone'] = $request->input('phone');
        $data['Department'] = $request->input('department');
        $data['Station'] = $request->input('station');
        $data['Positions'] = $request->input('positions');
        $data['Email'] = $request->input('email');
        $data['CompanyId'] = $request->input('companyId');
        $data['Location'] = $request->input('locations');
        $data['ModifiedTime'] = Carbon::now();
        //开启事务
        DB::beginTransaction();
        try {
            //更新用户信息
            DB::table('snets_emp_Employee')->where('id','=',$id)->update($data);

            $office['LeaderId'] = $request->input('leaderId');
            $office['EmployeeId'] = $id ;
            $off = DB::table('snets_emp_Office')->where('EmployeeId','=',$id)->first();
            if($off == null){
                $office['CreatedTime'] = Carbon::now();
                DB::table('snets_emp_Office')->insert($office);
            }
            else{
                $office['ModifiedTime'] = Carbon::now();
                DB::table('snets_emp_Office')->where('EmployeeId','=',$id)->update($office);
            }

            //提交事务
            DB::commit();
            $data['uses'] = 1;
            return json_encode($data);
        } catch (\Exception $e) {
            //事务回滚
            DB::rollBack();
            $data['uses'] = -2;
            return json_encode($data);
        }
    }


    /**
     * 删除用户
     * @param int $id
     * @return false|string
     */
    public function deleteCustomer(int $id)
    {
        $result = DB::table('snets_emp_Employee')->where('Id', '=', $id)->update(['status' => 2]);
        if ($result) {
            $data['uses'] = 1;
            return json_encode($data);
        } else {
            $data['uses'] = 0;
            return json_encode($data);
        }
    }
}
