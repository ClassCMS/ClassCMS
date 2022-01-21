<?php if(!defined('ClassCms')) {exit();}?>
{if $checkold}
<input type="{if $auth.showpsw}text{else}password{/if}"{if $disabled} disabled{/if} id="password_{$name}_old" name="{$name}_old" value="{if $auth.showpsw}{$value}{/if}" class="layui-input" placeholder="{if isset($placeholder_old)}{$placeholder_old}{/if}">
{/if}
<input type="{if $auth.showpsw}text{else}password{/if}"{if $disabled} disabled{/if} id="password_{$name}" name="{$name}" value="{if $auth.showpsw}{$value}{/if}" class="layui-input" placeholder="{if isset($placeholder_new)}{$placeholder_new}{/if}"{if $checkold} style="display:none;margin-top:20px"{/if}>
{if !$auth.showpsw}
<input type="{if $auth.showpsw}text{else}password{/if}"{if $disabled} disabled{/if} id="password_{$name}_2" name="{$name}_2" value="{if $auth.showpsw}{$value}{/if}" class="layui-input" placeholder="{if isset($placeholder_check)}{$placeholder_check}{/if}" style="display:none;margin-top:20px">
{/if}
{if !$disabled}
<script>
    layui.use(['index','jquery','layer'],function(){
        var $=layui.$;
        {if $checkold}
            $('#password_{$name}_old').keyup(function(){
                if ($('#password_{$name}_old').val().length>0)
                {
                    $('#password_{$name}').show();
                    {if !$auth.showpsw}$('#password_{$name}_2').show();{/if}
                }else{
                    $('#password_{$name}').hide();
                    $('#password_{$name}').val('');
                    {if !$auth.showpsw}
                    $('#password_{$name}_2').hide();
                    $('#password_{$name}_2').val('');
                    {/if}
                }
            });
        {elseif !$auth.showpsw}
            $('#password_{$name}').keyup(function(){
                if ($('#password_{$name}').val().length>0)
                {
                    $('#password_{$name}_2').show();
                }else{
                    $('#password_{$name}_2').hide();
                    $('#password_{$name}_2').val('');
                }
            });
        {/if}
        {if !$auth.showpsw}
        $('#password_{$name}_2').change(function(){
            if ($('#password_{$name}').val()!=$('#password_{$name}_2').val())
            {
                layui.layer.msg('密码不一致',{time: 1000});
            }
        });
        $('#password_{$name}_2').keyup(function(){
            if ($('#password_{$name}').val()==$('#password_{$name}_2').val())
            {
                $('#password_{$name}_2').css('color','green');
            }else{
                $('#password_{$name}_2').css('color','red');
            }
        });
        {/if}
    });
</script>
{/if}