@extends('layout')
@section('content')
<style>
    .layui-form-item label {
        text-align: left;
        padding-left: 0px;
        width: 85px;
    }
    .main-footer {
        height: 50px;
    }
    .layui-input-block {
        margin-left: 85px;
    }
    .department-list {
        float: left;
        width: 300px;
    }
    .layui-form-checkbox i {
        height: 30px;
    }
    .time-select {
        float: left;
        width: 290px;
        margin-right: 10px;
    }
</style>

<form class="layui-form" enctype="multipart/form-data" autocomplete="off">
    <div class="card-body">
        <div class="layui-form-item" style="border-bottom: 1px solid #3c8dbc;">
            <b style="color: #3c8dbc; margin-bottom: 10px; display: block; font-size: 16px; letter-spacing: 1px;">
                导出测评报告
                <a style="font-size: 12px; color: red;">（所有选项均必填）</a>
            </b>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">报告类型：</label>
            <div class="layui-input-block">
                <select name="report_type">
                    <option value="2">他评报告</option>
                    <option value="1">自评报告</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">部门选择：</label>
            <div class="layui-input-block">
                @foreach($department_info as $key => $value)
                    <div class="department-list">
                        <input type="checkbox" name="department[]" title="{{$value->Department}}" value="{{$value->Department}}" lay-verify="required">
                    </div>
                @endforeach
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">是否达标：</label>
            <div class="layui-input-block">
                <select name="is_standard">
                    <option value="1">未达标</option>
                    <option value="2">已达标</option>
                    <option value="3">已超标</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">职级选择：</label>
            <div class="layui-input-block">
                <select name="rank_id" lay-filter="rank_id">
                    @foreach($level_info as $key => $value)
                        <option value="{{$value->Id}}">{{$value->Title}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">能力词条：</label>
            <div class="layui-input-block">
                <select name="ability" class="ability-option">
                    @foreach($ability_info as $key => $value)
                        <option value="{{$value->Id}}">{{$value->Title}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">日期范围：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input time-select" id="time_start" name="time_start" placeholder="请选择查询起始时间">
                <input type="text" class="layui-input time-select" id="time_end" name="time_end" placeholder="请选择查询截止时间">
            </div>
        </div>
        <div class="layui-form-item">
            <button class="layui-btn layui-btn-normal" lay-submit lay-filter="formDemo">导出报告</button>
        </div>
    </div>
</form>

<!-- 日期选择 -->
<script>
    layui.use('laydate', function () {
        var laydate = layui.laydate;
        laydate.render({
            elem: '#time_start'
            , type: 'datetime'
            , done: function (value) {
                $('#time_start').attr("value", value);
            }
        });
        laydate.render({
            elem: '#time_end'
            , type: 'datetime'
            , done: function (value) {
                $('#time_end').attr("value", value);
            }
        });
    });
</script>

<!-- 监听词条 -->
<script>
    layui.use('form', function () {
        var form = layui.form;

        form.on('select(rank_id)', function (data) {
            var rank_id = data.value;

            $.ajax({
                url: "{{'chooseAbility'}}",
                async: false,
                type: "POST",
                dataType: "json",
                data: {
                    rank_id: rank_id
                },
                success: function (res) {
                    var option_str = '';
                    for (var i = 0; i < res.data.length; i++) {
                        option_str += "<option value='" + res.data[i].Id + "'>" + res.data[i].Title +"</option>";
                    }
                    $(".ability-option").html(option_str);
                    form.render('select');
                }
            });
        });

        // 表单提交
        form.on('submit(formDemo)', function (data) {
            $.ajax({
                url: "{{'chooseExport'}}",
                async: false,
                type: "GET",
                dataType: "json",
                data: data.field,
                success: function (res) {
                    if (res.code == 201) {
                        layer.msg(res.message, {icon: 5, time: 1500, shift: 6});
                    }
                    if (res.code == 200) {
                        window.location.href = res.data.filePath;
                    }
                }
            });
            return false;
        });
    });
</script>
@endsection


