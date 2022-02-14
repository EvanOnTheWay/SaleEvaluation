@extends('layout')
@section('content')
    <span>
        <a style="width: 120px" class="btn btn-block btn-info" href="{{url('admin/question')}}?id={{$data->Id}}&title={{$data->Title}}">问题</a>
    </span>
    <br>
<script type="text/javascript" src="/js/admin/ueditor.config.js"></script>
<!-- 编辑器源码文件 -->
<script type="text/javascript" src="/js/admin/ueditor.all.js"></script>
    <div class="card card-primary">
        <div class="card-body">
            <div class="form-group">
                <label for="exampleInputEmail1"><span style="color: red">*</span>能力词条</label>
                <input type="text" class="form-control" value="{{$data->Title}}" id="title" placeholder="能力词条">
                <input type="hidden" value="{{$data->Id}}" id="titleId">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail1"><span style="color: red">*</span>评分标准</label>
                <input type="text" class="form-control" value="{{$data->ScoreCriteria}}" id="score" placeholder="能力词条">

            </div>
            <div class="form-group">
                <label for="exampleInputEmail1"><span style="color: red">*</span>达标等级</label>
                <input type="text" class="form-control" value="{{$data->ArrivelLevel}}" id="level" placeholder="达标等级">

            </div>
            <div class="form-group">
                <label for="exampleInputEmail1"><span style="color: red">*</span>词条定义</label>
                <!-- 加载编辑器的容器 -->
                <script id="ueditorId_1" name="content" type="text/plain">

                </script>

                <!-- 实例化编辑器 -->
                <script type="text/javascript">
                    var ue1 = UE.getEditor('ueditorId_1');
                    var cnt = "{{str_replace(array("\r\n", "\r", "\n"), "", $data->Intro)}}";
                    ue1.ready(function() {
                        //设置编辑器的内容
                        ue1.setContent(cnt.replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&amp;/g, "&").replace(/&quot;/g, '"').replace(/&apos;/g, "'"));

                        //获取html内容
                    });
                    //console.log(ue);
                </script>
            </div>
            <div class="form-group">
                <label for="exampleInputEmail1">词条描述</label>

                <!-- 加载编辑器的容器 -->
                <script id="ueditorId_2" name="introduce" type="text/plain">

                </script>

                <!-- 实例化编辑器 -->
                <script type="text/javascript">
                    var ue2 = UE.getEditor('ueditorId_2');
                    var cnt1 = "{{str_replace(array("\r\n", "\r", "\n"), "", $data->EntryLevelDes)}}";
                    //console.log(cnt1);
                    ue2.ready(function() {
                        //设置编辑器的内容
                        ue2.setContent(cnt1.replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&amp;/g, "&").replace(/&quot;/g, '"').replace(/&apos;/g, "'"));
                    });
                    //console.log(ue);
                </script>
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
            var score = $('#score').val();
            var level = $('#level').val();
            var intro = ue1.getContent();
            var entryDes = ue2.getContent();
           /* console.log(entryDes)
            return false;*/
            $.ajax({
                url: "/admin/editEntryAction",
                dataType: "json",
                type: "POST",
                data: {
                    'titleId':titleId,
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
