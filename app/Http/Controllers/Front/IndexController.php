<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\ResponseWrapper;
use App\Services\IndexService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class IndexController extends Controller
{
    /**
     * 首页信息
     * @param Request $request
     * @return array
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Id' => 'required',

        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid();
        }
        $userid = $request->input('Id');
        $res = IndexService::newInstance()->getUser($userid);

        return ResponseWrapper::success($res);
    }

    /**
     * 获取个人信息
     * @param Request $request
     * @return array
     */
    public function getUserInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Id' => 'required',

        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid();
        }

        $userid = $request->input('Id');

        $res = IndexService::newInstance()->getUserInfo($userid);

        return ResponseWrapper::success($res);
    }

    /**
     * 我的下属
     * @param Request $request
     * @return array
     */
    public function myBranch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Id' => 'required',

        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid();
        }
        $id = $request->input('Id');
        $res = IndexService::newInstance()->getBranch($id);
        return ResponseWrapper::success($res);
    }

    public function image()
    {
        $file = scandir(public_path() . '/standard');
        foreach ($file as $key => $value) {
            if ($value == '.' || $value == '..') {
                unset($file[$key]);
            } else {
                $file[$key] = basename($value, '.png');
            }
        }

        unset($key, $value);

        asort($file);

        foreach ($file as $key => $value) {
            $file[$key] = env('APP_URL') . '/' . 'standard/' . $value . '.png';
        }

        $response = array_values($file);

        return ResponseWrapper::success($response);
    }

    public function help()
    {
        $file = scandir(public_path() . '/help');
        foreach ($file as $key => $value) {
            if ($value == '.' || $value == '..') {
                unset($file[$key]);
            } else {
                $file[$key] = basename($value, '.png');
            }
        }

        unset($key, $value);

        asort($file);

        foreach ($file as $key => $value) {
            $file[$key] = env('APP_URL') . '/' . 'help/' . $value . '.png';
        }

        $response = array_values($file);

        return ResponseWrapper::success($response);
    }

    /**
     * 202105 对接第三方用户信息：新增（编辑）用户接口
     * @param Request $request
     * @return array
     */
    public function UserUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Name' => 'required',
            'Phone' => 'required',
            'Department' => 'required',
            'Station' => 'required',
            'Positions' => 'required',
            'Email' => 'required'
        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid();
        }

        // LeaderEmail不为空，则需要进行上级是否存在验证
        if (!empty($request->input('LeaderEmail'))) {
            $LeaderId = DB::table('snets_emp_Employee')
                ->where('Email', '=', $request->input('LeaderEmail'))
                ->where('Status', '<>', 2)
                ->value('Id');
            if (empty($LeaderId)) {
                return response()->json(['code' => 10002, 'message' => '此人的领导在销售测评系统中未查询到！']);
            }
        }

        // 此用户在销售测评系统中是否存在
        $UserId = DB::table('snets_emp_Employee')
            ->where('Email', '=', $request->input('Email'))
            ->where('Status', '<>', 2)
            ->value('Id');

        // 基础入库数据
        $insert['Name'] = $request->input('Name');
        $insert['Phone'] = $request->input('Phone');
        $insert['Department'] = $request->input('Department');
        $insert['Station'] = $request->input('Station');
        $insert['Positions'] = $request->input('Positions');
        $insert['Email'] = $request->input('Email');
        $insert['CompanyId'] = $request->input('CompanyId');
        $insert['Location'] = $request->input('Location');

        DB::beginTransaction();
        try {
            // 新增用户至销售测评系统
            if (empty($UserId)) {
                $insert['Status'] = 0;
                $insert['CreatedTime'] = Carbon::now();
                $employeeId = DB::table('snets_emp_Employee')->insertGetId($insert);
            // 编辑用户至销售测评系统
            } else {
                $insert['ModifiedTime'] = Carbon::now();
                DB::table('snets_emp_Employee')->where('id', '=', $UserId)->update($insert);
                $employeeId = $UserId;
            }
            // LeaderEmail不为空，数据写入关联表
            if (!empty($request->input('LeaderEmail'))) {
                $office['LeaderId'] = $LeaderId;
                $office['EmployeeId'] = $employeeId;
                $off = DB::table('snets_emp_Office')->where('EmployeeId', '=', $employeeId)->first();

                if ($off == null || empty($off)) {
                    $office['CreatedTime'] = Carbon::now();
                    DB::table('snets_emp_Office')->insert($office);
                } else {
                    $office['ModifiedTime'] = Carbon::now();
                    DB::table('snets_emp_Office')->where('EmployeeId', '=', $employeeId)->update($office);
                }
            }

            DB::commit();
            return response()->json(['code' => 200, 'message' => '数据写入成功！']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 10003, 'message' => '数据写入失败！']);
        }
    }

    /**
     * 202105 对接第三方用户信息：删除用户接口
     * @param Request $request
     * @return array
     */
    public function UserStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Email' => 'required'
        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid();
        }

        // 此用户在销售测评系统中是否存在
        $UserId = DB::table('snets_emp_Employee')
            ->where('Email', '=', $request->input('Email'))
            ->where('Status', '<>', 2)
            ->value('Id');

        if (empty($UserId)) {
            return response()->json(['code' => 10004, 'message' => '此人在销售测评系统中未查询到！']);
        } else {
            $update['Status'] = 2;
            $update['ModifiedTime'] = Carbon::now();
            $result = DB::table('snets_emp_Employee')->where('id', '=', $UserId)->update($update);

            if ($result) {
                return response()->json(['code' => 200, 'message' => '用户删除成功！']);
            } else {
                return response()->json(['code' => 10005, 'message' => '用户删除失败！']);
            }
        }
    }

    /**
     * 202105 对接第三方用户信息：定时更新所有用户信息接口
     * @param Request $request
     * @return array
     */
    public function UserUpdateAll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'UserList' => 'required'
        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid();
        }

        // TODO:: 设置程序执行最大时间，为20分钟
        set_time_limit(1200);

        // TODO:: 遍历用户集合，逐条进行同步验证
        foreach ($request->input('UserList') as $key => $value) {

            // 写入员工表基础数据
            $insert['Name'] = $value['Name'];
            $insert['Phone'] = $value['Phone'];
            $insert['Department'] = $value['Department'];
            $insert['Station'] = $value['Station'];
            $insert['Positions'] = $value['Positions'];
            $insert['Email'] = $value['Email'];
            $insert['CompanyId'] = $value['CompanyId'];
            $insert['Location'] = $value['Location'];

            // 当前用户的ID
            $UserId = DB::table('snets_emp_Employee')
                ->where('Email', '=', $value['Email'])
                ->where('Status', '<>', 2)
                ->value('Id');

            // TODO:: 当前用户不存在，则新增
            if (empty($UserId)) {
                $insert['Status'] = 0;
                $insert['CreatedTime'] = Carbon::now();
                $employeeId = DB::table('snets_emp_Employee')->insertGetId($insert);

                if ($employeeId < 0) {
                    $insert_log['HaveLeader'] = 2;
                    $insert_log['Intro'] = "同步新增" . $value['Name'] . "信息失败，基础信息写入数据库失败！";
                } else {
                    // LeaderEmail不为空，则进行是否写入关联表的验证
                    if (!empty($value['LeaderEmail'])) {
                        $LeaderId = DB::table('snets_emp_Employee')
                            ->where('Email', '=', $value['LeaderEmail'])
                            ->where('Status', '<>', 2)
                            ->value('Id');

                        // 如果销售测评系统能够查询到此用户领导信息，则写入至关联表
                        if (!empty($LeaderId)) {
                            $office['LeaderId'] = $LeaderId;
                            $office['EmployeeId'] = $employeeId;
                            $off = DB::table('snets_emp_Office')->where('EmployeeId', '=', $employeeId)->first();

                            // 判断关联表中是否已存在此用户的关联数据
                            if ($off == null || empty($off)) {
                                $office['CreatedTime'] = Carbon::now();
                                $result = DB::table('snets_emp_Office')->insert($office);
                            } else {
                                $office['ModifiedTime'] = Carbon::now();
                                $result = DB::table('snets_emp_Office')->where('EmployeeId', '=', $employeeId)->update($office);
                            }
                            if ($result) {
                                $insert_log['Intro'] = "同步新增" . $value['Name'] . "信息成功，同步领导关联表成功！";
                            } else {
                                $insert_log['Intro'] = "同步新增" . $value['Name'] . "信息成功，同步领导关联表失败！";
                            }

                        // 如果销售测评系统不能查询到此用户领导信息，无需写入关联表
                        } else {
                            $insert_log['Intro'] = "同步新增" . $value['Name'] . "信息成功，但未能根据领导邮箱查询到领导信息！";
                        }
                        $insert_log['HaveLeader'] = 1;

                    // LeaderEmail为空，无需写入关联表
                    } else {
                        $insert_log['HaveLeader'] = 0;
                        $insert_log['Intro'] = "同步新增" . $value['Name'] . "信息成功，此用户没有领导！";
                    }
                }

            // TODO:: 当前用户已存在，则编辑
            } else {
                $insert['ModifiedTime'] = Carbon::now();
                $res_update = DB::table('snets_emp_Employee')->where('id', '=', $UserId)->update($insert);

                if ($res_update == false) {
                    $insert_log['HaveLeader'] = 2;
                    $insert_log['Intro'] = "同步编辑" . $value['Name'] . "信息失败，基础信息写入数据库失败！";
                } else {
                    // LeaderEmail不为空，则进行是否写入关联表的验证
                    if (!empty($value['LeaderEmail'])) {
                        $LeaderId = DB::table('snets_emp_Employee')
                            ->where('Email', '=', $value['LeaderEmail'])
                            ->where('Status', '<>', 2)
                            ->value('Id');

                        // 如果销售测评系统能够查询到此用户领导信息，则写入至关联表
                        if (!empty($LeaderId)) {
                            $office['LeaderId'] = $LeaderId;
                            $office['EmployeeId'] = $UserId;
                            $off = DB::table('snets_emp_Office')->where('EmployeeId', '=', $UserId)->first();

                            // 判断关联表中是否已存在此用户的关联数据
                            if ($off == null || empty($off)) {
                                $office['CreatedTime'] = Carbon::now();
                                $result = DB::table('snets_emp_Office')->insert($office);
                            } else {
                                $office['ModifiedTime'] = Carbon::now();
                                $result = DB::table('snets_emp_Office')->where('EmployeeId', '=', $UserId)->update($office);
                            }
                            if ($result) {
                                $insert_log['Intro'] = "同步编辑" . $value['Name'] . "信息成功，同步领导关联表成功！";
                            } else {
                                $insert_log['Intro'] = "同步编辑" . $value['Name'] . "信息成功，同步领导关联表失败！";
                            }

                        // 如果销售测评系统不能查询到此用户领导信息，无需写入关联表
                        } else {
                            $insert_log['Intro'] = "同步编辑" . $value['Name'] . "信息成功，但未能根据领导邮箱查询到领导信息！";
                        }
                        $insert_log['HaveLeader'] = 1;

                    // LeaderEmail为空，无需写入关联表
                    } else {
                        $insert_log['HaveLeader'] = 0;
                        $insert_log['Intro'] = "同步编辑" . $value['Name'] . "信息成功，此用户没有领导！";
                    }
                }
            }

            // TODO:: 此条同步信息写入日志
            $insert_log['Name'] = $value['Name'];
            $insert_log['Email'] = $value['Email'];
            $insert_log['CreatedTime'] = Carbon::now();

            DB::table('snets_emp_Log')->insertGetId($insert_log);
        }

        return response()->json(['code' => 200, 'message' => '数据同步成功！']);
    }
}
