<?php if(!defined('ClassCms')) {exit();}?>
<div class="layui-container header-container">
    <div class="header">
        <div class="layui-row">
            <div class="logo layui-col-xs9 layui-col-md4 layui-col-lg4">
                <a href="{$.0.link}">{$.0.logo}</a>
            </div>
            <div class="menu layui-col-xs3 layui-col-md8 layui-col-lg8">
                <span><i class="layui-icon layui-icon-template-1"></i></span>
            </div>
        </div>
    </div>
</div>
<div class="nav">
    <div class="layui-container">
        <ul class="layui-nav">
        {loop nav() as $nav}
            <li class="layui-nav-item{if $nav.active} active{/if}"><a href="{$nav.link}">{$nav.channelname}</a></li>
        {/loop}
        </ul>
    </div>
</div>
{if $.id!=$.0.id}
<div class="layui-container">
    <div class="breadcrumb">
        <span>当前位置 : </span>
        {loop bread() as $bread}
            {if $bread.active}
                {if isset($title)}
                    <a href="{$bread.link}">{$bread.channelname}</a>
                {else}
                    {$bread.channelname}
                {/if}
            {else}
                <a href="{$bread.link}">{$bread.channelname}</a>&gt;
            {/if}
        {/loop}
    </div>
</div>
{/if}