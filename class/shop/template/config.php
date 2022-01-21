<?php if(!defined('ClassCms')) {exit();}?>
<script>
layui.use(['index'],function(){
    {if $homeroute>1}layui.$('#classname').parent().parent().parent().append('<blockquote class="layui-elem-quote layui-text">系统存在多个模板应用,推荐安装 <a class="layui-btn layui-btn-xs layui-btn-normal" href="?do=shop:index&action=detail&classhash=domainbind&bread=域名绑定"></i>域名绑定</a> 为每个应用绑定不同的域名或页面前缀,避免页面网址冲突.</blockquote>');{/if}
    layui.admin.req({type:'post',url:"?do=shop:adminconfig",data:{ hash: '{$hash}'},async:true,beforeSend:function(){
        
    },done: function(res){
        if (res.error==0)
        {
            if (res.requires.length>10)
            {
                layui.$('#requires').find('td').eq(1).html(res.requires);
            }
            layui.$('#cms-right-top-button').append('<a href="?do=shop:index&action=detail&classhash={$hash}&bread={$classname}" class="layui-btn layui-btn-sm layui-btn-danger"><i class="layui-icon layui-icon-cart-simple"></i><b>应用商店</b></a>');
        }
    }});
    
});
</script>