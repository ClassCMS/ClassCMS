<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>{this:head($title)}</head>
<body>



  <div class="layui-fluid">
    <div class="layui-row">

<div class="layui-form">
    <input type="hidden" name="id" value="{$id}">
    <div class="layui-card">
        <div class="layui-card-header">
            <div class="layui-row">
                <?php
                    $breadcrumb=array(
                        array('url'=>'?do=admin:user:index','title'=>'用户管理'),
                        array('url'=>'?do=admin:info:index','title'=>'属性管理'),
                        array('title'=>$info['formname'].'['.$info['hash'].'] 修改')
                    );
                ?>
                <div id="cms-breadcrumb">{this:breadcrumb($breadcrumb)}</div>
                <div id="cms-right-top-button"></div>
            </div>
        </div>


<div class="layui-card-body">

    <div class="layui-tab" lay-filter="infotab">
            <ul class="layui-tab-title" id="tablist">
                <li class="layui-this">基础设置</li>
                <li>属性配置</li>
                <li>默认值</li>
                <li>权限</li>
            </ul>
            <div class="layui-tab-content">
                <div class="layui-tab-item layui-show">
                          <div class="layui-form-item layui-form-item-width-auto">
                            <label class="layui-form-label">属性名</label>
                                <div class="layui-input-right">
                                    <div class="layui-input-block">
                                      <input type="text" name="formname" value="{if isset($formname)}{$formname}{/if}" class="layui-input"  lay-verify="required">
                                    </div>
                                    <div class="layui-form-mid"></div>
                                </div>
                          </div>

                          <div class="layui-form-item layui-form-item-width-auto">
                            <label class="layui-form-label">标识</label>
                            <div class="layui-input-right">
                            <div class="layui-input-block">
                              <input type="text" name="hash" value="{if isset($hash)}{$hash}{/if}"  lay-verify="hash" class="layui-input" lay-verify="required"{if isset($hash)} readonly{/if}>
                            </div>
                            <div class="layui-form-mid">标识无法更改</div>
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
                                    {$enabled_input_config.inputhash=switch}
                                    {cms:input:form($enabled_input_config)}
                                </div>
                                <div class="layui-form-mid">
                                    {if !$enabled_input_config.value}启用属性后,系统将创建属性到用户表中{/if}
                                </div>
                            </div>
                          </div>

                          <div class="layui-form-item">
                            <label class="layui-form-label">类型</label>
                            <div class="layui-input-right">
                            <div class="layui-input-block">
                                {$inputselect_config.name=inputhash}
                                {$inputselect_config.value=$inputhash}
                                {$inputselect_config.inputhash=inputselect}
                                {cms:input:form($inputselect_config)}
                            </div>
                            <div class="layui-form-mid">更改为不同的类型,有可能导致用户属性数据丢失,修改属性后请重新更改属性配置和默认值</div>
                            </div>
                          </div>

                          <div class="layui-form-item layui-form-item-width-auto">
                            <label class="layui-form-label">宽度</label>
                            <div class="layui-input-right">
                            <div class="layui-input-block">
                                {$inputwidth_config.name=formwidth}
                                {$inputwidth_config.value=$formwidth}
                                {$inputwidth_config.step=5}
                                {$inputwidth_config.min=5}
                                {$inputwidth_config.showstep=1}
                                {$inputwidth_config.inputhash=slider}
                                {cms:input:form($inputwidth_config)}
                            </div>
                            <div class="layui-form-mid"></div>
                            </div>
                          </div>

                          <div class="layui-form-item">
                            <label class="layui-form-label">必填</label>
                            <div class="layui-input-right">
                                <div class="layui-input-block">
                                    {$nonull_input_config.name=nonull}
                                    {if isset($nonull)}
                                        {$nonull_input_config.value=$nonull}
                                    {else}
                                       {$nonull_input_config.value=0}
                                    {/if}
                                    {$nonull_input_config.inputhash=switch}
                                    {cms:input:form($nonull_input_config)}
                                </div>
                                <div class="layui-form-mid">
                                    
                                </div>
                            </div>
                          </div>

                          <div class="layui-form-item">
                            <label class="layui-form-label">列表页显示</label>
                            <div class="layui-input-right">
                                <div class="layui-input-block">
                                    {$indexshow_input_config.name=indexshow}
                                    {if isset($indexshow)}
                                        {$indexshow_input_config.value=$indexshow}
                                    {else}
                                       {$indexshow_input_config.value=0}
                                    {/if}
                                    {$indexshow_input_config.inputhash=switch}
                                    {cms:input:form($indexshow_input_config)}
                                </div>
                                <div class="layui-form-mid">
                                    启用后,后台用户列表页显示此属性
                                </div>
                            </div>
                          </div>

                          <div class="layui-form-item layui-form-item-width-auto">
                            <label class="layui-form-label">输入提示</label>
                            <div class="layui-input-right">
                            <div class="layui-input-block">
                              <textarea class="layui-textarea" name="tips" lay-filter="tips">{if isset($tips)}{$tips}{/if}</textarea>
                            </div>
                            <div class="layui-form-mid">显示在表单下方的输入提示.支持HTML代码</div>
                            </div>
                          </div>

                 </div>
                 <div class="layui-tab-item">
                    {if count($config)}
                        {loop $config as $input}
                          <div class="layui-form-item">
                            <label class="layui-form-label">{$input.configname}</label>
                            <div class="layui-input-right">
                            <div class="layui-input-block">
                                {cms:input:form($input)}
                            </div>
                            <div class="layui-form-mid">{$input.tips}</div>
                            </div>
                          </div>
                        {/loop}
                    {else}
                        <blockquote class="layui-elem-quote layui-text">
                            该属性无配置选项
                        </blockquote>
                    {/if}
                 </div>
                 <div class="layui-tab-item">
                          <div class="layui-form-item">
                            <label class="layui-form-label">默认值</label>
                            <div class="layui-input-right">
                            <div class="layui-input-block">
                              {cms:input:form($defaultvalue_form)}
                            </div>
                            <div class="layui-form-mid">当增加新用户时,属性的默认值.请先修改"属性配置",保存后再设置默认值</div>
                            </div>
                          </div>
                          <div class="layui-form-item">
                            <label class="layui-form-label">重置</label>
                            <div class="layui-input-right">
                            <div class="layui-input-block">
                                {$reset_input_config.name=resetdefault}
                                {$reset_input_config.value=0}
                                {$reset_input_config.inputhash=switch}
                                {cms:input:form($reset_input_config)}
                            </div>
                            <div class="layui-form-mid">将所有用户属性[{$hash}]全部重置为默认值,请谨慎操作!!!</div>
                            </div>
                          </div>
                 </div>
                 <div class="layui-tab-item">
                    <table class="layui-table">
                      <thead>
                        <tr>
                          <th>角色</th>
                          <th>操作</th>
                        </tr> 
                      </thead>
                      <tbody>
                        {loop $roles as $role}
                        <tr>
                          <td> <input type="checkbox" name="{$role.hash}" title="{$role.rolename}[{$role.hash}]"  lay-filter="checkall" lay-skin="primary"{if $role._editabled} disabled checked{/if}></td>
                          <td id="{$role.hash}_auth_list" class="info_auth_list">
                            {if $role._editabled}
                                {loop $actions as $thiskey=>$actionname}
                                    {if stripos($thiskey,'|false')}
                                        <input type="checkbox" name="{$role.hash}_role[{$thiskey}]" title="{$actionname[0]}" lay-filter="checkone" lay-skin="primary" disabled>
                                    {else}
                                        <input type="checkbox" name="{$role.hash}_role[{$thiskey}]" title="{$actionname[0]}" lay-filter="checkone" lay-skin="primary" checked disabled>
                                    {/if}
                                {/loop}
                                {loop $input_actions as $thiskey=>$actionname}
                                    {if stripos($thiskey,'|false')}
                                        <input type="checkbox" name="{$role.hash}_role[{$thiskey}]" title="{$actionname}" lay-filter="checkone" lay-skin="primary" disabled>
                                    {else}
                                        <input type="checkbox" name="{$role.hash}_role[{$thiskey}]" title="{$actionname}" lay-filter="checkone" lay-skin="primary" checked disabled>
                                    {/if}
                                {/loop}
                            {else}
                                {loop $actions as $thiskey=>$actionname}
                                    {$checkkey=cms:form:authStr($info,$thiskey)}
                                    <input type="checkbox" name="{$role.hash}_role[{$thiskey}]" title="{$actionname[0]}" lay-filter="checkone" lay-skin="primary"{if C('this:roleCheck',$checkkey,$role.hash,false)} checked{/if}>
                                {/loop}
                                {loop $input_actions as $thiskey=>$actionname}
                                    {$checkkey=cms:form:authStr($info,$thiskey)}
                                    <input type="checkbox" name="{$role.hash}_role[{$thiskey}]" title="{$actionname}" lay-filter="checkone" lay-skin="primary"{if C('this:roleCheck',$checkkey,$role.hash,false)} checked{/if}>
                                {/loop}
                            {/if}
                          </td>
                        </tr>
                        {/loop}
                      </tbody>
                    </table>
                    <blockquote class="layui-elem-quote layui-text">
                        如角色拥有"用户管理-属性管理"权限,则不受此页面权限限制,拥有全部权限
                    </blockquote>
                 </div>
            </div>
    </div>



</div>
    </div>




    <div class="layui-form-item layui-layout-admin">
        <div class="layui-input-block">
            <div class="layui-footer">
            <button class="layui-btn cms-btn" lay-submit="" lay-filter="form-submit">保存</button>
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
        layui.admin.req({type:'post',url:"?do=admin:info:editPost",data:data.field,async:true,beforeSend:function(){
            layui.admin.load('提交中...');
        },done: function(res){
            if (res.error==0)
            {
                var confirm=layer.confirm(res.msg, 
                    {
                        btn: ['好的','返回'],
                        shadeClose:1
                    },
                    function(){
                        if (res.refresh)
                        {
                            layui.admin.events.reload();
                        }
                        layui.layer.close(confirm);
                    },
                    function(){
                        layui.admin.events.back();
                    },
                );
            }
        }});
      return false;
    });
    layui.form.on('checkbox(checkall)', function(obj){
        if (obj.elem.checked)
        {
            layui.$('#'+obj.elem.name+'_auth_list input').each(function(){
                if (layui.$(this).attr('name').indexOf('|false')<0)
                {
                    layui.$(this).prop("checked", true);
                }
            });
        }else{
            layui.$('#'+obj.elem.name+'_auth_list input').each(function(){
                layui.$(this).prop("checked", false);
            });
        }
        layui.form.render();
    });
    layui.$('.info_auth_list').each(function(){
        var someone_checked=false;
        layui.$(this).find('input[type=checkbox]').each(function(){
            if (layui.$(this).prop("checked"))
            {
                someone_checked=true;
            }
        });
        if (someone_checked)
        {
            layui.$(this).prev().find('input[type=checkbox]').prop("checked", true);
            layui.form.render();
        }
    });
    layui.form.on('checkbox(checkone)', function(data){
      var someone_checked=false;
      layui.$(data.elem).parents('.info_auth_list').find('input[type=checkbox]').each(function(){
            if (layui.$(this).prop("checked"))
            {
                someone_checked=true;
            }
      });
      if (someone_checked)
      {
          layui.$(data.elem).parents('.info_auth_list').prev().find('input[type=checkbox]').prop("checked", true);
      }else{
          layui.$(data.elem).parents('.info_auth_list').prev().find('input[type=checkbox]').prop("checked", false);
      }
      layui.form.render();
    });
});
</script>
{this:body:~()}
</body>
</html>
