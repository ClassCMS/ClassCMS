<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>{this:head($title)}</head>
<body>

  <div class="layui-fluid">
    <div class="layui-row">

<div class="layui-form">
    {if isset($channel.id)}
    <input type="hidden" name="cid" value="{$channel.id}">
    {/if}
    {if $id}
    <input type="hidden" name="id" value="{$id}">
    {/if}
    <div class="layui-card">
        <div class="layui-card-header">
            <div class="layui-row">
                <div id="cms-breadcrumb">{this:breadcrumb($breadcrumb)}</div>
                <div id="cms-right-top-button">
                    {if ($id && $auth.del)}<a class="layui-btn layui-btn-sm layui-btn-danger articledel"><i class="layui-icon layui-icon-close"></i><b>删除</b></a>{/if}
                    {if !$auth.list && $varEnabled}<a href="?do=admin:article:varEdit&cid={$channel.id}" class="layui-btn layui-btn-sm layui-btn-danger">设置</a>{/if}
                </div>
            </div>
        </div>

        <div class="layui-card-body clear">
              <div class="layui-tab" lay-filter="columntab">
                    {if count($tabs)>1}
                        <ul class="layui-tab-title" id="tablist">
                        {loop $tabs as $key=>$tab}
                            <li{if $key==0} class="layui-this"{/if} lay-id="tabsort_{$key}"><span>{$tab}</span></li>
                        {/loop}
                        </ul>
                    {/if}
                    <div class="layui-tab-content" id="columnitem">
                        {loop $tabs as $key=>$tab}
                            <div class="layui-tab-item{if $key==0} layui-show{/if}">
                                {loop $columns as $column}
                                    {if $column.tabname==$tab}
                                        <div class="layui-form-item layui-form-item-width-{$column.formwidth}">
                                            <label class="layui-form-label{if !$column.auth.write} disabled{/if}">{$column.formname}</label>
                                            <div class="layui-input-right">
                                            <div class="layui-input-block">
                                                {cms:input:form($column)}
                                            </div>
                                            <div class="layui-form-mid">{$column.tips}</div>
                                            </div>
                                        </div>
                                    {/if}
                                {/loop}
                            </div>
                        {/loop}
                    </div>
            </div>
        </div>
    </div>



<div class="layui-form-item layui-layout-admin">
        <div class="layui-input-block">
            <div class="layui-footer">
            {if $allowsubmit}
                <button class="layui-btn cms-btn" lay-submit="" lay-filter="form-submit">{if !$id}增加{else}保存{/if}</button>
            {else}
                <button class="layui-btn layui-btn-disabled" lay-submit="" lay-filter="form-submit">保存</button>
            {/if}
            <button type="button" class="layui-btn layui-btn-primary" layadmin-event="back">返回</button>
            </div>
        </div>
    </div>

</div>


     </div>
  </div>
{if $allowsubmit}
<script>
    layui.use(['index'],function(){
    layui.form.on('submit(form-submit)', function(data){
        layui.$('button[lay-filter=form-submit]').blur();
        layui.admin.req({type:'post',url:"?do=admin:article:editSave",data:data.field,async:true,beforeSend:function(){
            layui.admin.load('提交中...');
        },done: function(res){
            if (res.error==0)
            {
                {if !$id && $auth.edit}
                    var confirm=layer.confirm(res.msg, {btn: ['编辑','返回'],shadeClose:1},function(){window.location=res.url;},function(){
                      layui.admin.events.back();
                      layui.layer.close(confirm);
                    });
                {else}
                    var confirm=layer.confirm(res.msg, {btn: ['好的','返回'],shadeClose:1},function(){layui.admin.events.reload();},function(){
                      layui.admin.events.back();
                      layui.layer.close(confirm);
                    });
                {/if}
                
            }
        }});
      return false;
    });
});
</script>
{/if}
{if $auth.del && $id}
    <script>
    layui.use(['index'],function(){
        layui.$('.articledel').click(function(){
            layui.layer.confirm('是否删除', {
              btn: ['删除','取消'],skin:'layer-danger',title:'请确认',shadeClose:1}, function(){
                layui.admin.req({type:'post',url:"?do=admin:article:del",data:{ ids: {$id},cid:{$channel.id}},async:true,beforeSend:function(){
                    layui.admin.load('删除中...');
                },done: function(res){
                    if (res.error==0)
                    {
                        layui.admin.events.back();
                    }
                }});
            });
        });
    });
    </script>
{/if}
{this:body:~()}
</body>
</html>
