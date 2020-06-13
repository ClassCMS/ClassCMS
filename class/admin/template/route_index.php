<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>{this:head($title)}</head>
<body>



  <div class="layui-fluid">
    <div class="layui-row">

<div class="layui-card">

    <div class="layui-card-header">
        <div class="layui-row">
            <div id="cms-breadcrumb">
                {this:breadcrumb($breadcrumb)}
            </div>
            <div id="cms-right-top-button">
                {if P('route:add')}<a href="?do=admin:route:add&moduleid={$module.id}" class="layui-btn layui-btn-sm layui-btn-danger"><i class="layui-icon layui-icon-add-1"></i><b>增加页面</b></a>{/if}
            </div>
        </div>
    </div>



        <div class="layui-card-body">
{$channel_route=0}
{$article_route=0}
{$list_route=0}
{if (count($routes)>0)}
            <table class="layui-table" lay-skin="line" >
            <colgroup>
              <col>
              <col>
              <col>
              <col>
            </colgroup>
            <thead>
              <tr>
                <th>标识</th>
                <th>网址</th>
                <th class="layui-hide-xs">函数</th>
                <th class="layui-hide-xs">模板</th>
                <th></th>
              </tr> 
            </thead>
            <tbody  id="routes">

{loop $routes as $route}
            <tr rel="{$route.id}">
                 <td>
                    <i class="layui-icon layui-icon-find-fill sortable-color"></i> 
                    <span{if $route.enabled==0} class="cms-text-disabled"{/if}>
                    {$route.hash}
                    {if $route.hash=='channel'}[栏目页]{$channel_route=1}{/if}
                    {if $route.hash=='list'}[列表页]{$list_route=1}{/if}
                    {if $route.hash=='article'}[文章页]{$article_route=1}{/if}
                    </span>
                </td>
                <td>
                    {$route.uri}
                </td>
                <td class="layui-hide-xs">
                    {$route.classfunction}
                </td>
                <td class="layui-hide-xs">
                    {$route.classview}
                </td>
                 <td class="btn">
                    <a class="layui-btn layui-btn-sm layui-btn-primary" href="?do=admin:route:edit&id={$route.id}">修改</a>
                    <a class="layui-btn layui-btn-sm layui-btn-primary routedel" rel="{$route.id}">删除</a>
                </td>
            </tr>
{/loop}
            </tbody>
          </table>
{/if}

<div class="layui-row">
    <div id="cms-left-bottom-button" class="layui-btn-container"></div>
    <div id="cms-right-bottom-button" class="layui-btn-container"></div>
</div>
           {if isset($module.domain)}
           <blockquote class="layui-elem-quote layui-text">
                当前模型有domain变量,所有下属栏目的页面只能通过此变量域名访问
           </blockquote>
           {/if}
           {if !$channel_route}
           <blockquote class="layui-elem-quote layui-text">
                当前模型没有栏目页[channel],栏目页无法访问
           </blockquote>
           {/if}
           {if count($columns)}
               {if !$list_route}
               <blockquote class="layui-elem-quote layui-text">
                    当前模型没有列表页[list],文章列表页无法访问
               </blockquote>
               {/if}
               {if !$article_route}
               <blockquote class="layui-elem-quote layui-text">
                    当前模型没有文章页[article],栏目文章页无法访问
               </blockquote>
               {/if}
            {/if}
        </div>
    </div>




     </div>
  </div>
  
<script>
layui.use(['index','sortable'],function(){
    {if (count($routes)>0)}
    new Sortable(routes, {
        handle: '.layui-icon',
        onSort: function (evt) {
            routesarray='';
            layui.$('#routes tr').each(function(){
                routesarray=routesarray+'|'+layui.$(this).attr('rel');
            });
            layui.admin.req({type:'post',url:"?do=admin:route:order",data:{ routesarray: routesarray},async:true,beforeSend:function(){
                layui.admin.load('修改排序中...');
            },done: function(res){
            }});
        }
    });
    {/if}
    layui.$('.routedel').click(function(){
        delrouteid=layui.$(this).attr('rel');
        nowtr=layui.$(this).parents('tr');
        layui.layer.confirm('是否删除此页面:'+layui.$(this).parents('tr').find('td').eq(0).text()+"<br>"+layui.$(this).parents('tr').find('td').eq(1).text()+"<br>删除后此页面无法访问", {
          btn: ['删除','取消'],skin:'layer-danger',title:'请确认',shadeClose:1}, function(){
            layui.admin.req({type:'post',url:"?do=admin:route:del",data:{ id: delrouteid},async:true,beforeSend:function(){
                layui.admin.load('删除中...');
            },done: function(res){
                if (res.error==0)
                {
                    layui.layer.msg(res.msg);
                    nowtr.remove();
                }
            }});
        });
    });
});
    </script>
{this:body:~()}
</body>
</html>
