<?php if(!defined('ClassCms')) {exit();}?>
<div id="{$name}_div" class="slider-input" style="padding-top:18px"></div>
<input id="{$name}_slider" type="hidden" name="{$name}" value="{$value}">
<script>
    layui.use(['slider'],function(){
        var $ = layui.$,slider = layui.slider;
        slider.render({
            elem: '#{$name}_div'
            ,theme: '#1E9FFF'
            ,value: '{$value}'
            ,change: function(val){
              $('#{$name}_slider').val(val);
            }
            {if $type && $type==2},type: 'vertical'{/if}
            {if $min},min: {$min}{/if}
            {if $max},max: {$max}{/if}
            {if $disabled},disabled: true{/if}
            {if $step},step:{$step}{/if}
            {if $step && $showstep},showstep: true{/if}
        });
    });
</script>