<?php if(!defined('ClassCms')) {exit();}?>
<input type="hidden" name="{$name}" id="{$name}_colorpicker_input" value="{$value}">
{if $disabled}
<div id="{$name}_colorpicker" class="layui-inline">
    <div class="layui-unselect layui-colorpicker">
        <span class="layui-colorpicker-trigger-bgcolor">
        <span class="layui-colorpicker-trigger-span" style="background: {$value};"><i class="layui-icon layui-colorpicker-trigger-i "></i></span>
        </span>
    </div>
</div>
{else}
<div id="{$name}_colorpicker"></div>
<script>
layui.use('colorpicker', function(){
  var colorpicker_{$name} = layui.colorpicker;
  colorpicker_{$name}.render({
    elem: '#{$name}_colorpicker'
    ,color:'{$value}'
    {if isset($rgb) && $rgb},format:'rgb'{if isset($alpha) && $alpha},alpha:true{/if}{/if}
    ,done: function(color){
      layui.$('#{$name}_colorpicker_input').val(color);
    }
  });
});
</script>
{/if}
