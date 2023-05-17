<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>{this:head(角色管理)}</head>
<body>
  <div class="layui-fluid">
    <div class="layui-row">
        <div class="layui-card">
            <div class="layui-card-header">
                <div class="layui-row">
                    <?php
                        $breadcrumb=array(
                            array('url'=>'?do=admin:user:index','title'=>'用户管理'),
                            array('title'=>'角色管理'),
                        );
                    ?>
                    <div id="cms-breadcrumb">{this:breadcrumb($breadcrumb)}</div>
                    <div id="cms-right-top-button">
                        {if P('user:roleAddPost')}<a href="?do=admin:user:roleEdit" class="layui-btn layui-btn-sm layui-btn-danger"><i class="layui-icon layui-icon-add-1"></i><b>增加角色</b></a>{/if}
                    </div>
                </div>
            </div>
          <div class="layui-card-body layui-form">
            <table class="layui-table" lay-skin="line">
            <colgroup>
              <col>
              <col>
              <col>
            </colgroup>
            <thead>
              <tr>
                <th>名称</th>
                <th>标识</th>
                <th class="layui-btn-td"></th>
              </tr> 
            </thead>
            <tbody id="roles">
                {$roles=cms:user:roleAll()}
                {loop $roles as $role}
                <tr  rel="{$role.hash}">
                <td>
                    <i class="layui-icon layui-icon-find-fill sortable-color"></i> <span{if $role.enabled==0} class="cms-text-disabled"{/if}>{$role.rolename}</span>
                </td>
                <td>
                    {$role.hash}
                </td>
                <td class="btn">
                    <a class="layui-btn layui-btn-sm layui-btn-primary" href="?do=admin:user:index&hash=&rolehash={$role.hash}">用户</a>
                    <a class="layui-btn layui-btn-sm layui-btn-primary" href="?do=admin:user:rolePermission&hash={$role.hash}">权限</a>
                    <a class="layui-btn layui-btn-sm layui-btn-primary" href="?do=admin:user:roleEdit&hash={$role.hash}">修改</a>
                    <a class="layui-btn layui-btn-sm layui-btn-primary  roledel" rel="{$role.hash}">删除</a>
                </td>
                </tr>
            {/loop}
            </tbody>
          </table>
            <div class="layui-row">
              <div id="cms-left-bottom-button" class="layui-btn-container"></div>
              <div id="cms-right-bottom-button" class="layui-btn-container"></div>
            </div>
          </div>
      </div>
    </div>
  </div>

<script>
layui.use(['index','sortable'],function(){
    new Sortable(roles, {
        handle: '.layui-icon',
        onSort: function (evt) {
            rolesarray='';
            layui.$('#roles tr').each(function(){
                rolesarray=rolesarray+'|'+layui.$(this).attr('rel');
            });
            layui.admin.req({type:'post',url:"?do=admin:user:roleOrder",data:{ rolesarray: rolesarray},async:true,beforeSend:function(){
                layui.admin.load('修改排序中...');
            },done: function(res){
            }});
        }
    });
    layui.$('.roledel').click(function(){
        delrolehash=layui.$(this).attr('rel');
        layui.layer.confirm('是否删除此角色:'+layui.$(this).parents('tr').find('td').eq(0).text().replace('<','&lt;').replace('>','&gt;'), {
          btn: ['删除','取消'],skin:'layer-danger',title:'请确认'}, function(){
            layui.admin.req({type:'post',url:"?do=admin:user:roleDel",data:{ hash: delrolehash},async:true,beforeSend:function(){
                layui.admin.load('删除中...');
            },done: function(res){
                if (res.error==0)
                {
                    layer.confirm(res.msg, {btn: ['好的'],shadeClose:1}, function(){layui.admin.events.reload();});
                }
            }});
        });
    });
});
</script>
{this:body:~()}
</body>
</html>