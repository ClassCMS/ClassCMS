<?php if(!defined('ClassCms')) {exit();}?>
<input type="hidden" name="{$name}" id="databaseselects_{$name}" value="{$value}">
<input type="hidden" name="{$name}_level" id="databaseselects_{$name}_level" value="{$level}">
<div id="databaseselects_{$name}_select">{$selecthtml}<i id="databaseselects_{$name}_load" style="display:none" class="layui-icon layui-icon-loading-1 layui-icon layui-anim layui-anim-rotate layui-anim-loop" ></i></div>

<script>
layui.use(['form','jquery'], function(){
    {if isset($disabled) && $disabled}
        layui.$("select[lay-filter=databaseselects_{$name}]").attr('disabled','1');
        layui.form.render('select');
    {else}
      layui.form.on('select(databaseselects_{$name})', function(data){
          layui.$("#databaseselects_{$name}").val(data.value);
          layui.$("#databaseselects_{$name}_level").val(layui.$(data.elem).attr('rel'));
          data.level=layui.$(data.elem).attr('rel');
          if (!data.value)
          {
            data.level--;
            if (data.level<0)
            {
              data.level=0;
            }else{
              data.value=layui.$(data.elem).parent('div.layui-inline').prev().find('select[rel='+data.level+']').val();
            }
            layui.$("#databaseselects_{$name}_level").val(data.level);
          }
          layui.admin.req({type:'post',url:"{$ajax_url}",data:{value:data.value,level:data.level},async:true,beforeSend:function(){
            layui.$('#databaseselects_{$name}_load').show();
          },done: function(res){
            if (res.error==0)
            {
                layui.$('#databaseselects_{$name}_select').html(res.html+'<i id="databaseselects_{$name}_load" style="display:none" class="layui-icon layui-icon-loading-1 layui-icon layui-anim layui-anim-rotate layui-anim-loop" ></i>');
                layui.$('#databaseselects_{$name}_load').hide();
                layui.form.render('select');
            }
          }});
        });
    {/if}
});
</script>