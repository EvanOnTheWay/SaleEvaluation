@extends('layout')
@section('content')


    <div class="card card-primary">
        <div class="card-body">
            <div class="form-group">
                <label for="exampleInputEmail1"><span style="color: red">*</span>问题</label>
                <input type="text" class="form-control"  id="title" placeholder="能力词条">
                <input type="hidden" value="{{$abilityId}}" id="abilityId">
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
            var abilityId = $('#abilityId').val();
            var title = $('#title').val();
            $.ajax({
                url: "/admin/addQuestionAction",
                dataType: "json",
                type: "POST",
                data: {
                    'abilityId':abilityId,
                    "title": title,
                },
                success: function (data) {
                    console.log(data);
                    if (data.uses == 1) {
                        layui.use('layer', function () {
                            var layer = layui.layer;
                            layer.open({
                                title: '信息提示'
                                ,content: '添加成功'
                            });
                        });
                    }
                    if (data.uses == -2) {
                        layui.use('layer', function () {
                            var layer = layui.layer;
                            layer.open({
                                title: '错误提示'
                                ,content: '添加失败'
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
