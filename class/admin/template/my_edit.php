<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>{this:head(账号管理)}</head>
<body>

  <div class="layui-fluid">
    <div class="layui-row">

<div class="layui-form">
    <div class="layui-card">
        <div class="layui-card-header">
            <div class="layui-row">
                <div id="cms-breadcrumb">{this:breadcrumb($breadcrumb)}</div>
                <div id="cms-right-top-button"></div>
            </div>
        </div>


        <div class="layui-card-body">
                  <div class="layui-form-item layui-form-item-width-auto">
                    <label class="layui-form-label">昵称</label>
                        <div class="layui-input-right">
                            <div class="layui-input-block">
                              <input type="text" name="username" value="{$userinfo.username}" class="layui-input"  lay-verify="required">
                            </div>
                            <div class="layui-form-mid"></div>
                        </div>
                  </div>

                  <div class="layui-form-item layui-form-item-width-auto">
                    <label class="layui-form-label">账号</label>
                        <div class="layui-input-right">
                            <div class="layui-input-block">
                              <input type="text" name="hash" value="{$userinfo.hash}" class="layui-input"  lay-verify="required" readonly disabled>
                            </div>
                            <div class="layui-form-mid">账号无法更改</div>
                        </div>
                  </div>

                  <div class="layui-form-item layui-form-item-width-auto">
                    <label class="layui-form-label">密码</label>
                    <div class="layui-input-right">
                        <div class="layui-input-block">
                            {cms:input:form($password_input)}
                        </div>
                        <div class="layui-form-mid">不更改则无需填写</div>
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
        layui.admin.req({type:'post',url:"?do=admin:my:editPost",data:data.field,async:true,beforeSend:function(){
            layui.admin.load('提交中...');
        },end :function(){
                if (res.refresh)
                {
                    parent.location.reload();
                    layui.admin.events.reload();
                }
                layui.layer.close(confirm);
            },
            done: function(res){
            if (res.error==0)
            {
                var confirm=layer.confirm(res.msg, {btn: ['好的','返回'],shadeClose:1},function(){
                        if (res.refresh)
                        {
                            parent.location.reload();
                            layui.admin.events.reload();
                        }
                        layui.layer.close(confirm);
                    },function(){
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
