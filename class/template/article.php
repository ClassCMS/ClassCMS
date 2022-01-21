<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,user-scalable=no,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0">
    <title>{if isset($.title) && !empty($.title)}{$.title}{else}{$.channelname}{/if}</title>
    {if isset($.keywords)}<meta name="keywords" content="{$.keywords}">{br}{/if}
    {if isset($.description)}<meta name="description" content="{$.description}">{br}{/if}
    {layui:css()}
    <link rel="stylesheet" href="{template}css/style.css">
</head>
<body>
{file header}
<div class="layui-container">
    <div class="layui-row">
        <div class="layui-col-md8 mainleft">
            <div class="article_list">
                {$articlelist.page=page}
                {$articlelist.all=1}
                {$articles=a($articlelist)}
                {loop $articles as $article}
                    <div class="item">
                        <div class="title"><a href="{$article.link}">{$article.title}</a></div>
                        <div class="content">{text($article.content,150)}...</div>
                        <div class="info"><i class="layui-icon layui-icon-time"></i> {date(Y-m-d,$article.datetime)}</div>
                    </div>
                {/loop}
            </div>
            <div class="pagelist">
                {this:pagelist()}
            </div>
        </div>
        <div class="layui-col-md4 mainright">
          {file left_channel}
          <div class="box">
              <div class="title">推荐文章</div>
              <ul>
                {$hotlist.where.recommend=1}
                {$hotlist.modulehash=article}
                {$hotlist.pagesize=10}
                {$articles=a($hotlist)}
                {loop $articles as $article}
                    <li><a href="{$article.link}">{$article.title}</a></li>
                {/loop}
              </ul>
          </div>
          <div class="box">
              <div class="title">广告</div>
              <div>{$.0.rightad}</div>
          </div>
        </div>
    </div>
</div>
{file footer}
</body>
</html>