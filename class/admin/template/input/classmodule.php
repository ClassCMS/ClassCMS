<?php if(!defined('ClassCms')) {exit();}?>
<div class="layui-inline">{cms:input:form($classinput)}</div>
<div class="layui-inline" id="{$name}_module_select" lay-filter="{$name}_module_select">{$chosemodule}</div>
<input id="{$name}_input" type="hidden" name="{$name}" value="{$value}">
<script>
    layui.use(['form'],function(){
        var $ = layui.$;
        layui.form.on('select({$name}_modulechose)', function(data){
            $('#{$name}_input').val($('select[name={$name}_classselect]').val()+':'+data.value);
        });
        layui.form.on('select({$name}_classselect)', function(data){
          if (data.value)
          {
            layui.admin.req({type:'post',url:"{$ajax_url}",data:{classhash: data.value},async:true,beforeSend:function(){
                $('#{$name}_module_select').html('<i class="layui-icon layui-icon-loading layui-icon layui-anim layui-anim-rotate layui-anim-loop"></i>');
                $('#{$name}_input').val('');
            },done: function(res){
                if (res.error==0)
                {
                    $('#{$name}_module_select').html(res.html);
                    layui.form.render('select');
                }
            }});
          }else{
            $('#{$name}_module_select').html('');
            $('#{$name}_input').val('');
          }
        });
    });
</script>
