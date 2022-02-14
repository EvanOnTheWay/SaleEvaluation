@extends('layout')
@section('content')
    <a href="{{url('admin/ability')}}?id={{$data->Id}}" style="  width:80px; margin-top: 5.9px;margin-right: -20px"  class="btn btn-block btn-info">能力维度</a>
    <br>
    <div class="card card-primary">
        <div class="card-body">
            <div class="form-group">
                <label for="exampleInputEmail1"><span style="color: red">*</span>测评等级</label>
                <input type="text" style="width: 180px" class="form-control" value="{{$data->Title}}" id="title" placeholder="姓名">
                <input type="hidden" value="{{$data->Id}}" id="rankId">
            </div>
                <button type="submit" id="sub_tn" class="btn btn-primary">提交</button>

                <a   href="{{url('admin/evaluateList')}}" class="btn btn-primary">返回</a>
            </div>
        </div>
    </div>

    <script>

        //点击保存
        $('#sub_tn').click(function () {
            var title = $('#title').val();
            var rankId = $('#rankId').val();
            
            /*console.log(title);
            return false;*/
            $.ajax({
                url: "/admin/editEvaluateAction",
                dataType: "json",
                type: "POST",
                data: {
                    "title": title,
                    "rankId": rankId,
                },
                success: function (data) {
                    console.log(data);
                    if (data.uses == 1) {
                        location.href = "{{url('admin/evaluateList')}}";
                    }
                    if (data.uses == -2) {
                        layui.use('layer', function () {
                            var layer = layui.layer;
                            layer.msg('编辑失败');
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
