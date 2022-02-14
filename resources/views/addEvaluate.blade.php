@extends('layout')
@section('content')
    <div class="card card-primary">
        <div class="card-body">
            <div class="form-group">
                <label for="exampleInputEmail1"><span style="color: red">*</span>测评等级</label>
                <input type="text" class="form-control" id="title" placeholder="测评等级">
            </div>
            <div class="card-footer">
                <button type="submit" id="sub_tn" class="btn btn-primary">提交</button>
                <a   href="{{url('admin/evaluateList')}}" class="btn btn-primary">返回</a>
            </div>
        </div>
    </div>

    <script>

        //点击保存
        $('#sub_tn').click(function () {
            var title = $('#title').val();
            /*console.log(title);
            return false;*/
            $.ajax({
                url: "/admin/addEvaluateAction",
                dataType: "json",
                type: "POST",
                data: {
                    "title": title,
                },
                success: function (data) {
                    console.log(data);
                    if (data.uses == 1) {
                        location.href = "{{url('admin/evaluateList')}}";
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
