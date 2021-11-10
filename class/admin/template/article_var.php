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
    <div class="layui-card">
        <div class="layui-card-header">
            <div class="layui-row">
                <div id="cms-breadcrumb">{this:breadcrumb($breadcrumb)}</div>
                <div id="cms-right-top-button"></div>
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
                                    {loop $vars as $var}
                                        {if $var.tabname==$tab}
                                            <div class="layui-form-item layui-form-item-width-{$var.formwidth}">
                                                <label class="layui-form-label{if !$var.auth.write} disabled{/if}">{$var.formname}</label>
                                                <div class="layui-input-right">
                                                <div class="layui-input-block">
                                                    {cms:input:form($var)}
                                                </div>
                                                <div class="layui-form-mid">{$var.tips}</div>
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
                <button class="layui-btn cms-btn" lay-submit="" lay-filter="form-submit">保存</button>
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
<script>layui.use(['index'],function(){
    layui.form.on('submit(form-submit)', function(data){
        layui.$('button[lay-filter=form-submit]').blur();
        layui.admin.req({type:'post',url:"?do=admin:article:varSave",data:data.field,async:true,beforeSend:function(){
            layui.admin.load('提交中...');
        },done: function(res){
            if (res.error==0)
            {
                var confirm=layer.confirm(res.msg, {btn: ['好的','返回'],shadeClose:1},function(){layui.admin.events.reload();},function(){
                    layui.admin.events.back();
                    });
            }
        }});
      return false;
    });
});
</script>
{/if}
{this:body:~()}
</body>
</html>
