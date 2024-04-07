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
                            {if $auth.add}<a href="{$url.add}" class="layui-btn layui-btn-sm layui-btn-danger"><i class="layui-icon layui-icon-add-1"></i><b>增加</b></a>{/if}
                            {if $auth.var}<a href="{$url.var}" class="layui-btn layui-btn-sm layui-btn-danger">设置</a>{/if}
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
                            {loop $article._btns as $btn}
                                {$btn.html}
                            {/loop}
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
                layui.layer.msg('请先选择');
                return;
            }
            layui.layer.confirm('是否删除?', {
              btn: ['删除','取消'],skin:'layer-danger',title:'请确认',shadeClose:1}, function(){
                layui.admin.req({type:'post',url:"{$url.del}",data:{ ids: ids,cid:{$channel.id}},async:true,tips:'删除中...',done: function(res){
                    if (res.error==0)
                    {
                        layui.layer.msg(res.msg);
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
