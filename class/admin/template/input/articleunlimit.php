<?php if(!defined('ClassCms')) {exit();}?>
<input type="hidden" name="{$name}" id="articleunlimit_{$name}" value="{$value}">
<div id="articleunlimit_{$name}_select">{$selecthtml}<i id="articleunlimit_{$name}_load" style="display:none" class="layui-icon layui-icon-loading-1 layui-icon layui-anim layui-anim-rotate layui-anim-loop" ></i></div>

<script>
layui.use(['form','jquery'], function(){
    {if isset($disabled) && $disabled}
        layui.$("select[lay-filter=articleunlimit_{$name}]").attr('disabled','1');
        layui.form.render('select');
    {else}
      layui.form.on('select(articleunlimit_{$name})', function(data){
          if (!data.value)
          {
              data.value=layui.$(data.elem).parent('div.layui-inline').prev().find('select').val();
          }
          layui.$("#articleunlimit_{$name}").val(data.value);
          layui.admin.req({type:'post',url:"{$ajax_url}",data:{value:data.value{if isset($source_cid)},source_cid:'{$source_cid}'{/if}{if isset($source_id)},source_id:'{$source_id}'{/if}},async:true,beforeSend:function(){
            layui.$('#articleunlimit_{$name}_load').show();
          },done: function(res){
            if (res.error==0)
            {
                layui.$('#articleunlimit_{$name}_select').html(res.html+'<i id="articleunlimit_{$name}_load" style="display:none" class="layui-icon layui-icon-loading-1 layui-icon layui-anim layui-anim-rotate layui-anim-loop" ></i>');
                layui.$('#articleunlimit_{$name}_load').hide();
                layui.form.render('select');
            }
          }});
        });
    {/if}
});
</script>
