<?php if(!defined('ClassCms')) {exit();}?>
<script>
layui.use(['index'],function(){
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