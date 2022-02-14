@extends('layout')
@section('content')


<script type="text/javascript" src="/js/admin/ueditor.config.js"></script>
<!-- 编辑器源码文件 -->
<script type="text/javascript" src="/js/admin/ueditor.all.js"></script>
    <div class="card card-primary">
        <div class="card-body">
            <div class="form-group">
                <label for="exampleInputEmail1"><span style="color: red">*</span>能力词条</label>
                <input type="text" class="form-control"  id="title" placeholder="能力词条">
                <input type="hidden" value="{{$pId}}" id="pId">
                <input type="hidden" value="{{$rankId}}" id="rankId">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail1"><span style="color: red">*</span>评分标准</label>
                <input type="text" class="form-control"  id="score" placeholder="评分标准">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail1"><span style="color: red">*</span>达标等级</label>
                <input type="text" class="form-control"  id="level" placeholder="达标等级">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail1"><span style="color: red">*</span>词条定义</label>

                <!-- 加载编辑器的容器 -->
                <script id="ueditorId_1" name="content" type="text/plain">

                </script>

                <!-- 实例化编辑器 -->
                <script type="text/javascript">
                    var ue1 = UE.getEditor('ueditorId_1');
                    //console.log(ue);
                </script>
            </div>
            <div class="form-group">
                <label for="exampleInputEmail1"><span style="color: red">*</span>词条描述</label>

                <!-- 加载编辑器的容器 -->
                <script id="ueditorId_2" name="introduce" type="text/plain">

                </script>

                <!-- 实例化编辑器 -->
                <script type="text/javascript">
                    var ue2 = UE.getEditor('ueditorId_2');
                    //console.log(ue);
                </script>
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
            var pId = $('#pId').val();
            var rankId = $('#rankId').val();
            var title = $('#title').val();
            var score = $('#score').val();
            var level = $('#level').val();
            var intro = ue1.getContent();
            var entryDes = ue2.getContent();

            /*console.log(intro);
            return false;*/
            $.ajax({
                url: "/admin/addEntryAction",
                dataType: "json",
                type: "POST",
                data: {
                    'pId':pId,
                    'rankId':rankId,
                    "title": title,
                    "score": score,
                    "level": level,
                    "intro": "<p>" + intro + "</p>",
                    "entryDes": "<p>" + entryDes + "</p>"
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
