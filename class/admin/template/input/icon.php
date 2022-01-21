<?php if(!defined('ClassCms')) {exit();}?>
<input id="{$name}_icon" type="hidden" name="{$name}" value="{$value}">
<button type="button" id="{$name}_icon_btn" class="layui-btn  layui-btn-primary">
    {if $value}<i class="layui-icon {$value}"></i>{else}选择{/if}
</button>
<div id="{$name}_icon_table" style="display:none">
{if count($icons)}
    <div style="padding:2px;max-height:470px;overflow:auto;">
        <div style="padding:5px;border-bottom:1px #eee solid">
            <p style="float:left">
                <input type="text" placeholder="搜索" class="layui-input {$name}_icon-input" style="display:inline-block;width:120px;height:30px">
                <button type="button" class="{$name}_icon-search layui-btn layui-btn-sm cms-btn">搜索</button>
            </p>
            <p style="float:right">
                <button type="button" class="{$name}_icon-cancel layui-btn layui-btn-sm layui-btn-primary">取消选择</button>
                <button type="button" class="{$name}_icon-close layui-btn layui-btn-sm layui-btn-primary">关闭</button>
            </p>
            <div class="layui-clear"></div>
        </div>
        <ul>
        {loop $icons as $key=>$icon}
            {if $key==$value}
                <li style="display:inline-block;padding:6px;border:#1E9FFF solid 1px;cursor:pointer" rel="{$key}" alt="{$icon.0}"><i class="layui-icon {$key}"></i></li>
            {else}
                <li style="display:inline-block;padding:6px;border:none;cursor:pointer" rel="{$key}" alt="{$icon.0}"><i class="layui-icon {$key}"></i></li>
            {/if}
        {/loop}
        </ul>
    </div>
{else}
    无法获取图标列表
{/if}
</div>
<script>
    layui.use(['layer'],function(){
        layui.$('#{$name}_icon_table .layui-icon-login-wechat').css('color','#000');
        layui.$('#{$name}_icon_table .layui-icon-login-qq').css('color','#000');
        layui.$('#{$name}_icon_table .layui-icon-login-weibo').css('color','#000');
        layui.$('#{$name}_icon_btn').click(function(){
            if (layui.$('body').width()>435)
            {
                maxWidth=420;
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
            layui.$('.{$name}_icon-close').click(function(){
                layui.layer.close({$name}_layer);
            });
            layui.$('.{$name}_icon-cancel').click(function(){
                layui.$('#{$name}_icon_btn').html('选择');
                layui.$('#{$name}_icon').val('');
                layui.layer.close({$name}_layer);
            });
            layui.$('.{$name}_icon_layer_html .{$name}_icon-search').click(function(){
                searchword=layui.$('.{$name}_icon_layer_html .{$name}_icon-input').val();
                if (searchword.length==0) {
                    layui.$('.{$name}_icon_layer_html ul li').show();
                }else{
                    matched=false;
                    layui.$('.{$name}_icon_layer_html ul li').each(function(){
                        if (layui.$(this).attr('rel').indexOf(searchword) != -1 || layui.$(this).attr('alt').indexOf(searchword) != -1) {
                            matched=true;
                        }
                    });
                    if (!matched) {
                        layui.layer.msg('无相关图标');
                        layui.$('.{$name}_icon_layer_html ul li').show();
                        return;
                    }
                    layui.$('.{$name}_icon_layer_html ul li').hide();
                    layui.$('.{$name}_icon_layer_html ul li').each(function(){
                        if (layui.$(this).attr('rel').indexOf(searchword) != -1 || layui.$(this).attr('alt').indexOf(searchword) != -1) {
                            layui.$(this).show();
                        }
                    });
                }
            });
            layui.$('.{$name}_icon_layer_html li').click(function(){
                layui.$('#{$name}_icon_table ul li').css('border','none');
                layui.$('#{$name}_icon_table ul li i.'+layui.$(this).attr('rel')).parent().css('border','#1E9FFF solid 1px');
                layui.$('#{$name}_icon_btn').html('<i class="layui-icon '+layui.$(this).attr('rel')+'"></i>');
                layui.$('#{$name}_icon').val(layui.$(this).attr('rel'));
                layui.layer.close({$name}_layer);
            });
        });
    });
</script>