@extends('layout')


@section('content')
    <span style="float: left">
        <a style="width: 60px" class="btn btn-block btn-info" href="{{url('admin/addCustomer')}}">新增</a>
    </span>
    <span style="float: right;margin-right: 620px">
        <a style="width: 120px" class="btn btn-block btn-info" href="{{url('http://weclientent.smith-nephew.com.cn/saler/demo/Template.xls')}}">模板下载</a>
    </span>
    <form action="/admin/customerList/" method="get">

            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="text" class="form-control" autocomplete="off" name="title" placeholder="姓名" style="width: 200px; ">
            <br>
            <input type="submit" class="layui-btn" value="搜索" id="sel"  style="width: 70px;margin-left: 200px;margin-top: -80px">
    </form>
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <!-- /.box-header -->
                <div class="box-body">
                    <table id="example2" class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>姓名</th>
                            <th>手机号</th>
                            <th>部门</th>
                            <th>职位</th>
                            <th>职级</th>
                            <th>是否绑定</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($data as $value)
                            <tr>
                                <td>{{$value->Name}}</td>
                                <td>{{$value->Phone}}</td>
                                <td>{{$value->Department}}</td>
                                <td>{{$value->Station}}</td>
                                <td>{{$value->Positions}}</td>
                                <td @if($value->Status == '已绑定')  style="color: green" @elseif($value->Status == '未绑定') style="color: #f00" @endif  class="status">
                                    {{$value->Status}}
                                </td>
                                <td style="cursor:pointer;width: 150px">
                                    <a href="{{url('admin/editCustomer')}}?id={{$value->Id}}" style=" width:60px; float: left;margin-top: 5.9px;margin-right: -20px"  class="btn btn-block btn-info">编辑</a>
                                    <span value="{{$value->Id}}" style="width: 60px;float: right; margin-top: 5.9px;margin-left: -20px"  class="del btn btn-block btn-danger">删除 </span>
                                </td>
                            </tr>
                        @empty
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col -->
    </div>



    <script>
        $(function () {

            $('#example2').DataTable({
                'paging': true,
                'lengthChange': false,
                'searching': false,
                'ordering': false,
                'info': true,
                'autoWidth': false,
                'pagingType': 'full_numbers',
                //隐藏数量列
                "oLanguage": { //国际化配置

                    "sProcessing": "正在获取数据，请稍后...",

                    "sLengthMenu": "显示 _MENU_ 条",

                    "sZeroRecords": "没有您要搜索的内容",

                    "sInfo": "从 _START_ 到  _END_ 条记录 总记录数为 _TOTAL_ 条",

                    "sInfoEmpty": "记录数为0",

                    "sInfoFiltered": "(全部记录数 _MAX_ 条)",

                    "sInfoPostFix": "",

                    "sSearch": "搜索",

                    "sUrl": "",

                    "oPaginate": {

                        "sFirst": "第一页",

                        "sPrevious": "上一页",

                        "sNext": "下一页",

                        "sLast": "最后一页"
                    }
                }

            })});
        //删除用户
        $('.del').click(function () {
            var _this = $(this);
            var id = $(this).attr('value');
            $.ajax({
                url: "/admin/deleteCustomer/" + id,
                dataType: "json",
                type: "get",
                success: function (data) {
                    if (data.uses == 1) {
                        layui.use('layer', function () {
                            var layer = layui.layer;
                            layer.open({
                                title: '信息提示'
                                ,content: '删除成功'
                            });
                        });
                        _this.parents('tr').remove();
                    }
                    if (data.uses == 0) {
                        layui.use('layer', function () {
                            var layer = layui.layer;
                            layer.open({
                                title: '信息提示'
                                ,content: '删除失败'
                            });
                        });
                    }
                }
            })
        })


    </script>

@endsection

