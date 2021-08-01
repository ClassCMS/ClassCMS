<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>{this:head(应用管理)}</head>
<body>
  <div class="layui-fluid">
    <div class="layui-row">
        <div class="layui-card">

            <div class="layui-card-header">
                <div class="layui-row">
                    <?php
                        $breadcrumb=array(
                            array('url'=>'','title'=>'应用管理')
                        );
                    ?>
                    <div id="cms-breadcrumb">{this:breadcrumb($breadcrumb)}</div>
                    <div id="cms-right-top-button"></div>
                </div>
            </div>
          <div class="layui-card-body layui-form">
            {if $newclass}
            <blockquote class="layui-elem-quote layui-text">
                新增应用:{$newclass}
            </blockquote>
            {/if}
            <table class="layui-table" lay-skin="line" >
            <colgroup>
              <col>
              <col>
              <col>
              <col>
              <col>
            </colgroup>
            <thead>
              <tr>
                <th>应用名</th>
                <th class="layui-hide-xss">标识</th>
                <th class="layui-show-md-td">版本</th>
                <th></th>
              </tr> 
            </thead>
            <tbody>
                {loop $classlist as $class}
                    <tr rel="{$class.hash}">
                    <td>
                        <i class="layui-icon {$class.ico}{if $class.enabled==1} cmscolor{/if}"></i>
                        {if P('class:config')}
                        <a href="?do=admin:class:config&hash={$class.hash}"><span{if !$class.enabled} class="cms-text-disabled"{/if}>{$class.classname}</span></a>
                        {else}
                        <span{if !$class.enabled} class="cms-text-disabled"{/if}>{$class.classname}</span>
                        {/if}
                        {if $class.classorder>1} <i class="layui-icon layui-icon-rate" title="置顶应用"></i>{/if}
                    </td>
                    <th class="layui-hide-xss">{$class.hash}</th>
                    <th class="layui-show-md-td">{$class.classversion}</th>
                    <td class="btn">
                        <a rel="{if !empty($class.adminpage)}?do={$class.hash}:{$class.adminpage}{/if}" {if !empty($class.adminpage) && $class.enabled}href="?do={$class.hash}:{$class.adminpage}"{/if} class="layui-btn layui-btn-sm layui-btn-primary{if empty($class.adminpage) || !$class.enabled} layui-btn-disabled{/if}">主页</a>
                        {if P('class:config')}<a class="layui-btn layui-btn-sm layui-btn-primary" href="?do=admin:class:config&hash={$class.hash}" rel="{$class.hash}">管理</a>{/if}
                    </td>
                    </tr>
                {/loop}
            </tbody>
          </table>

<div class="layui-row">
    <div id="cms-left-bottom-button" class="layui-btn-container">
        {if P('class:upload') && $GLOBALS['C']['Debug']}<button type="button" class="layui-btn layui-btn-sm layui-btn-normal" id="uploadclass"><i class="layui-icon"></i>上传安装包</button>{/if}
    </div>
    <div id="cms-right-bottom-button" class="layui-btn-container">
    </div>
</div>
          </div>
        </div>
    </div>
  </div>
<script>
layui.use(['index','form','upload'],function(){
    var $ = layui.$;
    layui.upload.render({
        elem: '#uploadclass'
        ,url: '?do=admin:class:upload'
        ,field:'zipfile'
        ,accept: 'file'
        ,data:{csrf:'{this:csrfForm()}'}
        ,acceptMime:'application/zip'
        ,exts:'zip'
        ,before:function(){
            layui.admin.load('上传中...');
        }
        ,done: function(res){
            layui.admin.loaded();
            if (res.error==0)
            {
                layer.confirm(res.msg, {
                        btn: ['刷新','取消'],
                        shadeClose:1
                    }, function(){
                      layui.admin.events.reload();
                    }, function(){
                    });
            }else{
                layui.admin.popup({content: res.msg,area: '300px',offset: '15px'});
            }
        }
    });
});
</script>
{this:body:~()}
</body>
</html>