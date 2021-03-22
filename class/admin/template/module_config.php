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
                        <div id="cms-breadcrumb">{this:breadcrumb($breadcrumb)}</div>
                        <div id="cms-right-top-button"></div>
                    </div>
            </div>

    <div class="layui-card-body layui-form">
            <table class="layui-table">
            <colgroup>
              <col width="80">
              <col>
            </colgroup>
            <tbody>

<tr>
<td align="right">模型:</td>
<td>
{$module.modulename}[{$module.hash}] 
{if P('channel:add')}<a class="layui-btn layui-btn-xs layui-btn-primary"  href="?do=admin:channel:add&classhash={$classinfo.hash}&modulehash={$module.hash}">增加栏目</a> {/if}
{if P('module:edit')}<a class="layui-btn layui-btn-xs layui-btn-primary moduleedit">修改</a> {/if}
{if P('module:del')}<a class="layui-btn layui-btn-xs layui-btn-primary moduledel">删除</a> {/if}
</td>
</tr>

{if P('module:edit')}
<tr>
<td align="right">状态:</td>
<td>
{if $module.enabled}
    <input type="checkbox" name="{$module.id}" lay-filter="enabled" checked lay-skin="switch">
{else}
    <input type="checkbox" name="{$module.id}" lay-filter="enabled" lay-skin="switch">
    当前模型已停用,下属栏目页面无法访问
{/if}
</td>
</tr>
{/if}


{if P('route:index')}
    <tr>
    <td align="right"><a class="layui-btn layui-btn-sm layui-btn-primary" href="?do=admin:route:index&id={$module.id}">页面</a></td>
    <td>
    {if count($routes)}
        {loop $routes as $route}
            <a href="?do=admin:route:edit&id={$route.id}" class="layui-btn  layui-btn-primary layui-btn-sm{if $route.enabled==0} layui-btn-disabled{/if}">{$route.hash}</a>
        {/loop}
    {else}
        无
    {/if}
    </td>
    </tr>
{/if}

{if P('var:index')}
    <tr>
    <td align="right"><a class="layui-btn layui-btn-sm layui-btn-primary" href="?do=admin:var:index&id={$module.id}">变量</a></td>
    <td>
        {if count($vars)}
            {if count($varstabs)>1}
                <table class="layui-table">
                    {loop $varstabs as $tab}
                        <tr>
                            <td>
                                {loop $vars as $var}
                                    {if $var.tabname==$tab}
                                    <a href="?do=admin:var:edit&id={$var.id}" class="layui-btn  layui-btn-primary layui-btn-sm{if $var.enabled==0} layui-btn-disabled{/if}">{$var.formname} {$var.hash}</a>
                                    {/if}
                                {/loop}
                            </td>
                        </tr>
                    {/loop}
                </table>
            {else}
                {loop $vars as $var}
                    <a href="?do=admin:var:edit&id={$var.id}" class="layui-btn  layui-btn-primary layui-btn-sm{if $var.enabled==0} layui-btn-disabled{/if}">{$var.formname} {$var.hash}</a>
                {/loop}
            {/if}
        {else}
            无
        {/if}
    </td>
    </tr>
{/if}


{if P('column:index')}
    <tr>
    <td align="right"><a class="layui-btn layui-btn-sm layui-btn-primary" href="?do=admin:column:index&id={$module.id}">字段</a></td>
    <td>
        {if count($columns)}
            {if count($columnstabs)>1}
                <table class="layui-table">
                    {loop $columnstabs as $tab}
                        <tr>
                            <td>
                                {loop $columns as $column}
                                    {if $column.tabname==$tab}
                                    <a href="?do=admin:column:edit&id={$column.id}" class="layui-btn  layui-btn-primary layui-btn-sm{if $column.enabled==0} layui-btn-disabled{/if}">{if $column.indexshow}<i class="layui-icon layui-icon-table"></i>{/if}{$column.formname} {$column.hash}</a>
                                    {/if}
                                {/loop}
                            </td>
                        </tr>
                    {/loop}
                </table>
            {else}
                {loop $columns as $column}
                    <a href="?do=admin:column:edit&id={$column.id}" class="layui-btn  layui-btn-primary layui-btn-sm{if $column.enabled==0} layui-btn-disabled{/if}">{if $column.indexshow}<i class="layui-icon layui-icon-table"></i>{/if}{$column.formname} {$column.hash}</a>
                {/loop}
            {/if}
        {else}
            无
        {/if}
    </td>
    </tr>
{/if}

{if P('module:permission') && count($roles)>1}
    <tr>
    <td align="right"><a class="layui-btn layui-btn-sm layui-btn-primary" href="?do=admin:module:permission&id={$module.id}">权限</a></td>
    <td>
        <table class="layui-table">
          <colgroup>
            <col width="150">
            <col>
          </colgroup>
        {loop $roles as $role}
            <tr>
            <td>
                {$role.rolename}[{$role.hash}]
            </td>
            <td>
            {if $role._editabled}
                {loop $actions as $thiskey=>$actionname}
                    {if stripos($thiskey,'|false')}
                        <input type="checkbox" name="{$role.hash}_role[{$thiskey}]" title="{$actionname[0]}" lay-skin="primary" disabled>
                    {else}
                        <input type="checkbox" name="{$role.hash}_role[{$thiskey}]" title="{$actionname[0]}" lay-skin="primary" checked disabled>
                    {/if}
                {/loop}
            {else}
                {loop $actions as $thiskey=>$actionname}
                    {$checkkey=cms:module:authStr($module,$thiskey)}
                    <input type="checkbox" name="{$role.hash}_role[{$thiskey}]" title="{$actionname[0]}" lay-skin="primary"{if C('this:roleCheck',$checkkey,$role.hash,false)} checked{/if} disabled>
                {/loop}
            {/if}
            </td>
            </tr>
        {/loop}
        </table>
    </td>
    </tr>
{/if}

            </tbody>
          </table>
          </div>



 </div>
  </div>
  </div>
  
<script>
    layui.use(['index','form'],function(){

        var $ = layui.$;

        layui.form.on('switch(enabled)', function(obj){
            layui.admin.req({type:'post',url:"?do=admin:module:editPost",data:{ id: {$module.id}, enabled: obj.elem.checked},async:true,beforeSend:function(){
                layui.admin.load('提交中...');
            },done: function(res){
                if (res.error==0)
                {
                    var confirm=layer.confirm(res.msg, {btn: ['好的','返回'],shadeClose:1},function(){layui.admin.events.reload();},function(){
                        layui.admin.events.back();
                        });
                }
            }});
        });

        {if P('module:edit')}
        $('.moduleedit').click(function(){
            layer.prompt({
              value: '{$module.modulename}',
              title: '模型名称'
            }, 
              function(value, index, elem){
                layui.admin.req({type:'post',url:"?do=admin:module:editPost",data:{ id: {$module.id}, modulename: value},async:true,beforeSend:function(){
                    layui.admin.load('提交中...');
                },done: function(res){
                    if (res.error==0)
                    {
                        var confirm=layer.confirm(res.msg, {btn: ['好的','返回'],shadeClose:1},function(){layui.admin.events.reload();},function(){
                            layui.admin.events.back();
                        });
                    }
                }});
            });
            
        });
        {/if}

        {if P('module:del')}
        layui.$('.moduledel').click(function(){
            layui.layer.confirm('是否删除模型?<br>注意:该模型的所有内容将被清空,请谨慎操作!!!<br>如模型下属栏目较多,删除可能会超时,请重试.', {
              btn: ['删除','取消'],skin:'layer-danger',title:'请确认',shadeClose:1}, function(){
                layui.admin.req({type:'post',url:"?do=admin:module:del",data:{ id: {$module.id}},timeout: 30000,async:true,beforeSend:function(){
                    layui.admin.load('删除中,如超时,请重试...');
                },done: function(res){
                    if (res.error==0)
                    {
                        var confirm=layer.confirm(res.msg, {btn: ['返回'],shadeClose:1},function(){window.location='?do=admin:module:index&classhash={$module.classhash}'});
                    }
                }});
            });
        });
        {/if}

});
</script>
{this:body:~()}
</body>
</html>
