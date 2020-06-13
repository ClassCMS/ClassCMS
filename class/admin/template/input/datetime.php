<?php if(!defined('ClassCms')) {exit();}?>
<input type="text" class="layui-input" id="{$name}_datetime" value="{$value}" name="{$name}"  autocomplete="off" {if $disabled} readonly disabled{/if}>
{if !$disabled}
<script>
layui.use('laydate', function(){
  var laydate = layui.laydate;
  laydate.render({
    elem: '#{$name}_datetime'
    ,isInitValue: true
    ,theme: '#1E9FFF'
    {if isset($time) && $time},type: 'datetime'{/if}
  });
});
</script>
{/if}