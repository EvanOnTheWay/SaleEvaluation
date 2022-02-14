@extends('layout')
@section('content')
    <div class="card card-primary">
        <div class="card-body">
            <div class="form-group">
                <label for="exampleInputEmail1"><span style="color: red">*</span>姓名</label>
                <input type="text" class="form-control" value="{{$user->Name}}" id="employee_name" placeholder="姓名">
                <input type="hidden" value="{{$user->Id}}" id="employeeId">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail1"><span style="color: red">*</span>手机号</label>
                <input type="text" class="form-control" value="{{$user->Phone}}" id="phone" placeholder="手机号">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail1"><span style="color: red">*</span>部门</label>
                <input type="text" class="form-control" value="{{$user->Department}}" id="department" placeholder="部门">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail1"><span style="color: red">*</span>职位</label>
                <input type="text" class="form-control" value="{{$user->Station}}" id="station" placeholder="职位">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail1"><span style="color: red">*</span>职级</label>
                <input type="text" class="form-control" value="{{$user->Positions}}" id="positions" placeholder="职级">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail1">邮件</label>
                <input type="text" class="form-control" value="{{$user->Email}}" id="email" placeholder="邮件">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail1">员工号</label>
                <input type="text" class="form-control" value="{{$user->CompanyId}}" id="companyId" placeholder="员工号">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail1">地址</label>
                <input type="text" class="form-control" value="{{$user->Location}}" id="locations" placeholder="地址">
            </div>

            <div class="form-group">
                <label><span style="color: red">*</span>请选择上级</label>
                <select class="form-control select2" id="leaderId" style="width: 30%;">
                    <option value="0">请选择</option>
                    @foreach($leaders as $value)
                        <option value="{{$value['id']}}"
                                @if($value['select'] == $value['id']) selected @endif >{{$value['name']}}</option>
                    @endforeach

                </select>
            </div>
            <div class="card-footer">
                <button type="submit" id="sub_tn" class="btn btn-primary">提交</button>

                <a   href="{{url('admin/customerList')}}" class="btn btn-primary">返回</a>
            </div>
        </div>
    </div>

    <script>

        //点击保存
        $('#sub_tn').click(function () {
            var employeeId = $('#employeeId').val();
            var names = $('#employee_name').val();
            var phone = $('#phone').val();
            var department = $('#department').val();
            var station = $('#station').val();
            var positions = $('#positions').val();
            var email = $('#email').val();
            var companyId = $('#companyId').val();
            var locations = $('#locations').val();
            var leaderId = $("#leaderId option:selected").val();

            /*console.log(leaderId);
            return false;*/
            $.ajax({
                url: "/admin/editCustomerAction",
                dataType: "json",
                type: "POST",
                data: {
                    "employeeId": employeeId,
                    "names": names,
                    "phone": phone,
                    "department": department,
                    "station": station,
                    "positions": positions,
                    "email": email,
                    "companyId": companyId,
                    "leaderId": leaderId,
                    "locations": locations

                },
                success: function (data) {
                    console.log(data);
                    if (data.uses == 1) {
                        location.href = "{{url('admin/customerList')}}";
                    }
                    if (data.uses == -2) {
                        layui.use('layer', function () {
                            var layer = layui.layer;
                            layer.msg('添加失败');
                        });
                    }
                    if (data.uses == -1) {
                        layui.use('layer', function () {
                            var layer = layui.layer;
                            layer.msg('数据格式错误');
                        });
                    }
                }
            })
        })
    </script>

@endsection
