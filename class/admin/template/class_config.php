<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>{this:head($classinfo.classname)}</head>
<body>
  
  <div class="layui-fluid">
    <div class="layui-row">
        <div class="layui-card">

            <div class="layui-card-header">
                <div class="layui-row">
                    <?php
                        $breadcrumb=array(
                            array('url'=>'?do=admin:class:index','title'=>'应用管理'),
                            array('url'=>'','title'=>$classinfo['classname']),
                        );
                    ?>
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
<tr id="classname">
    <td align="right">应用:</td>
    <td>
        {$classinfo.classname} [{$classinfo.hash}]
    </td>
</tr>

<tr id="version">
    <td align="right">版本:</td>
    <td>
        {$classinfo.classversion}&nbsp;
        {if P('class:fileUpdate') && $classinfo.classversion && $classinfo.classversion<$new_version}
            <a id="class_update" rel="{$classinfo.hash}" old="{$classinfo.classversion}" new="{$new_version}" class="layui-btn layui-btn-sm layui-btn-normal">新版本:{$new_version}</a>
        {/if}
        {if $classinfo.classversion && $classinfo.classversion>$new_version}
            应用文件有变动,请卸载后重新安装.
        {/if}
    </td>
</tr>

{if $classinfo.requires}
    <tr id="requires">
        <td align="right">依赖:</td>
        <td>
            {$classinfo.requires}
        </td>
    </tr>
{/if}

{if $classinfo.author}
    <tr id="author">
        <td align="right">开发者:</td>
        <td>
            {$classinfo.author}{if isset($classinfo.url) && ($classinfo.url)} [<a href="{$classinfo.url}" target="_blank" style="color:blue" rel="nofollow noreferrer">{ltrim($classinfo.url,"//")}</a>]{/if}
        </td>
    </tr>
{/if}

{if $classinfo.installed}
    <tr id="state">
    <td align="right">状态:</td>
    <td>
    {if $classinfo.enabled}
        <input type="checkbox" name="{$classinfo.hash}" lay-filter="enabled" checked lay-skin="switch"{if !P('class:changeState')} disabled{/if}>
    {else}
        <input type="checkbox" name="{$classinfo.hash}" lay-filter="enabled" lay-skin="switch"{if !P('class:changeState')} disabled{/if}>
    {/if}
    </td>
    </tr>

    {if $classinfo.enabled}
        <tr id="config">
        <td align="right">设置:</td>
        <td>
            {if $classinfo.classorder>999999}
                <input type="checkbox" name="{$classinfo.hash}" title="置顶应用" lay-filter="classorder" checked lay-skin="primary"{if !P('class:order')} disabled{/if}>
            {else}
                <input type="checkbox" name="{$classinfo.hash}" title="置顶应用" lay-filter="classorder" lay-skin="primary"{if !P('class:order')} disabled{/if}>
            {/if}

            {if $classinfo.hash!='admin'}
                {if $classinfo.menu}
                    <input type="checkbox" name="{$classinfo.hash}" title="后台菜单" lay-filter="menu" checked lay-skin="primary"{if !P('class:menu')} disabled{/if}>
                {else}
                    <input type="checkbox" name="{$classinfo.hash}" title="后台菜单" lay-filter="menu" lay-skin="primary"{if !P('class:menu')} disabled{/if}>
                {/if}
            {else}
                <input type="checkbox" name="{$classinfo.hash}" title="后台菜单" lay-filter="menu" checked lay-skin="primary" disabled>
            {/if}
        </td>
        </tr>
    {/if}

{/if}

<tr id="manage">
    <td align="right">管理:</td>
    <td class="layui-btn-container">
    {if $phpcheck}
        {$phpcheck}
    {elseif $filenotfound}
        <a id="class_uninstall"  rel="{$classinfo.hash}" class="layui-btn layui-btn-sm layui-btn-primary">强制卸载</a> [应用文件不存在]
    {else}
        {if $classinfo.installed}
            {if !empty($classinfo.adminpage) && $classinfo.enabled}<a id="{$classinfo.hash}_adminpage" href="?do={$classinfo.hash}:{$classinfo.adminpage}" class="layui-btn layui-btn-sm layui-btn-primary">主页</a>{/if}
            {if $classinfo.module}
                {if P('channel:index')}<a class="layui-btn layui-btn-sm layui-btn-primary" href="?do=admin:channel:index&classhash={$classinfo.hash}">栏目</a>{/if}
                {if P('module:index')}<a class="layui-btn layui-btn-sm layui-btn-primary" href="?do=admin:module:index&classhash={$classinfo.hash}">模型</a>{/if}
            {/if}
            {if $classinfo.enabled && count($roles)>1 && $classinfo.auth && P('class:permission')}<a class="layui-btn layui-btn-sm layui-btn-primary" href="?do=admin:class:permission&hash={$classinfo.hash}">权限</a>{/if}
            {if $setting && $classinfo.enabled && P('class:setting')}<a class="layui-btn layui-btn-sm layui-btn-primary" href="?do=admin:class:setting&hash={$classinfo.hash}">设置</a>{/if}
            {if P('class:uninstall')}<a id="class_uninstall"  rel="{$classinfo.hash}" class="layui-btn layui-btn-sm layui-btn-primary">卸载</a>{/if}
        {else}
            {if P('class:install')}<a id="class_install" rel="{$classinfo.hash}" class="layui-btn layui-btn-sm layui-btn-normal">安装</a>{/if}
        {/if}
    {/if}
    </td>
</tr>

{if $description}
<tr id="description">
    <td align="right">简介:</td>
    <td>
    {$description}
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

        {if P('class:changeState')}
        layui.form.on('switch(enabled)', function(obj){
            layui.admin.req({type:'post',url:"?do=admin:class:changeState",data:{ hash: obj.elem.name, state: obj.elem.checked},async:true,beforeSend:function(){
                layui.admin.load('请稍等...');
            },done: function(res){
                configmsg(res);
            }});
        });
        {/if}

        {if P('class:order')}
        layui.form.on('checkbox(classorder)', function(obj){
            layui.admin.req({type:'post',url:"?do=admin:class:order",data:{ hash: obj.elem.name, state: obj.elem.checked},async:true,beforeSend:function(){
                layui.admin.load('请稍等...');
            },done: function(res){
                configmsg(res);
            }});
        });
        {/if}
        
        {if P('class:menu')}
        layui.form.on('checkbox(menu)', function(obj){
            layui.admin.req({type:'post',url:"?do=admin:class:menu",data:{ hash: obj.elem.name, state: obj.elem.checked},async:true,beforeSend:function(){
                layui.admin.load('请稍等...');
            },done: function(res){
                configmsg(res);
            }});
        });
        {/if}

        {if P('class:install')}
        layui.$('#class_install').click(function(){
            var classhash=layui.$(this).attr('rel');
            layer.confirm('确定安装此应用?', {btn: ['安装','取消'],shadeClose:1}, function(){
                layui.admin.req({type:'post',url:"?do=admin:class:install",data:{ hash: classhash},async:true,beforeSend:function(){
                    layui.admin.load('安装中...');
                },done: function(res){
                    configmsg(res);
                }});
            });
            
        });

        layui.$('#class_uninstall').click(function(){
            var classhash=layui.$(this).attr('rel');
            layer.confirm('确定卸载此应用?<br>应用所属的数据将会被删除!!!{if $filenotfound}<br>强制卸载可能会有数据残留.{else}<br>卸载后请手动删除应用文件夹.{$required_tips}{/if}', {btn: ['卸载','取消'],title:'请确认',skin:'layer-danger',shadeClose:1}, function(){
                layui.admin.req({type:'post',url:"?do=admin:class:uninstall",data:{ hash: classhash},async:true,beforeSend:function(){
                    layui.admin.load('卸载中...');
                },done: function(res){
                    configmsg(res);
                }});
            });
            
        });
        {/if}

        {if P('class:fileUpdate')}
        layui.$('#class_update').click(function(){
            var classhash=layui.$(this).attr('rel');
            var old_version=layui.$(this).attr('old');
            var new_version=layui.$(this).attr('new');
            layer.confirm('检测到应用文件有变动,是否更新?', {btn: ['更新','取消'],shadeClose:1}, function(){
                layui.admin.req({type:'post',url:"?do=admin:class:fileUpdate",data:{ hash: classhash,old_version:old_version,new_version:new_version},async:true,beforeSend:function(){
                    layui.admin.load('更新中...');
                },done: function(res){
                    configmsg(res);
                }});
            });
            
        });
        {/if}
    });
    function configmsg(res){
        if (res.error==0)
        {
            var confirm=layer.confirm(res.msg, {btn: ['好的'],shadeClose:1,end :function(){layui.admin.events.reload();}}, function(){layui.layer.close(confirm);});
        }
    }
</script>
{this:body:~()}
</body>
</html>