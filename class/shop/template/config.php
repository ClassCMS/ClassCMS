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
                layui.$('#requires td').on('click','a',function(){
                    var this_require_state = layui.$(this).attr('data-state');
                    var this_require_hash = layui.$(this).attr('data-hash');
                    if(this_require_state==4){
                        if(layui.$(window).width()<900){ width=layui.$(window).width(); }else{ width=880; }
                            if(layui.$(window).height()<700){ height=layui.$(window).height(); }else{ height=680; }
                            layui.layer.open({
                                type: 2,
                                title: this_require_hash,
                                shadeClose: true,
                                area: [width+'px', height+'px'],
                                content: '?do=shop:index&action=detail&nobread=1&classhash='+this_require_hash
                            });
                    }else{
                        if(layui.$(window).width()<900){ width=layui.$(window).width(); }else{ width=880; }
                        if(layui.$(window).height()<700){ height=layui.$(window).height(); }else{ height=680; }
                        layui.layer.open({
                            type: 2,
                            title: this_require_hash,
                            shadeClose: true,
                            area: [width+'px', height+'px'],
                            content: '?do=admin:class:config&nobread=1&hash='+this_require_hash
                        });
                    }
                });
            }
            {if $nobread}
                layui.$('body>.layui-fluid>.layui-row>.layui-card>.layui-card-header').hide();
            {else}
                layui.$('#cms-right-top-button').append('<a href="?do=shop:index&action=detail&classhash={$hash}" class="layui-btn layui-btn-sm layui-btn-danger"><i class="layui-icon layui-icon-cart-simple"></i><b>应用商店</b></a>');
            {/if}
        }
    }});
});
</script>