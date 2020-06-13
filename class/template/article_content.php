<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,user-scalable=no,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0">
    <title>{if isset($title)}{$title}{/if}</title>{br}
    {if isset($keywords)}<meta name="keywords" content="{$keywords}">{br}{/if}
    {if isset($description)}<meta name="description" content="{$description}">{br}{/if}
    {layui:css()}
    <link rel="stylesheet" href="{template}css/style.css">
</head>
<body>
{file header}
<div class="layui-container">
    <div class="layui-row">
        <div class="layui-col-md8 mainleft">
            <div class="article_content">
                <h1>{$title}</h1>
                <div class="content">{$content}</div>
            </div>
        </div>
        <div class="layui-col-md4 mainright">
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