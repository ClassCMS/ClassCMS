<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>{this:head(栏目列表)}</head>
<body>
  
  <div class="layui-fluid">
    <div class="layui-row">

        <div class="layui-card">
            <div class="layui-card-header">
                <div class="layui-row">
                    <div id="cms-breadcrumb">{this:breadcrumb($breadcrumb)}</div>
                    <div id="cms-right-top-button">
                        {if P('channel:add')}<a href="?do=admin:channel:add&classhash={$classinfo.hash}{if $fid}&fid={$fid}{/if}" class="layui-btn layui-btn-sm layui-btn-danger"><i class="layui-icon layui-icon-add-1"></i><b>增加栏目</b></a>{/if}
                    </div>
                </div>
            </div>
          
          <div class="layui-card-body layui-form">
{if count($channels)}
<table class="layui-table" lay-skin="line"  lay-size1="sm">
         <thead>
          <tr>
            <th>栏目名</th>
            {if $channel_edit}<th class="layui-hide-xs">栏目ID</th>{/if}
            {if P('module:config')}<th class="layui-hide-xs">模型标识</th>{/if}
            <th class="layui-hide-xs">排序</th>
            <th ></th>
          </tr> 
        </thead>
        <tbody id="channel">
            {loop $channels as $channel}
            <tr rel="{$channel.id}" data-name="{$channel.channelname}">
                <td>
                    {if isset($channel.ex)}{$channel.ex}{/if}
                    <a href="?do=admin:article:home&cid={$channel.id}"><span{if $channel.enabled==0} class="cms-text-disabled"{/if}>{$channel.channelname}</span></a>
                </td>
                {if $channel_edit}<td class="layui-hide-xs">{$channel.id}</td>{/if}
                {if P('module:config')}
                    <td class="layui-hide-xs">
                        <a href="?do=admin:channel:jumpModule&id={$channel.id}">{$channel.modulehash}</a>
                    </td>
                {/if}
                <td class="layui-hide-xs">
                    {$channel.channelorder}
                </td>
                <td class="btn">
                    <a class="layui-btn layui-btn-sm  layui-btn-primary{if $channel.enabled==0} layui-btn-disabled{/if}"{if $channel.enabled} target="_blank" href="?do=admin:channel:jump&id={$channel.id}"{/if}>访问</a>
                    {if $showpage}<a class="layui-btn layui-btn-sm  layui-btn-primary" href="?do=admin:channel:index&classhash={$classinfo.hash}&id={$channel.id}">栏目</a>{/if}
                    <a class="layui-btn layui-btn-sm  layui-btn-primary" href="?do=admin:article:home&cid={$channel.id}">管理</a>
                    {if $channel_edit}<a class="layui-btn layui-btn-sm layui-btn-primary" href="?do=admin:channel:edit&id={$channel.id}">修改</a>{/if}
                    {if P('channel:del')}<a class="layui-btn layui-btn-sm layui-btn-primary channeldel">删除</a>{/if}
                </td>
            </tr>
            {/loop}
        </tbody>
</table>
{else}
    <blockquote class="layui-elem-quote layui-text">
    {if $fid}
        尚未增加子栏目
    {else}
        {$classinfo.classname}[{$classinfo.hash}] 尚未增加栏目
    {/if}
    </blockquote>
{/if}
<div class="layui-row">
    <div id="cms-left-bottom-button" class="layui-btn-container">
    </div>
    <div id="cms-right-bottom-button" class="layui-btn-container">
        {if count($channels) && $showpage}{this:pagelist()}{/if}
    </div>
</div>

{if count($channels) && $showpage}
    <blockquote class="layui-elem-quote layui-text">
        栏目数量较多,已关闭树状显示栏目列表
    </blockquote>
{/if}

    <script>
    layui.use(['index','form','sortable'],function(){
        layui.$('.channeldel').click(function(){
            delid=layui.$(this).parents('tr').attr('rel');
            layui.layer.confirm('是否删除栏目:'+layui.$(this).parents('tr').attr('data-name')+'<br>注意:栏目文章与栏目变量也将被删除!', {
              btn: ['删除','取消'],skin:'layer-danger',title:'请确认',shadeClose:1}, function(){
                layui.admin.req({type:'post',url:"?do=admin:channel:del",data:{ id: delid},async:true,beforeSend:function(){
                    layui.admin.load('删除中...');
                },done: function(res){
                    if (res.error==0)
                    {
                        layui.layer.msg(res.msg);
                        layui.$('tr[rel='+delid+']').remove();
                    }
                }});
            });
        });
    });
    </script>

          </div>
        </div>
      
    </div>
  </div>

{this:body:~()}
</body>
</html>