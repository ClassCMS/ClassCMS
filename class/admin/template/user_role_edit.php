<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>{this:head(角色管理)}</head>
<body>


  <div class="layui-fluid" id="component-tabs">
    <div class="layui-row">

<div class="layui-form">

    <div class="layui-card">
        <div class="layui-card-header">
            <div class="layui-row">
                <?php
                    $breadcrumb=array(
                        array('url'=>'?do=admin:user:index','title'=>'用户管理'),
                        array('url'=>'?do=admin:user:roleIndex','title'=>'角色管理'),
                    );
                    if(isset($rolename)) {
                        $breadcrumb[]=array('title'=>$rolename.'['.$hash.'] 修改');
                    }else {
                        $breadcrumb[]=array('title'=>'增加');
                    }
                ?>
                <div id="cms-breadcrumb">{this:breadcrumb($breadcrumb)}</div>
                <div id="cms-right-top-button"></div>
            </div>
        </div>


        <div class="layui-card-body">
                  <div class="layui-form-item layui-form-item-width-auto">
                    <label class="layui-form-label">名称</label>
                        <div class="layui-input-right">
                            <div class="layui-input-block">
                              <input type="text" name="rolename" value="{if isset($rolename)}{$rolename}{/if}" class="layui-input"  lay-verify="required">
                            </div>
                            <div class="layui-form-mid"></div>
                        </div>
                  </div>
                  <div class="layui-form-item layui-form-item-width-auto">
                    <label class="layui-form-label">标识</label>
                    <div class="layui-input-right">
                    <div class="layui-input-block">
                      <input type="text" name="hash" value="{if isset($hash)}{$hash}{/if}" lay-verify="hash" class="layui-input" {if isset($hash)}readonly{/if}>
                    </div>
                    <div class="layui-form-mid">{if isset($hash)}标识无法更改{else}标识格式为字母或(字母,数字,_)组合{/if}</div>
                    </div>
                  </div>

                  <div class="layui-form-item">
                    <label class="layui-form-label">启用</label>
                    <div class="layui-input-right">
                        <div class="layui-input-block">
                            {$adminrolehash=C('cms:user:$admin_role')}
                            {$enabled_input_config.name=enabled}
                            {if isset($enabled)}
                                {$enabled_input_config.value=$enabled}
                            {else}
                               {$enabled_input_config.value=1}
                            {/if}
                            {if isset($hash) && $hash==$adminrolehash}
                                {$enabled_input_config.disabled=1}
                            {/if}
                            {$enabled_input_config.inputhash=switch}
                            {cms:input:form($enabled_input_config)}
                        </div>
                        <div class="layui-form-mid">
                            {if isset($hash) && $hash==$adminrolehash}
                                管理员角色无法被禁用
                            {else}
                                禁用后,下属用户无法登入
                            {/if}
                        </div>
                    </div>
                  </div>
        </div>
    </div>

<div class="layui-form-item layui-layout-admin">
        <div class="layui-input-block">
            <div class="layui-footer">
            <button class="layui-btn cms-btn" lay-submit="" lay-filter="form-submit">{if isset($hash)}保存{else}增加{/if}</button>
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
        layui.admin.req({type:'post',url:"?do=admin:user:{if isset($hash)}roleEditPost{else}roleAddPost{/if}",data:data.field,async:true,beforeSend:function(){
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
