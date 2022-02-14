<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SN后台管理系统</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="/css/admin/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="/css/admin/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="/css/admin/ionicons.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="/css/admin/dataTables.bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="/css/admin/AdminLTE.min.css">

    {{--<!-- Theme style -->
    <link rel="stylesheet" href="/css/admin/css/adminlte.min.css">--}}

    <!-- AdminLTE Skins. We have chosen the skin-blue for this starter
          page. However, you can choose any other skin. Make sure you
          apply the skin class to the body tag so the changes take effect. -->
    <link rel="stylesheet" href="/css/admin/skin-blue.min.css">
    <link rel="stylesheet" href="{{asset('layui/css/layui.css')}}">
    <script src="{{asset('layui/layui.js')}}"></script>


    <!-- Ionicons -->
    <!-- Font Awesome -->
    {{--<link rel="stylesheet" href="/css/admin/all.min.css">--}}

    <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="/css/admin/_all-skins.min.css">
    <link rel="stylesheet" href="/css/admin/select2.min.css">
    <link rel="stylesheet" href="/css/admin/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="/css/admin/daterangepicker.css">


    <!-- summernote -->
    <link rel="stylesheet" href="/css/admin/summernote-bs4.css">


    {{--<link rel="stylesheet" href="/css/admin/all.min.css">--}}

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>

    <![endif]-->

    <!-- REQUIRED JS SCRIPTS -->

    <!-- jQuery 3 -->
    <script src="/js/admin/jquery.min.js"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="/js/admin/bootstrap.min.js"></script>
    <script src="/js/admin/jquery.dataTables.min.js"></script>
    <script src="/js/admin/dataTables.bootstrap.min.js"></script>
    <!-- SlimScroll -->
    <script src="/js/admin/jquery.slimscroll.min.js"></script>
    <!-- FastClick -->
    <script src="/js/admin/fastclick.js"></script>
    <!-- AdminLTE App -->
    <script src="/js/admin/adminlte.min.js"></script>

    <script src="/js/admin/select2.full.min.js"></script>
    <script src="/js/admin/bootstrap.bundle.min.js"></script>

    <script src="/js/admin/jquery.bootstrap-duallistbox.min.js"></script>
    <script src="/js/admin/bootstrap-switch.min.js"></script>
    <script src="/js/admin/moment.min.js"></script>
    <script src="/js/admin/jquery.inputmask.bundle.min.js"></script>
    <script src="/js/admin/daterangepicker.js"></script>
    <script src="/js/admin/bootstrap-datepicker.min.js"></script>
    <!-- Summernote -->
    <script src="/js/admin/summernote-bs4.min.js"></script>
    <!-- AdminLTE for demo purposes -->
    {{--<script src="/js/admin/demo.js"></script>--}}
    <!-- Google Font -->
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

</head>
<!--
BODY TAG OPTIONS:
=================
Apply one or more of the following classes to get the
desired effect
|---------------------------------------------------------|
| SKINS         | skin-blue                               |
|               | skin-black                              |
|               | skin-purple                             |
|               | skin-yellow                             |
|               | skin-red                                |
|               | skin-green                              |
|---------------------------------------------------------|
|LAYOUT OPTIONS | fixed                                   |
|               | layout-boxed                            |
|               | layout-top-nav                          |
|               | sidebar-collapse                        |
|               | sidebar-mini                            |
|---------------------------------------------------------|
-->
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

    <!-- Main Header -->
    <header class="main-header">

        <!-- Logo -->
        <a href="#" class="logo">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini">SN</span>
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg">SN</span>
        </a>

        <!-- Header Navbar -->
        <nav class="navbar navbar-static-top" role="navigation">
            <!-- Sidebar toggle button-->

            <!-- Navbar Right Menu -->
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <!-- User Account Menu -->
                    <li class="dropdown user user-menu">
                        <!-- Menu Toggle Button -->
                        <a href="{{url('/loginOut')}}" >
                            <!-- The user image in the navbar-->
                            <!-- hidden-xs hides the username on small devices so only the image appears. -->
                            欢迎您，{{$username}} &nbsp;&nbsp; <i class="fa fa-sign-out" ></i>

                        </a>
                    </li>
                    <!-- Control Sidebar Toggle Button -->
                </ul>
            </div>
        </nav>
    </header>
    <!-- Left side column. contains the logo and sidebar -->
    <aside class="main-sidebar">

        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">


            <!-- Sidebar Menu -->
            <ul class="sidebar-menu" data-widget="tree">
                <li class="header">NAVIGATION</li>
                <!-- Optionally, you can add icons to the links -->
                <li ><a href="{{url('admin/index')}}"> <i class="fa fa-home"></i> <span <?php echo "style='color: #fff'"; ?> >后台首页</span></a></li>
                <li class="treeview active menu-open">
                    <a href="#"><i class="fa fa-users"></i> <span>用户管理</span>
                        <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
                    </a>
                    <ul class="treeview-menu">
                        <li><a href="{{url('admin/customerList')}}" <?php if ($url == "customerList"){ echo "style='color: #fff'";} ?>>用户列表</a></li>
                    </ul>

                </li>
                <li class="treeview menu-open" style="height: auto;">
                    <a href="#"><i class="fa fa-bars"></i> <span>测评管理</span>
                        <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
                    </a>
                    <ul class="treeview-menu" style="display: block;">
                        <li><a href="{{url('admin/evaluateList')}}"<?php if ($url == "evaluateList"){ echo "style='color: #fff'";} ?>>测评列表</a></li>
                    </ul>
                </li>

                <li class="treeview menu-open" style="height: auto;">
                    <a href="#"><i class="fa fa-bars"></i> <span>统计管理</span>
                        <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
                    </a>
                    <ul class="treeview-menu" style="display: block;">
                        <li><a href="{{url('admin/report')}}"<?php if ($url == "report"){ echo "style='color: #fff'";} ?>>测评报告</a></li>
                        <li><a href="{{url('admin/chooseIndex')}}"<?php if ($url == "choose"){ echo "style='color: #fff'";} ?>>筛选导出</a></li>
                        <li><a href="{{url('admin/chooseOption')}}"<?php if ($url == "chooseOption"){ echo "style='color: #fff'";} ?>>导出选项</a></li>
                    </ul>
                </li>

            </ul>
            <!-- /.sidebar-menu -->
        </section>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <p style="float: right">
                当前位置：<a href="#">首页</a>>{{$title}}
            </p>

        </section>
        <section class="content container-fluid">

            <!--------------------------
              | Your Page Content Here |
              -------------------------->
            @yield('content')

        </section>

        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Main Footer -->
    <footer class="main-footer">
        <!-- To the right -->
        <div class="pull-right hidden-xs">
            SN后台管理系统
        </div>
    </footer>




</div>

</body>


</html>
