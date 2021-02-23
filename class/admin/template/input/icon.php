<?php if(!defined('ClassCms')) {exit();}?>
<input id="{$name}_icon" type="hidden" name="{$name}" value="{$value}">
<button type="button" id="{$name}_icon_btn" class="layui-btn  layui-btn-primary">
    {if $value}<i class="layui-icon {$value}"></i>{else}选择{/if}
</button>
<div id="{$name}_icon_table" style="display:none">
{$icons=layui:icon_list()}
{if count($icons)}
    <ul>
    {loop $icons as $key=>$icon}
        {if $key==$value}
            <li style="display:inline-block;padding:4px;font-size:30px;color:#1E9FFF" rel="{$key}"><i class="layui-icon {$key}"></i></li>
        {else}
            <li style="display:inline-block;padding:4px;font-size:30px;color:#000" rel="{$key}"><i class="layui-icon {$key}"></i></li>
        {/if}
    {/loop}
    <li style="display:inline-block;padding:4px;font-size:14px;cursor:pointer" class="cmscolor" rel="">空</li>
    </ul>
{else}
    无法获取图标列表
{/if}
</div>
<script>
    layui.use(['layer'],function(){
        layui.$('#{$name}_icon_btn').click(function(){
            if (layui.$('body').width()>415)
            {
                maxWidth=415;
            }else{
                maxWidth=layui.$('body').width()-50;
            }
            var {$name}_layer=layui.layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              maxWidth:maxWidth,
              skin: '{$name}_icon_layer_html',
              shadeClose: true,
              content: layui.$('#{$name}_icon_table').html()
            });
            layui.$('.{$name}_icon_layer_html li').click(function(){
                if (layui.$(this).attr('rel'))
                {
                    layui.$('#{$name}_icon_btn').html('<i class="layui-icon '+layui.$(this).attr('rel')+'"></i>');
                    layui.$('#{$name}_icon').val(layui.$(this).attr('rel'));
                }else{
                    layui.$('#{$name}_icon_btn').html('选择');
                    layui.$('#{$name}_icon').val('');
                }
                layui.layer.close({$name}_layer);
            });
        });
    });
</script>