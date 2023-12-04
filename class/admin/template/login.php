<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>{this:head(登入)}{this:loginHead:~()}</head>
<body>
<div class="layadmin-user-login layadmin-user-display-show" id="LAY-user-login">
    <div class="layadmin-user-login-main">
      <div class="layadmin-user-login-box layadmin-user-login-header"><h2 class="cmscolor">{this:loginTitle()}</h2></div>
          <div class="layadmin-user-login-box layadmin-user-login-body layui-form" lay-filter="classcms-login-form">
                <div class="layui-form-item">
                    <label class="layadmin-user-login-icon layui-icon layui-icon-username" for="LAY-user-login-username"></label>
                    <input type="text" name="userhash" value="{if isset($_GET.u)}{htmlspecialchars($_GET.u)}{/if}" id="LAY-user-login-username" lay-verify="" placeholder="账号" class="layui-input">
                </div>
                <div class="layui-form-item">
                    <label class="layadmin-user-login-icon layui-icon layui-icon-password" for="LAY-user-login-password"></label>
                    <input type="password" name="passwd" value="{if isset($_GET.p)}{htmlspecialchars($_GET.p)}{/if}" id="LAY-user-login-password" lay-verify="" placeholder="密码" class="layui-input">
                </div>
                {this:loginFormitem:~()}
                <div class="layui-form-item" style="margin-top: 20px;">
                    <button class="layui-btn layui-btn-fluid cms-btn" lay-submit lay-filter="classcms-login-submit">登 入</button>
                </div>
                <div class="layui-trans layui-form-item layadmin-user-login-other">
                {this:loginIco:~()}
                </div>
          </div>
    </div>
    {this:loginCopyright()}
</div>
<script>
layui.use(['form'],function(){
    function loginpost(){
        var data = layui.form.val("classcms-login-form");
        layui.admin.req({type:'post',url:"?do=admin:login",data:data,async:true,beforeSend:function(){
            layui.admin.load('登入中...');
        },done: function(res){
            if (res.error==0)
            {
                if (res.link) {
                    window.location.href=res.link;
                }else{
                    layui.admin.events.reload();
                }
                return ;
            }
        }});
        return false;
    }
    layui.$('.layui-form input').bind('keypress',function(event){if(event.keyCode == "13"){loginpost();}});
    layui.form.on('submit(classcms-login-submit)', function(data){loginpost();});
    device = layui.device();
    if(device.ie && device.ie < 10){
        alert('IE'+ device.ie + '下浏览效果可能不佳，推荐使用：Chrome / Firefox / Edge 等高级浏览器');
    }
});
</script>
{this:loginBody:~()}
</body>
</html>