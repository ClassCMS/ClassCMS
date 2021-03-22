<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>{this:head($title)}</head>
<body>


  <div class="layui-fluid">
    <div class="layui-row">
<div class="layui-form">
<input type="hidden" name="id" value="{$module.id}">

<div class="layui-card">
    <div class="layui-card-header">
        <div class="layui-row">
            <div id="cms-breadcrumb">{this:breadcrumb($breadcrumb)}</div>
            <div id="cms-right-top-button"></div>
        </div>
    </div>

    <div class="layui-card-body">

                <table class="layui-table">
                      <colgroup>
                        <col width="150">
                        <col>
                      </colgroup>
                      <thead>
                        <tr>
                          <th>角色</th>
                          <th>操作</th>
                        </tr> 
                      </thead>
                      <tbody>
                        {loop $roles as $role}
                        <tr>
                          <td> <input type="checkbox" name="{$role.hash}" title="{$role.rolename}[{$role.hash}]" lay-filter="checkall" lay-skin="primary"{if $role._editabled} disabled checked{/if}></td>
                          <td id="{$role.hash}_auth_list" class="module_auth_list">
                            {if $role._editabled}
                                {loop $actions as $thiskey=>$actionname}
                                    {if stripos($thiskey,'|false')}
                                        <input type="checkbox" name="{$role.hash}_role[{$thiskey}]" title="{$actionname[0]}" lay-filter="checkone" lay-skin="primary" disabled>
                                    {else}
                                        <input type="checkbox" name="{$role.hash}_role[{$thiskey}]" title="{$actionname[0]}" lay-filter="checkone" lay-skin="primary" disabled checked>
                                    {/if}
                                {/loop}
                            {else}
                                {loop $actions as $thiskey=>$actionname}
                                    {$checkkey=cms:module:authStr($module,$thiskey)}
                                    <input type="checkbox" name="{$role.hash}_role[{$thiskey}]" title="{$actionname[0]}" lay-filter="checkone" lay-skin="primary"{if C('this:roleCheck',$checkkey,$role.hash,false)} checked{/if}>
                                {/loop}
                            {/if}
                          </td>
                        </tr>
                        {/loop}
                      </tbody>
                    </table>
                <blockquote class="layui-elem-quote layui-text">
                {loop $actions as $thiskey=>$actionname}
                    {if isset($actionname[1]) && !empty($actionname[1])}
                        {$actionname[0]}:{$actionname[1]}<br>
                    {/if}
                {/loop}
                </blockquote>

                <blockquote class="layui-elem-quote layui-text">
                    如角色拥有"模型管理-权限管理"权限,则不受此页面权限限制,拥有全部权限
                </blockquote>
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
  
<script>
layui.use(['form'],function(){
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
    layui.$('.module_auth_list').each(function(){
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
      layui.$(data.elem).parents('.module_auth_list').find('input[type=checkbox]').each(function(){
            if (layui.$(this).prop("checked"))
            {
                someone_checked=true;
            }
      });
      if (someone_checked)
      {
          layui.$(data.elem).parents('.module_auth_list').prev().find('input[type=checkbox]').prop("checked", true);
      }else{
          layui.$(data.elem).parents('.module_auth_list').prev().find('input[type=checkbox]').prop("checked", false);
      }
      layui.form.render();
    });
    layui.form.on('submit(form-submit)', function(data){
          layui.$('button[lay-filter=form-submit]').blur();
          layui.admin.req({type:'post',url:"?do=admin:module:permissionPost",data:data.field,async:true,beforeSend:function(){
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
