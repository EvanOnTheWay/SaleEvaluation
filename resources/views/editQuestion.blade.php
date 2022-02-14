@extends('layout')
@section('content')
    <span>
        <a style="width: 120px" class="btn btn-block btn-info" href="{{url('admin/option')}}?id={{$data->Id}}&title={{$data->Title}}">能力描述</a>
    </span>
    <br>
    <div class="card card-primary">
        <div class="card-body">
            <div class="form-group">
                <label for="exampleInputEmail1"><span style="color: red">*</span>问题</label>
                <input type="text" class="form-control" value="{{$data->Title}}" id="title" placeholder="">
                <input type="hidden" value="{{$data->Id}}" id="titleId">
            </div>
            <div class="card-footer">
                <button type="submit" id="sub_tn" class="btn btn-primary">提交</button>
                <a href="{{url('admin/evaluateList')}}" class="btn btn-primary">返回</a>
            </div>
        </div>
    </div>

    <script>


        //点击保存
        $('#sub_tn').click(function () {
            var titleId = $('#titleId').val();
            var title = $('#title').val();
           /* console.log(entryDes)
            return false;*/
            $.ajax({
                url: "/admin/editQuestionAction",
                dataType: "json",
                type: "POST",
                data: {
                    'titleId':titleId,
                    "title": title,
                },
                success: function (data) {
                    console.log(data);
                    if (data.uses == 1) {
                        layui.use('layer', function () {
                            var layer = layui.layer;
                            layer.open({
                                title: '信息提示'
                                ,content: '修改成功'
                            });
                        });
                    }
                    if (data.uses == -2) {
                        layui.use('layer', function () {
                            var layer = layui.layer;
                            layer.open({
                                title: '错误提示'
                                ,content: '修改失败'
                            });
                        });
                    }
                    if (data.uses == -1) {
                        layui.use('layer', function () {
                            var layer = layui.layer;
                            layer.open({
                                title: '错误提示'
                                ,content: '数据格式错误'
                            });
                        });
                    }
                }
            })
        })
    </script>

@endsection
