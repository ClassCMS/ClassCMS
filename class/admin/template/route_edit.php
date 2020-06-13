<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>{this:head($title)}</head>
<body>

  <div class="layui-fluid" id="component-tabs">
    <div class="layui-row">

<div class="layui-form">

{if isset($route.id)}
<input type="hidden" name="id" value="{$route.id}">
{else}
<input type="hidden" name="moduleid" value="{$module.id}">
{/if}

    <div class="layui-card">
        <div class="layui-card-header">
            <div class="layui-row">
                <div id="cms-breadcrumb">{this:breadcrumb($breadcrumb)}</div>
                <div id="cms-right-top-button"></div>
            </div>
        </div>

        <div class="layui-card-body">

                  <div class="layui-form-item layui-form-item-width-auto">
                    <label class="layui-form-label">网址</label>
                        <div class="layui-input-right">
                            <div class="layui-input-block">
                              <input type="text" style="letter-spacing:.5px" name="uri" value="{if isset($route.uri)}{$route.uri}{/if}" class="layui-input" placeholder="网址应避免与其它页面网址重复">
                            </div>
                            <div class="layui-form-mid layui-btn-container">
                                <a class="layui-btn layui-btn-sm layui-btn-primary" layadmin-event="change_input" input-name="uri" add-text="/">/</a>
                                <a class="layui-btn layui-btn-sm layui-btn-primary" layadmin-event="change_input" input-name="uri" add-text="($.id)">栏目ID</a>
                                <a class="layui-btn layui-btn-sm layui-btn-primary" layadmin-event="change_input" input-name="uri" add-text="($.channelname)">栏目名</a>
                                <a class="layui-btn layui-btn-sm layui-btn-primary" layadmin-event="change_input" input-name="uri" add-text="page_(page)">分页</a>
                                <a class="layui-btn layui-btn-sm layui-btn-primary" layadmin-event="change_input" input-name="uri" add-text="($id)">文章ID</a>
                                <a class="layui-btn layui-btn-sm layui-btn-primary" layadmin-event="change_input" input-name="uri" add-text=".html">.html</a>
                                <a class="layui-btn layui-btn-sm layui-btn-danger" layadmin-event="change_input" input-name="uri" set-text="">重置</a>
                            </div>
                        </div>
                  </div>

                  <div class="layui-form-item layui-form-item-width-auto">
                    <label class="layui-form-label">标识</label>
                    <div class="layui-input-right">
                    <div class="layui-input-block">
                      <input type="text" name="hash" value="{if isset($route.hash)}{$route.hash}{/if}"  class="layui-input" {if isset($route.hash)}readonly{/if}>
                    </div>
                    <div class="layui-form-mid">{if isset($route.hash)}标识无法更改{else}标识格式为字母或(字母,数字,_)组合.默认栏目页标识为channel,列表页为list,文章页为article{/if}</div>
                    </div>
                  </div>

                  <div class="layui-form-item">
                    <label class="layui-form-label">启用</label>
                    <div class="layui-input-right">
                        <div class="layui-input-block">
                            {$enabled_input_config.name=enabled}
                            {if isset($route['enabled'])}
                                {$enabled_input_config.value=$route.enabled}
                            {else}
                               {$enabled_input_config.value=1}
                            {/if}
                            {$enabled_input_config.inputhash=switch}
                            {cms:input:form($enabled_input_config)}
                        </div>
                        <div class="layui-form-mid">
                            禁用后,此页面将无法访问
                        </div>
                    </div>
                  </div>


                  <div class="layui-form-item layui-form-item-width-auto">
                    <label class="layui-form-label">方法名</label>
                        <div class="layui-input-right">
                            <div class="layui-input-block">
                              <input type="text" name="classfunction" value="{if isset($route.classfunction)}{$route.classfunction}{/if}" class="layui-input" >
                            </div>
                            <div class="layui-form-mid">选填,访问页面时,{if isset($module.classhash)}{$module.classhash}:{/if}方法名 将会被调用</div>
                        </div>
                  </div>

                  <div class="layui-form-item layui-form-item-width-auto">
                    <label class="layui-form-label">模板文件</label>
                        <div class="layui-input-right">
                            <div class="layui-input-block">
                              <input type="text" name="classview" value="{if isset($route.classview)}{$route.classview}{/if}" class="layui-input" >
                            </div>
                            <div class="layui-form-mid">选填,访问页面时,此应用{if isset($module.classhash)}({$module.classhash}){/if}下的模板文件将会被调用,后缀(.php)可不填</div>
                        </div>
                  </div>

        </div>
    </div>

    <div class="layui-form-item layui-layout-admin">
        <div class="layui-input-block">
            <div class="layui-footer">
            <button class="layui-btn layui-btn-normal cms-btn" lay-submit="" lay-filter="form-submit">{if isset($route.id)}保存{else}增加{/if}</button>
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
        layui.admin.req({type:'post',url:"?do=admin:route:{if isset($route.id)}editPost{else}addPost{/if}",data:data.field,async:true,beforeSend:function(){
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
