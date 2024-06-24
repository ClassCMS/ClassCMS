<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>{this:head($title)}</head>
<body>



<div class="layui-fluid">
  <div class="layui-row">

<div class="layui-form">
<input type="hidden" name="hash" value="{$user.hash}">
<input type="hidden" name="last_update_time" value="{$user.last_update_time}">
    <div class="layui-card">
        <div class="layui-card-header">
            <div class="layui-row">
                <?php
                    $breadcrumb=array(
                        array('url'=>'?do=admin:user:index','title'=>'用户管理'),
                    );
                    $breadcrumb[]=array('title'=>$user['username'].'['.$user['hash'].'] 管理','url'=>'?do=admin:user:edit&id='.$user['id']);
                    $breadcrumb[]=array('title'=>'属性');
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
                                    {loop $infos as $info}
                                        {if $info.tabname==$tab}
                                            <div class="layui-form-item layui-form-item-width-{$info.formwidth}">
                                                <label class="layui-form-label{if !$info.auth.write} disabled{/if}">{$info.formname}</label>
                                                <div class="layui-input-right">
                                                <div class="layui-input-block">
                                                    {cms:input:form($info)}
                                                </div>
                                                <div class="layui-form-mid">{$info.tips}</div>
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
        layui.admin.req({type:'post',url:"?do=admin:user:infoSave",data:data.field,async:true,tips:'提交中...',popup:true});
    });
});
</script>
{/if}
{this:body:~()}
</body>
</html>
