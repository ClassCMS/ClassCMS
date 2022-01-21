<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>{this:head($channel.channelname)}</head>
<body>

<div class="layui-fluid">
    <div class="layui-row">
        <div class="layui-card">

        <div class="layui-card-header">
                <div class="layui-row">
                    <div id="cms-breadcrumb">{this:breadcrumb($breadcrumb)}</div>
                    <div id="cms-right-top-button">
                            {if $auth.add}<a href="?do=admin:article:edit&cid={$channel.id}" class="layui-btn layui-btn-sm layui-btn-danger"><i class="layui-icon layui-icon-add-1"></i><b>增加</b></a>{/if}
                            {if $varEnabled}<a href="?do=admin:article:varEdit&cid={$channel.id}" class="layui-btn layui-btn-sm layui-btn-danger">设置</a>{/if}
                    </div>
                </div>
            </div>
          <div class="layui-card-body layui-form">
            {this:article:listTop:~()}
            {if count($articles)}
                <table class="layui-table" lay-skin="line"  id="articles">
                <colgroup>
                  <col>
                  <col>
                  <col>
                </colgroup>
                <thead>
                  <tr>
                    <th></th>
                    {loop $columns as $column}
                    <th rel="{$column.hash}">{$column.formname}</th>
                    {/loop}
                    <th></th>
                  </tr> 
                </thead>
                <tbody>
                    {loop $articles as $article}
                        <tr rel="{$article.id}">
                        <td style="width:20px"><input type="checkbox" name="check_article" lay-skin="primary" ></td>
                        {loop $columns as $column}
                            <td rel="{$column.hash}">
                                {if !isset($article[$column.hash])}{$article[$column.hash]=''}{/if}
                                {$column.value=$article[$column.hash]}
                                {$column.article=$article}
                                {cms:input:view($column)}
                            </td>
                        {/loop}
                        <td class="btn">
                            {if $viewbutton && $channel.enabled && $channel._module.enabled}<a class="layui-btn layui-btn-sm layui-btn-primary{if !$article.link || $article.link=='#'} layui-btn-disabled{/if}"{if $article.link && $article.link!='#'} href="{$article.link}" target="_blank"{/if}>浏览</a>{/if}
                            {if $auth.edit}<a class="layui-btn layui-btn-sm layui-btn-primary" href="?do=admin:article:edit&cid={$article.cid}&id={$article.id}">修改</a>{/if}
                            {if $auth.del}<a class="layui-btn layui-btn-sm layui-btn-primary articledel">删除</a>{/if}
                        </td>
                        </tr>
                    {/loop}
                </tbody>
              </table>

                <div class="layui-row">
                    <div id="cms-left-bottom-button" class="layui-btn-container">
                        <a class="layui-btn layui-btn-sm layui-btn-primary choseall">全选</a>
                        <a class="layui-btn layui-btn-sm layui-btn-primary choseback">反选</a>
                        {if $auth.del}<a class="layui-btn layui-btn-sm layui-btn-primary delchosed">删除</a>{/if}
                    </div>
                    <div id="cms-right-bottom-button" class="layui-btn-container">{this:pagelist()}</div>
                </div>
            {/if}
          </div>
      </div>
    </div>
  </div>
<script>
function chosedArticle(){
    articles=new Array();
    layui.$('#articles tbody input[name=check_article]').each(function(){
        if (layui.$(this).prop("checked"))
        {
            articles.push(layui.$(this).parents('tr').attr('rel'));
        }
    });
    return articles.join(';');
}
layui.use(['index','form'],function(){
    layui.$('.choseall').click(function(){
        layui.$('#articles tbody input[name=check_article]').prop("checked",true);
        layui.form.render('checkbox');
    });
    layui.$('.choseback').click(function(){
        layui.$('#articles tbody input[name=check_article]').each(function(){
            if (layui.$(this).prop("checked"))
            {
                layui.$(this).prop("checked",false);
            }else{
                layui.$(this).prop("checked",true);
            }
        });
        layui.form.render('checkbox');
    });
    {if $auth.del}
        layui.$('.articledel').click(function(){
            delArticle(layui.$(this).parents('tr').attr('rel'));
        });
        layui.$('.delchosed').click(function(){
            delArticle(chosedArticle());
        });
        function delArticle(ids){
            if (ids.length==0)
            {
                layui.layer.msg('请先选择文章');
                return;
            }
            layui.layer.confirm('是否删除文章', {
              btn: ['删除','取消'],skin:'layer-danger',title:'请确认',shadeClose:1}, function(){
                layui.admin.req({type:'post',url:"?do=admin:article:del",data:{ ids: ids,cid:{$channel.id}},async:true,beforeSend:function(){
                    layui.admin.load('删除中...');
                },done: function(res){
                    if (res.error==0)
                    {
                        layui.layer.msg('删除成功');
                        del_ids = ids.split(";");
                        for (i=0; i<del_ids.length; i++ ){
                            layui.$('#articles tbody tr[rel='+del_ids[i]+']').remove();
                        }
                    }
                    if (layui.$('#articles tbody tr').length==0)
                    {
                        layui.admin.events.reload();
                    }
                }});
            });
        }
    {/if}
});
</script>
{this:body:~()}
</body>
</html>
