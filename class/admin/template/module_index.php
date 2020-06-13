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
                    <div id="cms-right-top-button">
                        {if P('module:add')}<a href="?do=admin:module:add&classhash={$classhash}" class="layui-btn layui-btn-sm layui-btn-danger"><i class="layui-icon layui-icon-add-1"></i><b>增加模型</b></a>{/if}
                    </div>
                </div>
            </div>


          <div class="layui-card-body layui-form">
{if (count($modulelist))}

 <table class="layui-table" lay-skin="line" >
            <colgroup>
              <col>
              <col>
              <col>
              <col>
            </colgroup>
            <thead>
              <tr>
                <th>模型名</th>
                <th class="layui-hide-xs">模型标识</th>
                <th ></th>
              </tr> 
            </thead>
            <tbody id="modules">
{loop $modulelist as $module}
<tr rel="{$module.id}">
<td>
<i class="layui-icon layui-icon-find-fill sortable-color"></i>
{if P('module:edit')}<a href="?do=admin:module:config&id={$module.id}">{/if}
<span{if $module.enabled==0} class="cms-text-disabled"{/if}>{$module.modulename}</span>
{if P('module:edit')}</a>{/if}
</td>
<td class="layui-hide-xs">
{$module.hash}
</td>

<td class="btn">
        {if P('route:index')}<a class="layui-btn layui-btn-sm layui-btn-primary" href="?do=admin:route:index&id={$module.id}">页面</a>{/if}
        {if P('var:index')}<a class="layui-btn layui-btn-sm layui-btn-primary" href="?do=admin:var:index&id={$module.id}">变量</a>{/if}
        {if P('column:index')}<a class="layui-btn layui-btn-sm layui-btn-primary" href="?do=admin:column:index&id={$module.id}">字段</a>{/if}
        {if P('module:edit')}<a class="layui-btn layui-btn-sm layui-btn-primary" href="?do=admin:module:config&id={$module.id}">管理</a>{/if}
</td>
</tr>
{/loop}
            </tbody>
          </table>

<div class="layui-row">
    <div id="cms-left-bottom-button" class="layui-btn-container"></div>
    <div id="cms-right-bottom-button" class="layui-btn-container"></div>
</div>

<script>
layui.use(['index','sortable'],function(){

new Sortable(modules, {
        handle: '.layui-icon',
        onSort: function (evt) {
            modulesarray='';
            layui.$('#modules tr').each(function(){
                modulesarray=modulesarray+'|'+layui.$(this).attr('rel');
            });
            layui.admin.req({type:'post',url:"?do=admin:module:order",data:{ modulesarray: modulesarray,classhash:'{$classhash}'},async:true,beforeSend:function(){
                layui.admin.load('修改排序中...');
            },done: function(res){
                
            }});
        }
    });

});
</script>
{else}
<blockquote class="layui-elem-quote layui-text">
    {$classname}[{$classhash}] 尚未增加模型
</blockquote>

{/if}

          </div>

        </div>
      
    </div>
  </div>

{this:body:~()}
</body>
</html>