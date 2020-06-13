<?php if(!defined('ClassCms')) {exit();}?>
{//右侧子栏目}
{$navs=nav($.id)}
{if count($navs)}
  <div class="box">
      <div class="title">子栏目</div>
      <ul>
        {loop $navs as $nav}
            <li class="{if $nav.active} active{/if}"><a href="{$nav.link}">{$nav.channelname}</a></li>
        {/loop}
      </ul>
  </div>
{elseif $.fid==0}
  
{else}
  {$navs=nav($.fid)}
  {if count($navs)>1}
    <div class="box">
      <div class="title">栏目</div>
      <ul>
        {loop $navs as $nav}
            <li class="{if $nav.active} active{/if}"><a href="{$nav.link}">{$nav.channelname}</a></li>
        {/loop}
      </ul>
    </div>
  {/if}
{/if}