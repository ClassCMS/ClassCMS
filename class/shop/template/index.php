<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head> {admin:head:(应用商店)} </head>
<body>
{if $html}
<div class="layui-fluid">
    <div class="layui-row">
        <div class="layui-card">

            {if $breadcrumb}
                <div class="layui-card-header">
                    <div class="layui-row">
                        <div id="cms-breadcrumb">{admin:breadcrumb($breadcrumb)}</div>
                        <div id="cms-right-top-button">{if count($breadcrumb)==1}<a href="https://classcms.com" target="_blank" class="layui-btn layui-btn-sm layui-btn-primary"><i class="layui-icon layui-icon-website" ></i><b>ClassCMS.COM</b></a>{else}<a href="javascript:history.go(-1);" title="返回" class="layui-btn layui-btn-sm layui-btn-primary"><i class="layui-icon layui-icon-return" ></i><b>返回</b></a>{/if}</div>
                    </div>
                </div>
            {/if}

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