<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>{this:head(增加模型)}</head>
<body>
  <div class="layui-fluid">
    <div class="layui-row">
      <div class="layui-form">

        <div class="layui-card">
            <div class="layui-card-header">
                    <div class="layui-row">
                        <div id="cms-breadcrumb">{this:breadcrumb($breadcrumb)}</div>
                        <div id="cms-right-top-button"></div>
                    </div>
            </div>
    <div class="layui-card-body">
            <input type="hidden" name="classhash" value="{$classinfo.hash}">
                    <table class="layui-table">
                    <colgroup>
                      <col width="120">
                      <col>
                    </colgroup>
                    <tbody>
        <tr>
        <td align="right">模型名:</td>
        <td class="layui-form-item-width-auto">
        <div class="layui-input-block"><input type="text" name="modulename" value="" class="layui-input"  lay-verify="required" placeholder="如:新闻 产品 单页"></div>
        </td>
        </tr>

        <tr>
        <td align="right">模型标识:</td>
        <td class="layui-form-item-width-auto">
        <div class="layui-input-block"><input type="text" name="hash" value=""  lay-verify="hash" class="layui-input" placeholder="格式为字母或(字母,数字,_)组合,如:news product page"></div>
        </td>
        </tr>


        <tr>
        <td align="right"><input type="checkbox" name="page" title="栏目页面" lay-skin="primary" lay-filter="checkall" checked></td>
        <td id="page_list" class="checkbox_list">
        {loop $routes as $key=>$route}
        <input type="checkbox" name="rotues[]" value="{$key}" title="{$route.title}" lay-filter="checkone" lay-skin="primary"{if !isset($route.checked) || $route.checked} checked{/if}>
        {/loop}
        </td>
        </tr>

        <tr>
        <td align="right"><input type="checkbox" name="var" title="栏目变量" lay-skin="primary" lay-filter="checkall"></td>
        <td id="var_list" class="checkbox_list">
        {loop $vars as $key=>$var}
        <input type="checkbox" name="vars[]" value="{$key}" title="{$var.title}" lay-filter="checkone" lay-skin="primary"{if !isset($var.checked) || $var.checked}{/if}>
        {/loop}
        </td>
        </tr>


        <tr>
        <td align="right"><input type="checkbox" name="column" title="文章字段" lay-skin="primary" lay-filter="checkall"></td>
        <td id="column_list" class="checkbox_list">
        {loop $columns as $key=>$column}
        <input type="checkbox" name="columns[]" value="{$key}" title="{$column.title}" lay-filter="checkone" lay-skin="primary"{if !isset($column.checked) || $column.checked}{/if}>
        {/loop}
        </td>
        </tr>
                    </tbody>
                  </table>
                  <div class="layui-form-item layui-layout-admin">
                    <div class="layui-input-block">
                        <div class="layui-footer">
                        <button class="layui-btn cms-btn" lay-submit="" lay-filter="form-submit">增加</button>
                        <button type="button" class="layui-btn layui-btn-primary" layadmin-event="back">返回</button>
                        </div>
                    </div>
                </div>
        </div>
</div>
</div>
</div>
  
<script>
layui.use(['form'],function(){
    layui.form.on('checkbox(checkall)', function(obj){
        if (obj.elem.checked)
        {
            layui.$('#'+obj.elem.name+'_list input').each(function(){
                layui.$(this).prop("checked", true);
            });
        }else{
            layui.$('#'+obj.elem.name+'_list input').each(function(){
                layui.$(this).prop("checked", false);
            });
        }
        layui.form.render();
    });

    layui.form.on('checkbox(checkone)', function(data){
        var someone_checked=false;
        layui.$(data.elem).parents('.checkbox_list').find('input[type=checkbox]').each(function(){
            if (layui.$(this).prop("checked"))
            {
                someone_checked=true;
            }
        });
        if (someone_checked)
        {
          layui.$(data.elem).parents('.checkbox_list').prev().find('input[type=checkbox]').prop("checked", true);
        }else{
          layui.$(data.elem).parents('.checkbox_list').prev().find('input[type=checkbox]').prop("checked", false);
        }
        layui.form.render();
    });

    layui.form.on('submit(form-submit)', function(data){
        layui.$('button[lay-filter=form-submit]').blur();
        layui.admin.req({type:'post',url:"?do=admin:module:addPost",data:data.field,async:true,beforeSend:function(){
            layui.admin.load('提交中...');
        },done: function(res){
            if (res.error==0)
            {
                var confirm=layer.confirm(res.msg, {btn: ['管理','返回'],shadeClose:1},function(){
                    window.location=res.url;
                    },function(){
                    layui.admin.events.back();
                    });
            }
        }});
      return false;
    });
});
</script>
{this:body:~()}
</body>
</html>
