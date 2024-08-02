<?php if(!defined('ClassCms')) {exit();}?>
<input type="hidden" name="{$name}" id="databaseunlimit_{$name}" value="{$value}">
<div id="databaseunlimit_{$name}_select">{$selecthtml}<i id="databaseunlimit_{$name}_load" style="display:none" class="layui-icon layui-icon-loading-1 layui-icon layui-anim layui-anim-rotate layui-anim-loop" ></i></div>

<script>
layui.use(['form','jquery'], function(){
    {if isset($disabled) && $disabled}
        layui.$("select[lay-filter=databaseunlimit_{$name}]").attr('disabled','1');
        layui.form.render('select');
    {else}
      layui.form.on('select(databaseunlimit_{$name})', function(data){
          if (!data.value)
          {
              data.value=layui.$(data.elem).parent('div.layui-inline').prev().find('select').val();
          }
          layui.$("#databaseunlimit_{$name}").val(data.value);
          layui.admin.req({type:'post',url:"{$ajax_url}",data:{value:data.value},async:true,beforeSend:function(){
            layui.$('#databaseunlimit_{$name}_load').show();
          },done: function(res){
            if (res.error==0)
            {
                layui.$('#databaseunlimit_{$name}_select').html(res.html+'<i id="databaseunlimit_{$name}_load" style="display:none" class="layui-icon layui-icon-loading-1 layui-icon layui-anim layui-anim-rotate layui-anim-loop" ></i>');
                layui.$('#databaseunlimit_{$name}_load').hide();
                layui.form.render('select');
            }
          }});
        });
    {/if}
});
</script>