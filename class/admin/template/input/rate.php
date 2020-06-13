<?php if(!defined('ClassCms')) {exit();}?>
<div id="{$name}_div"></div>
<input id="{$name}_rate" type="hidden" name="{$name}" value="{$value}">
<script>
    layui.use(['rate'],function(){
        var $ = layui.$,rate = layui.rate;
        rate.render({
            elem: '#{$name}_div'
            ,theme: '{$color}'
            ,value: {$value}
            {if $showtext},text: true{/if}
            ,choose: function(val){
              $('#{$name}_rate').val(val);
            }
            {if $half},half: true{/if}
            {if $disabled},readonly: true{/if}
            {if $stars},length: {$stars}{/if}
        });
    });
</script>