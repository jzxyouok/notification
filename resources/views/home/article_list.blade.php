@extends('layouts.app')

@section('title', '我的文章')

@section('link')
    @parent
    <link rel="stylesheet" href="/vendor/data-table/media/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="/stylesheets/css/style.css">
@endsection

@section('breadcrumbs')
    <li navValue="nav_4"><i class="fa fa-home" aria-hidden="true"></i><a href="/">我的文章</a></li>
    <li navValue="nav_4_1"><a href="/admin/task/page/">全部文章</a></li>
@endsection

@section('content_body')
    <div class="row animated fadeInRight">
        <div class="col-sm-12">
            <h4 class="section-subtitle"><b>我的文章</b></h4>
            <div class="panel">
                <div class="panel-content">
                    <form method="get" action="{{ route('admin_article_search') }}">
                        <div class="row pt-md">
                            <div class="form-group col-sm-9 col-lg-10">
                                            <span class="input-with-icon">
                                                <input type="text" class="form-control" name="keyword" id="lefticon" placeholder="输入文章标题关键字...">
                                                <input type="hidden" name="page" value="1">
                                        <i class="fa fa-search"></i>
                                    </span>
                            </div>
                            <div class="form-group col-sm-3  col-lg-2 ">
                                <button type="submit" class="btn btn-primary btn-block">搜索文章</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="panel">
                <div class="panel-content">
                    <div class="table-responsive">
                        <div id="basic-table_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer">
                            <div class="row">
                                <div class="col-sm-12">
                                    <table id="basic-table" class="data-table table table-striped nowrap table-hover dataTable no-footer" cellspacing="0" width="100%" role="grid" aria-describedby="basic-table_info" style="width: 100%;">
                                        <thead>
                                        <tr role="row">
                                            <th>文章ID<i class="sort"><img src="/images/px.gif" /></i></th>
                                            <th>标题</th>
                                            <th>用户</th>
                                            <th>阅读</th>
                                            <th>发布时间</th>
                                            <th>分类</th>
                                            <th>状态</th>
                                            @if ($admin)
                                                <th>置顶</th>
                                            @endif
                                            <th>操作</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($list_article as $row)
                                            <tr role="row" class="odd @if ($row['attribute'] == 3) color-success @endif">
                                                <td>{{$row['article_id']}}</td>
                                                <td>{{$row['title']}}</td>
                                                <td>{{app('App\Profile')->where('user_id', $row['user_id'])->first()['real_name']}}</td>
                                                <td>{{$row['click']}}</td>
                                                <td>{{$row['created_at']}}</td>
                                                <td>{{$row['name']}}</td>
                                                <td>{{$judge::articleStatus($row['attribute'])}}</td>
                                                @if ($admin)
                                                    <td>
                                                        @if ($row['attribute'] == 1)
                                                            <a href="/admin/article/top/{{$row['article_id']}}/3" class="tablelink">设为置顶</a>
                                                        @endif
                                                        @if ($row['attribute'] == 3)
                                                            <a href="/admin/article/top/{{$row['article_id']}}/1" class="tablelink">取消置顶</a>
                                                        @endif
                                                    </td>
                                                @endif
                                                <td>
                                                    @if ($row['attribute'] != 2)
                                                        <a href="/{{config('site.article_path').$row['links']}}" class="tablelink" target="_blank">查看</a>
                                                    @else
                                                        <a href="/article/{{$row['article_id']}}" class="tablelink" target="_blank">查看</a>
                                                    @endif
                                                    <a href="/admin/article/update/{{$row['article_id']}}" class="tablelink">编辑</a>
                                                    <a href="/admin/article/delete/{{$row['article_id']}}" class="tablelink" onclick="if(confirm('删除后不可恢复，确定要删除吗？') === false)return false;"> 删除</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-5">
                                    <div class="dataTables_info">共{{$count}}条记录  当前显示第{{$page}}页</div>
                                </div>
                                <div class="col-sm-7">
                                    <div class="dataTables_paginate paging_simple_numbers">
                                        <ul class="pagination">
                                            <li class="paginate_button previous"><a href="{{($page-1) < 1 ? 1 : ($page-1)}}">上一页</a></li>
                                            @for ($i=1;$i<=$max_page;$i++)
                                                <li class="paginate_button" id="paginate_button_{{$i}}"><a href="{{$i}}">{{$i}}</a></li>
                                            @endfor
                                            <li class="paginate_button next">
                                                <a href="{{($page+1) > $max_page ? $max_page : $page+1}}">Next</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @parent
    <script src="/vendor/nano-scroller/nano-scroller.js"></script>
    <script src="/javascripts/template-script.min.js"></script>
    <script src="/javascripts/template-init.min.js"></script>
    <script src="/vendor/data-table/media/js/jquery.dataTables.min.js"></script>
    <script src="/vendor/data-table/media/js/dataTables.bootstrap.min.js"></script>
@endsection