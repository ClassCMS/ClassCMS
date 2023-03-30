<?php if(!defined('ClassCms')) {exit();}?>
<div style="border: 1px dashed #e2e2e2;display:inline-block;">
<div class="layui-upload-drag" id="{$name}_imgupload_drag" style="border:none{if $value && !$multiple};display:none{/if}">
  {if !$disabled}
    <i class="layui-icon" id="{$name}_layui-icon"{if $value} style="display:none"{/if}></i>
    <p id="{$name}_imgupload_text" style="text-align:center">点击上传{if $multiple}多张图片{/if}，或将图片拖拽到此处</p>
  {/if}
</div>
  <div {if !$value} class="layui-hide"{/if} id="{$name}_imgupload_view"  style="border-top: 1px solid #e2e2e2;background: #f2f2f2;">
    {loop $pics as $pic}
        <p style="display:inline-block;position:relative;padding:5px">
            <img src="{$pic}" style="max-width:100%;max-height: {$height}px;{if $multiple}cursor:move;{/if}">
            <i class="layui-icon layui-icon-close" style="position:absolute;top:3%;right:3%;opacity: 0.5;background-color:#000000;color:#fff;cursor:pointer;font-size:20px;"></i>
        </p>
    {/loop}
  </div>
</div>

<input type="hidden" name="{$name}" id="{$name}_imgupload" value="{$value}">

{if !$disabled}
<script>
layui.use(['index','sortable','upload'], function(){
  var $ = layui.jquery,upload = layui.upload;
  $('#{$name}_imgupload_view').on('click','i',function(){
      $(this).parents('p').remove();
      {$name}_imgupload_reload();
  });
  function {$name}_imgupload_reload(){
    value='';
    $('#{$name}_imgupload_view img').each(function(){
      if ($(this).attr('src').slice(0,5)!='data:') {
        value=value+$(this).attr('src')+';';
      }
    });
    if ($('#{$name}_imgupload_view img').length)
    {
        $('#{$name}_imgupload_view').removeClass('layui-hide');
        $('#{$name}_layui-icon').hide();
    }else{
        $('#{$name}_imgupload_view').addClass('layui-hide');
        $('#{$name}_layui-icon').show();
        $('#{$name}_imgupload_text').html('点击上传{if $multiple}多张图片{/if}，或将图片拖拽到此处');
        $('#{$name}_imgupload_drag').show();
    }
    $('#{$name}_imgupload').val(value);
  }
  {if $multiple}
    new Sortable({$name}_imgupload_view, {
        handle: 'img',
        onSort: function (evt) {
            {$name}_imgupload_reload();
        }
    });
  {/if}
  piclist=[];
  var {$name}_imgupload = upload.render({
    elem: '#{$name}_imgupload_drag'
    ,url: '{$ajax_url}'
    ,field:'{$name}_layupload'
    ,accept:'images'
    {if $multiple}
    ,allDone: function(obj){
        if (obj.aborted)
        {
            $('#{$name}_imgupload_text').html('上传成功:'+obj.successful+';失败:'+obj.aborted);
        }else{
            $('#{$name}_imgupload_text').html('上传完成{if $multiple},点击继续上传{/if}');
        }
    }
    {/if}
    {if $multiple},multiple: true{/if}
    ,before: function(obj){
      if (navigator.userAgent.indexOf("Safari") > -1 && navigator.userAgent.indexOf("Chrome") == -1) {}else{
        $('#{$name}_layui-icon').hide();
        $('#{$name}_imgupload_text').html('上传中 <i class="layui-icon layui-icon-loading layui-anim layui-anim-rotate layui-anim-loop" style="font-size:16px"></i>');
      }
      $('#{$name}_imgupload_view').removeClass('layui-hide');
      {if !$multiple}$('#{$name}_imgupload_view p').remove();{/if}
      obj.preview(function(index, file, result){
          if (piclist[index]===false) {
            $('#{$name}_imgupload_view').append('<p style="display:inline-block;position:relative;padding:5px;color:#fff"><img rel="'+index+'" src="'+ result +'" style="max-width:100%;max-height: {$height}px;{if $multiple}cursor:move;{/if}"><span rel="loading" style="position:absolute;bottom:0%;left:0%;right:0%;opacity: 0.7;background-color:red;margin:5px;white-space:nowrap;color:#fff;font-size:14px;"> 上传失败</span><i class="layui-icon layui-icon-close" style="position:absolute;top:3%;right:3%;opacity: 0.5;background-color:#000000;cursor:pointer;font-size:20px;"></i></p>');
          }else if(piclist[index]){
            $('#{$name}_imgupload_view').append('<p style="display:inline-block;position:relative;padding:5px;color:#fff"><img rel="'+index+'" src="'+ piclist[index] +'" style="max-width:100%;max-height: {$height}px;{if $multiple}cursor:move;{/if}"><i class="layui-icon layui-icon-close" style="position:absolute;top:3%;right:3%;opacity: 0.5;background-color:#000000;cursor:pointer;font-size:20px;"></i></p>');
          }else{
            $('#{$name}_imgupload_view').append('<p style="display:inline-block;position:relative;padding:5px;color:#fff"><img rel="'+index+'" src="'+ result +'" style="max-width:100%;max-height: {$height}px;{if $multiple}cursor:move;{/if}"><span rel="loading" style="position:absolute;bottom:0%;left:0%;right:0%;opacity: 0.7;background-color:#000000;margin:5px;white-space:nowrap;color:#fff;font-size:14px;"> <i class="layui-icon layui-icon-loading layui-anim layui-anim-rotate layui-anim-loop"></i></span><i class="layui-icon layui-icon-close" style="position:absolute;top:3%;right:3%;opacity: 0.5;background-color:#000000;cursor:pointer;font-size:20px;"></i></p>');
          }
      });
    }
    ,progress: function(n, elem, res, index){
      $('#{$name}_imgupload_view img[rel='+index+']').parent().find('span[rel=loading]').html('&nbsp;'+n + '%');
    }
    ,error: function(index){
      $('#{$name}_imgupload_view img[rel='+index+']').parent().find('span[rel=loading]').html(' 上传失败').css('background','red');
    }
    ,done: function(res, index){
      if(res.error == 0){
          {if !$multiple}
            $('#{$name}_imgupload_text').html('上传成功');
          {/if}
          piclist[index]=res.url;
          $('#{$name}_imgupload_view img[rel='+index+']').attr('src',res.url);
          $('#{$name}_imgupload_view img[rel='+index+']').parent().find('span[rel=loading]').remove();
      }else{
          {if !$multiple}
            $('#{$name}_imgupload_text').html(res.message);
          {/if}
          piclist[index]=false;
          $('#{$name}_imgupload_view img[rel='+index+']').parent().find('span[rel=loading]').html(res.message).css('background','red');
      }
      {$name}_imgupload_reload();
    }
  });
});
</script>
{/if}