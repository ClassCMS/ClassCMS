<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>{this:head($title)}</head>
<body>
  <div class="layui-fluid">
    <div class="layui-row">

<div class="layui-form">
{if isset($channel.id)}
<input type="hidden" name="id" value="{$channel.id}">
{else}
<input type="hidden" name="classhash" value="{$classinfo.hash}">
{/if}
    <div class="layui-card">
        <div class="layui-card-header">
            <div class="layui-row">
                <div id="cms-breadcrumb">{this:breadcrumb($breadcrumb)}</div>
                <div id="cms-right-top-button">
                    {if P('channel:add') && isset($channel.id)}<a href="?do=admin:channel:add&classhash={$classinfo.hash}&fid={$channel.id}" class="layui-btn layui-btn-sm layui-btn-danger"><i class="layui-icon layui-icon-add-1"></i><b>增加栏目</b></a>{/if}
                </div>
            </div>
        </div>
        <div class="layui-card-body">
                  <div class="layui-form-item layui-form-item-width-auto">
                    <label class="layui-form-label">栏目名</label>
                        <div class="layui-input-right">
                            <div class="layui-input-block">
                              <input type="text" name="channelname" value="{if isset($channel.channelname)}{$channel.channelname}{/if}" class="layui-input"  lay-verify="required">
                            </div>
                            <div class="layui-form-mid"></div>
                        </div>
                  </div>

                  <div class="layui-form-item">
                    <label class="layui-form-label">上级栏目</label>
                        <div class="layui-input-right">
                            <div class="layui-input-block">
                                {$channelselect_config.name=fid}
                                {if isset($channel.fid)}
                                    {$channelselect_config.value=$channel.fid}
                                {/if}
                                {$channelselect_config.classhash=$classinfo.hash}
                                {$channelselect_config.inputhash=channelselect}
                                {cms:input:form($channelselect_config)}
                            </div>
                            <div class="layui-form-mid">留空则为一级栏目</div>
                        </div>
                  </div>

                  <div class="layui-form-item">
                    <label class="layui-form-label">模型</label>
                        <div class="layui-input-right">
                            <div class="layui-input-block">
                                {$moduleselect_config.name=modulehash}
                                {if isset($channel.modulehash)}
                                    {$moduleselect_config.value=$channel.modulehash}
                                {/if}
                                {$moduleselect_config.classhash=$classinfo.hash}
                                {$moduleselect_config.inputhash=moduleselect}
                                {cms:input:form($moduleselect_config)}
                            </div>
                            <div class="layui-form-mid">{if isset($channel.modulehash)}变更栏目模型后,旧栏目变量与文章不会同步到新栏目中{/if}</div>
                        </div>
                  </div>

                  <div class="layui-form-item layui-form-item-width-auto">
                    <label class="layui-form-label">排序</label>
                    <div class="layui-input-right">
                    <div class="layui-input-block">
                      <input type="text" name="channelorder" value="{if isset($channel.channelorder)}{$channel.channelorder}{else}1{/if}" class="layui-input" lay-verify="required|number">
                    </div>
                    <div class="layui-form-mid">排序从小到大</div>
                    </div>
                  </div>

                  <div class="layui-form-item">
                    <label class="layui-form-label">启用</label>
                    <div class="layui-input-right">
                        <div class="layui-input-block">
                            {$enabled_input_config.name=enabled}
                            {if isset($channel.enabled)}
                                {$enabled_input_config.value=$channel.enabled}
                            {else}
                               {$enabled_input_config.value=1}
                            {/if}
                            {$enabled_input_config.inputhash=switch}
                            {cms:input:form($enabled_input_config)}
                        </div>
                        <div class="layui-form-mid">
                            停用后,栏目页面无法访问
                        </div>
                    </div>
                  </div>
        </div>

    </div>

    <div class="layui-form-item layui-layout-admin">
        <div class="layui-input-block">
            <div class="layui-footer">
            <button class="layui-btn cms-btn" lay-submit="" lay-filter="form-submit">{if isset($channel.id)}保存{else}增加{/if}</button>
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
        layui.admin.req({type:'post',url:"?do=admin:channel:{if isset($channel.id)}editPost{else}addPost{/if}",data:data.field,async:true,beforeSend:function(){
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
