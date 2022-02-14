@extends('layout')


@section('content')

   {{--<button style="width: 120px" class="btn btn-block btn-info">导出测评报告</button>

   <br><br>--}}

   <button style="width: 160px" class="btn btn-block btn-info">导出自评报告</button>
   <br><br>
   <button style="width: 160px;" id="sub" class="layui-btn layui-btn-normal">导出他评报告</button>




    <script>
        $('.btn').click(function () {
            $.ajax({
                url: "/admin/exportUserReport/",
                dataType: "json",
                type: "get",
                success: function (data) {
                    if(data.code == 100){
                        window.location.href = data.data.filePath;
                    }else {
                        layui.use('layer', function(){
                            var layer = layui.layer;
                            layer.msg('下载失败，请稍后再试');
                        });
                    }
                }
            })
        })

        $('#sub').click(function () {
            $.ajax({
                url: "/admin/exportReport/",
                dataType: "json",
                type: "get",
                success: function (data) {
                    if(data.code == 100){
                        window.location.href = data.data.filePath;
                    }else {
                        layui.use('layer', function(){
                            var layer = layui.layer;
                            layer.msg('下载失败，请稍后再试');
                        });
                    }
                }
            })
        })

    </script>

@endsection


