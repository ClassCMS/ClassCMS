<?php if(!defined('ClassCms')) {exit();}?>
<div id="{$name}_div"></div>
<input id="{$name}_input" type="hidden" name="{$name}" value="{$value}">
<script>
    layui.use(['transfer'],function(){
        var $ = layui.$
        layui.transfer.render({
            elem: '#{$name}_div'
            ,id:'{$name}_transfer'
            ,title: ['{if $title_left}{$title_left}{/if}', '{if $title_right}{$title_right}{/if}']
            ,data:{$values}
            ,value: {$json_value}
            {if !$disabled}
            ,onchange: function(obj, index){
              var getData = layui.transfer.getData('{$name}_transfer');
              $('#{$name}_input').val('');
              for(index = 0;index<getData.length; index++) {
                    $('#{$name}_input').val($('#{$name}_input').val()+getData[index]['value']+';');
              }
            }
            {/if}
            {if $search},showSearch: true{/if}
            {if $width},width: {$width}{/if}
            {if $height},height: {$height}{/if}
        });
    });
</script>
