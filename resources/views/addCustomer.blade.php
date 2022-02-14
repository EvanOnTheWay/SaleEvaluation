@extends('layout')
@section('content')
    <form action="/admin/import" method="post" enctype="multipart/form-data">
        <label for="exampleInputEmail1">一键导入</label>
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="file" name="customer_excel" id="exc">
        <br>
        <input type="submit" value="提交" disabled class="layui-btn">
    </form>
    <br><br>
    <div class="card card-primary">
        <div class="card-body">
            <div class="form-group">
                <label for="exampleInputEmail1"><span style="color: red">*</span>姓名</label>
                <input type="text" class="form-control" id="employee_name" placeholder="姓名">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail1"><span style="color: red">*</span>手机号</label>
                <input type="text" class="form-control" id="phone" placeholder="手机号">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail1"><span style="color: red">*</span>部门</label>
                <input type="text" class="form-control" id="department" placeholder="部门">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail1"><span style="color: red">*</span>职位</label>
                <input type="text" class="form-control" id="station" placeholder="职位">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail1"><span style="color: red">*</span>职级</label>
                <input type="text" class="form-control" id="positions" placeholder="职级">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail1">邮件</label>
                <input type="text" class="form-control" id="email" placeholder="邮件">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail1">员工号</label>
                <input type="text" class="form-control" id="companyId" placeholder="员工号">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail1">地址</label>
                <input type="text" class="form-control" id="locations" placeholder="地址">
            </div>

            <div class="form-group">
                <label><span style="color: red">*</span>请选择上级</label>
                <select class="form-control select2" id="leaderId" style="width: 30%;">
                    <option value="0">请选择</option>
                    @foreach($data as $value)
                    <option value="{{$value->Id}}">{{$value->Name}}</option>
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


        $('#exc').change(function(){
            var File = $("#exc")[0].files[0];
            if(!File){
                $('.layui-btn').attr('disabled', true);
            } else {
                $('.layui-btn').attr('disabled', false);
            }
        });

        //点击保存
        $('#sub_tn').click(function () {
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
                url: "/admin/addCustomerAction",
                dataType: "json",
                type: "POST",
                data: {
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
