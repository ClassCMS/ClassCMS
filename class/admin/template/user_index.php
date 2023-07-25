<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>{this:head(用户管理)}</head>
<body>
  <div class="layui-fluid">
    <div class="layui-row">
        <div class="layui-card">

        <div class="layui-card-header">
                <div class="layui-row">
                    <div id="cms-breadcrumb">{this:breadcrumb($breadcrumb)}</div>
                    <div id="cms-right-top-button">
                        {if P('user:add')}<a href="?do=admin:user:add" class="layui-btn layui-btn-sm layui-btn-danger"><i class="layui-icon layui-icon-add-1"></i><b>增加</b></a>{/if}
                        {if P('user:roleIndex')}<a href="?do=admin:user:roleIndex" class="layui-btn layui-btn-sm layui-btn-danger"><i class="layui-icon layui-icon-user"></i><b>角色</b></a>{/if}
                        {if P('info:index')}<a href="?do=admin:info:index" class="layui-btn layui-btn-sm layui-btn-danger"><i class="layui-icon layui-icon-table"></i><b>属性</b></a>{/if}
                    </div>
                </div>
            </div>

          <div class="layui-card-body layui-form">
            <table class="layui-table" lay-skin="line" id="users">
            <colgroup>
              <col>
              <col>
              <col>
            </colgroup>
            <thead>
              <tr>
                <th>账号</th>
                <th>昵称</th>
                <th>角色</th>
                {loop $infos as $info}
                <th>{$info.formname}</th>
                {/loop}
                <th></th>
              </tr> 
            </thead>
            <tbody>
                {loop $users as $user}
                    <tr rel="{$user.id}" data-hash="{$user.hash}">
                        <td>
                            <span{if $user.enabled==0} class="cms-text-disabled"{/if}>{$user.hash}</span>
                        </td>
                        <td>
                            {$user.username}
                        </td>
                        <td>
                            {$roleinput_config=array()}
                            {$roleinput_config.value=$user.rolehash}
                            {$roleinput_config.inputhash=rolecheckbox}
                            {cms:input:view($roleinput_config)}
                        </td>
                        {loop $infos as $info}
                            <td rel="{$info.hash}">
                                {if !isset($user[$info.hash])}{$user[$info.hash]=''}{/if}
                                {$info.value=$user[$info.hash]}
                                {$info.article=$user}
                                {cms:input:view($info)}
                            </td>
                        {/loop}
                        <td class="btn">
                            {if P('user:edit')}<a class="layui-btn layui-btn-sm layui-btn-primary" href="?do=admin:user:edit&id={$user.id}">管理</a>{/if}
                            {if P('user:del')}<a class="layui-btn layui-btn-sm layui-btn-primary  userdel">删除</a>{/if}
                        </td>
                    </tr>
                {/loop}
            </tbody>
          </table>

<div class="layui-row">
    <div id="cms-left-bottom-button" class="layui-btn-container"></div>
    <div id="cms-right-bottom-button" class="layui-btn-container">
        {this:pagelist()}
    </div>
</div>


          </div>
      </div>
    </div>
  </div>
{if P('user:del')}
<script>
layui.use(['index','form'],function(){
    layui.$('.userdel').click(function(){
        deluserhash=layui.$(this).parents('tr').attr('data-hash');
        deluserid=layui.$(this).parents('tr').attr('rel');
        layui.layer.confirm('是否删除账号:'+deluserhash, {
          btn: ['删除','取消'],skin:'layer-danger',title:'请确认',shadeClose:1}, function(){
            layui.admin.req({type:'post',url:"?do=admin:user:del",data:{ id: deluserid},async:true,tips:'删除中...',done: function(res){
                if (res.error==0)
                {
                    layui.layer.msg(res.msg);
                    layui.$('tr[rel='+deluserid+']').remove();
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

