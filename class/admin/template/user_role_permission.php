<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>{this:head($title)}</head>
<body>
  <div class="layui-fluid" id="component-tabs">
    <div class="layui-row">

<div class="layui-form">
<input type="hidden" name="rolehash" value="{$hash}">
    <div class="layui-card">
        <div class="layui-card-header">
            <div class="layui-row">
                <?php
                    $breadcrumb=array(
                        array('url'=>'?do=admin:user:index','title'=>'用户管理'),
                        array('url'=>'?do=admin:user:roleIndex','title'=>'角色管理'),
                        array('url'=>'','title'=>$rolename.'['.$hash.'] 权限'),
                    );
                ?>
                <div id="cms-breadcrumb">{this:breadcrumb($breadcrumb)}</div>
                <div id="cms-right-top-button"></div>
            </div>
        </div>

        <div class="layui-card-body">
            {if $superadmin}
                <blockquote class="layui-elem-quote layui-text">
                    {$hash} 为管理员角色,拥有全部权限.
                </blockquote>
            {else}
                <blockquote class="layui-elem-quote layui-text">
                    注意:某些权限可能会造成越权访问(如:应用管理,角色管理等),勾选这些权限后,用户相当于获取了管理员权限
                </blockquote>
            {/if}
        </div>
    </div>

<?php
$classlist=C('cms:class:all');
foreach($classlist as $class) {
    if($class['auth']) {
        $class_auth=C($class['hash'].':auth');
    }else {
        $class_auth=false;
    }
    if($class_auth && is_array($class_auth)) {
        foreach($class_auth as $key=>$val) {
            if(is_array($val)) {
                $table_auth=1;
            }else {
                $table_auth=0;
            }
            break;
        }
?>
<div class="layui-card">
<div class="layui-card-header">{$class.classname} <input type="checkbox" name="{$class.hash}"  lay-filter="checkall" lay-skin="primary"{if $superadmin} disabled{/if}></div>
<div class="layui-card-body class_auth_list" id="{$class.hash}_auth_list">

{if $table_auth}
    <table class="layui-table" lay-size="sm">
      <tbody>
      {loop $class_auth as $tablename=>$this_auth_list}
        <tr>
          <td style="min-width:50px">{$tablename}</td>
          <td>
              {loop $this_auth_list as $this_key=>$this_auth}
              <?php
                  $this_keys=explode(';',$this_key);
                  foreach($this_keys as $key=>$val) {
                    if(empty($val)) {
                        unset($this_keys[$key]);
                    }else {
                        $this_keys[$key]=$class['hash'].':'.$val;
                    }
                  }
                  $this_key=implode(';',$this_keys);
              ?>
                <input type="checkbox" name="{$this_key}" title="{$this_auth}" lay-filter="checkone" lay-skin="primary"{if C('this:actionsCheck',$this_key,$hash)} checked{/if}{if $superadmin} disabled{/if}>
              {/loop}
          </td>
        </tr>
      {/loop}
      </tbody>
    </table>
{else}
    {loop $class_auth as $this_key=>$this_auth}
        <?php
            $this_keys=explode(';',$this_key);
            foreach($this_keys as $key=>$val) {
                if(empty($val)) {
                    unset($this_keys[$key]);
                }else {
                    $this_keys[$key]=$class['hash'].':'.$val;
                }
            }
            $this_key=implode(';',$this_keys);
        ?>
        <input type="checkbox" name="{$this_key}" title="{$this_auth}" lay-filter="checkone" lay-skin="primary"{if C('this:actionsCheck',$this_key,$hash)} checked{/if}{if $superadmin} disabled{/if}>
    {/loop}
{/if}

</div>
</div>
<?php
    }
}
?>

    <div class="layui-form-item layui-layout-admin">
        <div class="layui-input-block">
            <div class="layui-footer">
            <button class="layui-btn {if $superadmin}layui-btn-disabled{else}cms-btn{/if}" lay-submit="" lay-filter="form-submit">保存</button>
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
                    layui.$(this).prop("checked", true);
                });
            }else{
                layui.$('#'+obj.elem.name+'_auth_list input').each(function(){
                    layui.$(this).prop("checked", false);
                });
            }
            layui.form.render();
        });
        layui.$('.class_auth_list').each(function(){
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
          layui.$(data.elem).parents('.class_auth_list').find('input[type=checkbox]').each(function(){
                if (layui.$(this).prop("checked"))
                {
                    someone_checked=true;
                }
          });
          if (someone_checked)
          {
              layui.$(data.elem).parents('.class_auth_list').prev().find('input[type=checkbox]').prop("checked", true);
          }else{
              layui.$(data.elem).parents('.class_auth_list').prev().find('input[type=checkbox]').prop("checked", false);
          }
          layui.form.render();
        });
        {if !$superadmin}
        layui.form.on('submit(form-submit)', function(data){
          layui.$('button[lay-filter=form-submit]').blur();
          layui.admin.req({type:'post',url:"?do=admin:user:rolePermissionPost",data:data.field,async:true,beforeSend:function(){
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
        {/if}
    });
</script>
{this:body:~()}
</body>
</html>
