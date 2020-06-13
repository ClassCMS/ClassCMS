<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>{this:head($title)}</head>
<body>

  <div class="layui-fluid">
    <div class="layui-row">

<div class="layui-form">
    <input type="hidden" name="classcms_classhash_" value="{$classinfo.hash}">
    <div class="layui-card">
        <div class="layui-card-header">
            <div class="layui-row">
                <?php
                    $breadcrumb=array(
                        array('url'=>'?do=admin:class:index','title'=>'应用管理'),
                        array('url'=>'?do=admin:class:config&hash='.$classinfo['hash'],'title'=>$classinfo['classname'].''),
                        array('url'=>'','title'=>'设置'),
                    );
                ?>
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
                                    {loop $configs as $config}
                                        {if $config.tabname==$tab}
                                            <div class="layui-form-item layui-form-item-width-{$config.formwidth}">
                                                <label class="layui-form-label">{$config.formname}</label>
                                                <div class="layui-input-right">
                                                <div class="layui-input-block">
                                                    {cms:input:form($config)}
                                                </div>
                                                <div class="layui-form-mid">{$config.tips}</div>
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
            <button class="layui-btn layui-btn-normal cms-btn" lay-submit="" lay-filter="form-submit">保存</button>
            <button type="button" class="layui-btn layui-btn-primary" layadmin-event="back">返回</button>
            </div>
        </div>
    </div>

</div>


     </div>
  </div>
<script>layui.use(['index'],function(){
    layui.form.on('submit(form-submit)', function(data){
        layui.$('button[lay-filter=form-submit]').blur();
        layui.admin.req({type:'post',url:"?do=admin:class:settingPost",data:data.field,async:true,beforeSend:function(){
            layui.admin.load('提交中...');
        },done: function(res){
            if (res.error==0)
            {
                var confirm=layer.confirm(res.msg, {btn: ['好的','返回'],shadeClose:1},function(){layui.layer.close(confirm);},function(){
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
