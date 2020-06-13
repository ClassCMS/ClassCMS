<?php if(!defined('ClassCms')) {exit();}?>
<div style="border: 1px dashed #e2e2e2;display:inline-block;">
<div class="layui-upload-drag" id="{$name}_fileupload_drag" style="border: none;">
  {if !$disabled}
  <i class="layui-icon" id="{$name}_layui-icon"{if $value} style="display:none"{/if}></i>
  <p id="{$name}_fileupload_text" style="text-align:center">点击上传{if $multiple}多个文件{/if}，或将文件拖拽到此处</p>
  {/if}
</div>
  <div {if !$value} class="layui-hide"{/if} id="{$name}_fileupload_view"  style="border-top: 1px solid #e2e2e2;padding:10px">
    <table class="layui-table" lay-skin="line">
        <tbody id="{$name}_fileupload_tbody">
        {loop $files as $file}
            <tr>
            {if $multiple}<td><i class="layui-icon layui-icon-find-fill sortable-color"></i></td>{/if}
            <td><a href="{$file.0}" target="_blank" class="fileupload_url">{$file.1}</a></td>
            <td align="right"><i class="layui-icon layui-icon-close cmscolor fileupload_del" style="cursor:pointer;font-size:20px"></i></td>
            </tr>
        {/loop}
        </tbody>
    </table>
  </div>
</div>

<input type="hidden" name="{$name}" id="{$name}_fileupload" value="{$value}">

{if !$disabled}
<script>
layui.use(['index','sortable','upload'], function(){
  var $ = layui.jquery
  ,upload = layui.upload;

    $('#{$name}_fileupload_view').on('click','.fileupload_del',function(){
        $(this).parents('tr').remove();
        {$name}_fileupload_reload();
    });

  function {$name}_fileupload_reload(){
    value='';
    $('#{$name}_fileupload_view table .fileupload_url').each(function(){
        value=value+$(this).attr('href')+';';
    });
    if (value=='')
    {
        $('#{$name}_fileupload_view').addClass('layui-hide');
        $('#{$name}_layui-icon').show();
        $('#{$name}_fileupload_text').html('点击上传{if $multiple}多个文件{/if}，或将文件拖拽到此处');
    }else{
        $('#{$name}_fileupload_view').removeClass('layui-hide');
        $('#{$name}_layui-icon').hide();
    }
    $('#{$name}_fileupload').val(value);
  }
  {if $multiple}
    new Sortable({$name}_fileupload_tbody, {
        handle: 'tr',
        onSort: function (evt) {
            {$name}_fileupload_reload();
        }
    });
  {/if}
  var {$name}_fileupload = upload.render({
    elem: '#{$name}_fileupload_drag'
    ,url: '{$ajax_url}'
    ,field:'{$name}_layupload'
    ,accept:'file'
    {if $multiple}
    ,allDone: function(obj){
        if (obj.aborted)
        {
            $('#{$name}_fileupload_text').html('上传成功:'+obj.successful+';失败:'+obj.aborted);
        }else{
            $('#{$name}_fileupload_text').html('上传完成{if $multiple},点击继续上传{/if}');
        }
    }
    {/if}
    {if $multiple},multiple: true{/if}
    ,before: function(obj){
      $('#{$name}_fileupload_text').html('上传中<i class="layui-icon layui-icon-loading-1 layui-icon layui-anim layui-anim-rotate layui-anim-loop"></i>');
    }
    ,done: function(res){
      if(res.error == 0){
          {if !$multiple}
            $('#{$name}_fileupload_tbody tr').remove();
            $('#{$name}_fileupload_text').html('上传成功');
          {/if}
          $('#{$name}_fileupload_tbody').append('<tr>{if $multiple}<td><i class="layui-icon layui-icon-find-fill sortable-color"></i></td>{/if}<td><a href="'+res.url+'" target="_blank" class="fileupload_url">'+res.filename+'</a></td><td align="right"><i class="layui-icon layui-icon-close cmscolor fileupload_del" style="cursor:pointer;font-size:20px"></i></td></tr>');
          {$name}_fileupload_reload();
      }else{
          {if !$multiple}
            $('#{$name}_fileupload_text').html(res.message);
          {else}
            layui.layer.alert(res.message);
          {/if}
      }
    }
  });
});
</script>
{/if}