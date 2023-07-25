<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>{this:head($title)}</head>
<body>


  <div class="layui-fluid">
    <div class="layui-row">

<div class="layui-form">
    <div class="layui-card">
        <div class="layui-card-header">
            <div class="layui-row">
                 <?php
                    $breadcrumb=array(
                        array('url'=>'?do=admin:user:index','title'=>'用户管理'),
                    );
                    if(isset($username)) {
                        $breadcrumb[]=array('title'=>$username.'['.$hash.'] 管理');
                    }else {
                        $breadcrumb[]=array('title'=>'增加');
                    }
                ?>
                <div id="cms-breadcrumb">{this:breadcrumb($breadcrumb)}</div>
                <div id="cms-right-top-button">
                {if isset($id) && count($infos) && P('user:info')}<a href="?do=admin:user:info&id={$id}" class="layui-btn layui-btn-sm layui-btn-danger"><i class="layui-icon layui-icon-table"></i><b>属性</b></a>{/if}
                </div>
            </div>
        </div>

        <div class="layui-card-body">
                  <div class="layui-form-item layui-form-item-width-auto">
                    <label class="layui-form-label">昵称</label>
                        <div class="layui-input-right">
                            <div class="layui-input-block">
                              <input type="text" name="username" value="{if isset($username)}{$username}{/if}" class="layui-input" lay-verify="required">
                            </div>
                            <div class="layui-form-mid"></div>
                        </div>
                  </div>
                  <div class="layui-form-item layui-form-item-width-auto">
                    <label class="layui-form-label">账号</label>
                    <div class="layui-input-right">
                    <div class="layui-input-block">
                      <input type="text" name="hash" value="{if isset($hash)}{$hash}{/if}" lay-verify="hash" class="layui-input" {if isset($hash)}readonly{/if}>
                    </div>
                    <div class="layui-form-mid">{if isset($hash)}账号无法更改{else}格式为字母或(字母,数字,_)组合{/if}</div>
                    </div>
                  </div>

                  <div class="layui-form-item">
                    <label class="layui-form-label">启用</label>
                    <div class="layui-input-right">
                        <div class="layui-input-block">
                            {$enabled_input_config.name=enabled}
                            {if isset($enabled)}
                                {$enabled_input_config.value=$enabled}
                            {else}
                                {$enabled_input_config.value=1}
                            {/if}
                            {if isset($hash) && $nowuser.hash==$hash}
                                {$enabled_input_config.disabled=1}
                            {/if}
                            {$enabled_input_config.inputhash=switch}
                            {cms:input:form($enabled_input_config)}
                        </div>
                        <div class="layui-form-mid">
                            {if isset($hash) && $nowuser.hash==$hash}
                                无法停用自身账号
                            {/if}
                        </div>
                    </div>
                  </div>

                  <div class="layui-form-item">
                    <label class="layui-form-label">角色</label>
                    <div class="layui-input-right">
                        <div class="layui-input-block">
                            {cms:input:form($roleinput)}
                        </div>
                        <div class="layui-form-mid">
                            {if isset($hash) && $nowuser.hash==$hash}
                                无法更改自身所属角色
                            {/if}
                        </div>
                    </div>
                  </div>

                  <div class="layui-form-item layui-form-item-width-auto">
                    <label class="layui-form-label">密码</label>
                    <div class="layui-input-right">
                        <div class="layui-input-block">
                            {cms:input:form($passwdinput)}
                        </div>
                        <div class="layui-form-mid">{if isset($hash)}不更改则无需填写{/if}</div>
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
  
<script>layui.use(['index','form'],function(){
    layui.form.on('submit(form-submit)', function(data){
        layui.$('button[lay-filter=form-submit]').blur();
        layui.admin.req({type:'post',url:"?do=admin:user:{if isset($hash)}editPost{else}addPost{/if}",data:data.field,async:true,tips:'提交中...',popup:true});
    });
});
</script>
{this:body:~()}
</body>
</html>