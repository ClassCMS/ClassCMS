<?php if(!defined('ClassCms')) {exit();}?>
<input type="checkbox" lay-filter="{$name}_switch{if isset($article.id)}_{$article.id}{/if}" {if isset($value) && $value} checked{/if}{if $disabled} disabled{/if} lay-skin="switch"  lay-text="{$opentips}|{$closetips}">

{if !$disabled}
<script>
layui.use(['index','form','jquery'], function(){
    layui.form.on('switch({$name}_switch{if isset($article.id)}_{$article.id}{/if})', function(obj){
        layui.admin.req({type:'post',url:"{$ajax_url}",data:{ name: '{$name}', state: obj.elem.checked,articleid:{$article.id},cid:{$article.cid}},async:true,beforeSend:function(){
            layui.admin.load('请稍等...');
        },done: function(res){
            if (res.msg)
            {
                layui.layer.msg(res.msg);
            }
        }});
    });
});
</script>
{/if}