<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>{admin:head:(应用商店)}</head>
<body>
{if $html}
<div class="layui-fluid">
    <div class="layui-row">
        <div class="layui-card">
            <div class="layui-card-header">
                <div class="layui-row">
                    <div id="cms-breadcrumb">{admin:breadcrumb($breadcrumb)}</div>
                    <div id="cms-right-top-button">{if count($breadcrumb)==1}<a href="//classcms.com" target="_blank" class="layui-btn layui-btn-sm layui-btn-primary"><i class="layui-icon layui-icon-website" ></i><b>ClassCMS.COM</b></a>{/if}</div>
                </div>
            </div>
            <div class="layui-card-body">
                {$content}
            </div>
        </div>
     </div>
</div>
{else}
{$content}
{/if}
{admin:body:~()}
</body>
</html>