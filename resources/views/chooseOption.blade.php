@extends('layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box">

                <!-- 筛选查询 -->
                <div class="box-header with-border">
                    <div class="pull-left">
                        <form class="layui-form user-form-key" method="get" enctype="multipart/form-data" action="{{url('admin/chooseOption')}}" >
                            <button type="submit" class="btn btn-info" style="margin-left: 15px">搜索</button>
                            <a href="{{url('admin/chooseOption')}}" class="btn btn-info" style="background: #009688;">重置</a>
                            <input type="text" name="search_info" value="{{$search_info}}" class="layui-input" placeholder="请输入关键字，支持题等级、词条、题目、选项标题" style="width: 380px; float: left;">
                        </form>
                    </div>
                </div>

                <div class="box-body">
                    <table id="example2" class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>选项等级</th>
                            <th>能力词条</th>
                            <th>题目名称</th>
                            <th>选项名称</th>
                            <th>导出时是否显示</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($data as $value)
                            <tr>
                                <td>{{$value->RankTitle}}</td>
                                <td>{{$value->AbilityTitle}}</td>
                                <td>{{$value->QuestionTitle}}</td>
                                <td>{{$value->OptionTitle}}</td>
                                @if($value->IsDisplay == 1)
                                    <td style="color: green; font-weight: bold">是</td>
                                @else
                                    <td style="color: red; font-weight: bold">否</td>
                                @endif
                                <td style="cursor:pointer; width:150px ;">
                                    @if($value->IsDisplay == 1)
                                        <a data-id="{{$value->Id}}" class="click-status btn btn-block btn-danger">点击隐藏 </a>
                                    @else
                                        <a data-id="{{$value->Id}}" class="click-status btn btn-block btn-info">点击显示 </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                        @endforelse
                        </tbody>
                    </table>
                    {{ $data->appends(['search_info' => $search_info])->links('commons.paginate',['items'=>$data]) }}
                </div>
            </div>
        </div>
    </div>

    <script>
        layui.use(['layer', 'jquery', 'form'], function () {
            var $ = layui.jquery;
            var layer = layui.layer;

            // 更改显示状态
            $('.click-status').click(function () {
                var optionId = $(this).attr('data-id');

                $.ajax({
                    url: "/saler/index.php/admin/chooseOptionStatus/" + optionId,
                    dataType: "json",
                    type: "get",
                    success: function (data) {
                        if (data.uses == 1) {
                            layer.msg("操作成功", {
                                time: 1500,
                                end: function () {
                                    window.location.reload();
                                }
                            })
                        }
                        if (data.uses == 0) {
                            layer.msg("操作失败，请联系管理员！");
                        }
                    }
                })
            });
        });
    </script>

@endsection


