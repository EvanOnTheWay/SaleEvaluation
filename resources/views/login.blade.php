<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>欢迎登录施乐辉销售测评管理系统</title>
    <link href="{{asset('admin/css/style.css')}}" rel="stylesheet" type="text/css" />
    <script language="JavaScript" src="{{asset('admin/js/jquery.js')}}"></script>
    <script src="{{asset('admin/js/cloud.js')}}" type="text/javascript"></script>
    <script src="{{asset('jquery-1.10.2.min.js')}}"></script>
    <link rel="stylesheet" href="{{asset('layui/css/layui.css')}}">
    <script src="{{asset('layui/layui.js')}}"></script>
    <style>
        img{
            cursor: pointer;
        }
    </style>
</head>

<body style="background-color:#1c77ac; background-image:url({{asset('admin/images/light.png')}}); background-repeat:no-repeat; background-position:center top; overflow:hidden;">

<div id="mainBody">
    <div id="cloud1" class="cloud"></div>
    <div id="cloud2" class="cloud"></div>
</div>

<div class="logintop">
    <span>欢迎登录施乐辉销售测评系统后台管理界面平台</span>
</div>

<div class="loginbody">
    <span class="systemlogo"></span>
    <div class="loginbox loginbox1">

        <ul>
            <li><input name="username" id="username" type="text" value="" class="loginuser" placeholder="用户名"  /></li>
            <li><input name="password" type="password" id="password" value="" class="loginpwd" placeholder="密　码" /></li>
            <li>
                <input name="" type="submit" id="sub_tn" class="loginbtn" value="登录"   />
            </li>
        </ul>
    </div>
</div>

</body>
<script language="javascript">
    $(function(){
        $('.loginbox').css({'position':'absolute','left':($(window).width()-692)/2});
        $(window).resize(function(){
            $('.loginbox').css({'position':'absolute','left':($(window).width()-692)/2});
        })
    });
</script>
</html>
<script>

    //点击登录
    $('#sub_tn').click(function () {
        var username = $('#username').val();
        var password = $('#password').val();
        $.get("{{url('/loginAction')}}",{username:username,password:password},function (data) {
            if (data.uses == -1){
                layui.use('layer', function(){
                    var layer = layui.layer;
                    layer.msg('用户名或密码不正确');
                });
            }
            if (data.uses == 1){
                location.href="{{url('admin/index')}}";
            }
        },'json');
    })
</script>
