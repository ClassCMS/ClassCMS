<?php if(!defined('ClassCms')) {exit();}?>
<div id="{$name}_article" class="article_choose_input">
    <ul id="{$name}_ul">
    {loop $articles as $article}
    <li data-id="{$article.1}"  data-cid="{$article.0}"></li>
    {/loop}
    </ul>
    {if !$module && !$channel}
      <div id="{$name}_channel_select">
        <div class="article_choose_input_channel_select">
          <div class="layui-inline">{cms:input:form($choseinput)}</div>
          {if $chosehtml}<div class="layui-inline {$name}_channel_select">{$chosehtml}</div>{/if}
        </div>
      </div>
    {/if}
    <table class="layui-table" style="width:100%;max-width:500px" lay-size="sm">
    <thead class="action">
        <tr>
            <td  colspan=2>
                <span class="search"><a>搜索</a></span>
                <span class="refresh"><a>刷新</a></span>
                <span class="choose"><a>选择</a></span>
                <span class="count"><a>{if $multiple && count($articles)}已选:{count($articles)}{/if}{if !$multiple && count($articles)}已选{/if}</a></span>
                <span class="next"><a>下一页&gt;</a></span>
                <span class="page">/</span>
                <span class="prev"> <a>&lt;上一页</a></span>
            </td>
        </tr>
    </thead>
    <tbody class="article_list" id="{$name}_tbody">
    {loop $articles as $article}
    <tr data-id="{$article.1}" data-cid="{$article.0}">
    <td>
        {if $multiple}<i class="layui-icon layui-icon-find-fill sortable-color"></i> {/if}
        {$article.2} 
        <i class="layui-icon close">&#x1006;</i>
    </td>
    <td>
    {if $article.0}<a href="?do=admin:article:edit&cid={$article.0}&id={$article.1}" target="_blank">查看</a>{/if}
    </td>
    </tr>
    {/loop}
    </tbody>
    </table>
</div>
<input id="{$name}_article_input" type="hidden" name="{$name}" value="{$value}">
<input id="{$name}_choose_cid" type="hidden" value="{if $module}{$module}{elseif $channel}{$channel}{elseif $defaultchannel}{$defaultchannel}{/if}">
<input id="{$name}_choose_page" type="hidden" value="1">
<input id="{$name}_choose_pagecount" type="hidden" value="0">
<input id="{$name}_choose_keyword" type="hidden" value="">
<script>
    layui.use(['index','sortable'],function(){
       var $ = layui.$;
       $('#{$name}_article').on('click','i.close',function(){
           dataid=$(this).parents('tr').attr('data-id');
           datacid=$(this).parents('tr').attr('data-cid');
           {$name}_article_del(dataid,datacid);
           $(this).parents('tr').remove();
           if (!$('#{$name}_article ul li').length){{$name}_article_load();}
       });
       {if count($articles)==0}
             {if $module || $channel}
               $('#{$name}_article span').show();
               $('#{$name}_article span.choose').hide();
               {$name}_article_load();
             {/if}
       {else}
         $('#{$name}_article span').hide();
         $('#{$name}_article span.choose').show();
       {/if}
        {if $multiple}
        new Sortable({$name}_tbody, {
            handle: '.layui-icon',
            onSort: function (evt) {
                $('#{$name}_ul').html('');
                $('#{$name}_tbody tr').each(function(){
                    $('#{$name}_ul').append('<li data-id="'+$(this).attr('data-id')+'"  data-cid="'+$(this).attr('data-cid')+'"></li>');
                });
                {$name}_input_reload();
            }
        });
        {/if}
        $('#{$name}_article thead').on('click','span.count',function(){
            layui.admin.req({type:'post',url:"{$ajax_url}",data:{ajaxdo:'showvalue',name:'{$name}',value:$('#{$name}_article_input').val()},async:true,beforeSend:function(){
                $('#{$name}_article span.page').html('<i class="layui-icon layui-icon-loading-1 layui-icon layui-anim layui-anim-rotate layui-anim-loop"></i>');
            },done: function(res){
                if (res.error==0)
                {
                    $('#{$name}_article tbody.article_list').html(res.html);
                    $('#{$name}_article span').hide();
                    $('#{$name}_article span.choose').show();
                }
            }});
        });
        $('#{$name}_article thead').on('click','span.choose',function(){
            if ($('#{$name}_choose_cid').val().length==0){layui.admin.popup({content:'请先选择栏目'});return;}
            {$name}_article_load();
            $('#{$name}_article span').show();
            $('#{$name}_article span.choose').hide();
        });
        $('#{$name}_article thead').on('click','span.search',function(){
            var {$name}_layerprompt=layer.prompt({title: '输入搜索词',value:$('#{$name}_choose_keyword').val(),yes:function(){
                var keyword = $('.layui-layer-prompt .layui-layer-input').val();
                if (keyword) {
                    $('#{$name}_article span.search').html('<a>搜索:'+keyword+'</a>');
                } else {
                    $('#{$name}_article span.search').html('<a>搜索</a>');
                }
                $('#{$name}_choose_keyword').val(keyword);
                $('#{$name}_choose_page').val(1);
                {$name}_article_load();
                layer.close({$name}_layerprompt);
            }});
        });
        $('#{$name}_article thead').on('click','span.refresh',function(){
            {$name}_article_load();
        });
        $('#{$name}_article thead').on('click','span.prev',function(){
            if (parseInt($('#{$name}_choose_page').val())>1)
            {
                $('#{$name}_choose_page').val(parseInt($('#{$name}_choose_page').val())-1);
                {$name}_article_load();
            }
        });
        $('#{$name}_article thead').on('click','span.next',function(){
            if (parseInt($('#{$name}_choose_page').val())<parseInt($('#{$name}_choose_pagecount').val()))
            {
                $('#{$name}_choose_page').val(parseInt($('#{$name}_choose_page').val())+1);
                {$name}_article_load();
            }
            
        });
      function {$name}_input_reload(){
            $('#{$name}_article_input').val('');
            $('#{$name}_article li').each(function(){
                $('#{$name}_article_input').val($('#{$name}_article_input').val()+$(this).attr('data-cid')+':'+$(this).attr('data-id')+';');
            });
            return true;
      }
      function {$name}_article_add(only,id,cid,title,url){
        if (only){$('#{$name}_article li').remove();}
        $('#{$name}_article ul').append('<li data-id="'+id+'"  data-cid="'+cid+'"></li>');
        {if $multiple}
          $('#{$name}_article span.count a').text('已选:'+$('#{$name}_article ul li').length);
        {else}
          $('#{$name}_article span.count a').text('已选');
        {/if}
        {$name}_input_reload();
      }
      function {$name}_article_del(id,cid){
        $('#{$name}_article li').each(function(){
            if ($(this).attr('data-id')==id && $(this).attr('data-cid')==cid)
            {
                $(this).remove();
                if (!$('#{$name}_article ul li').length)
                {
                    $('#{$name}_article span.count a').text('未选');
                }else{
                    {if $multiple}
                      $('#{$name}_article span.count a').text('已选:'+$('#{$name}_article ul li').length);
                    {else}
                      $('#{$name}_article span.count a').text('已选');
                    {/if}
                }
                return {$name}_input_reload();
            }
        });
      }
      function {$name}_article_load(){
        if ($('#{$name}_choose_cid').val().length==0){return ;}
        layui.admin.req({type:'post',url:"{$ajax_url}",data:{ajaxdo:'articlelist',name:'{$name}',value:$('#{$name}_article_input').val(),cid:$('#{$name}_choose_cid').val(),page:$('#{$name}_choose_page').val(),keyword:$('#{$name}_choose_keyword').val()},async:true,beforeSend:function(){
            $('#{$name}_article span.page').html('<i class="layui-icon layui-icon-loading-1 layui-icon layui-anim layui-anim-rotate layui-anim-loop"></i>');
        },done: function(res){
            if (res.error==0)
            {
                $('#{$name}_article tbody.article_list').html(res.html);
                layui.form.render();
                $('#{$name}_article span').show();
                $('#{$name}_article span.choose').hide();
                $('#{$name}_choose_pagecount').val(res.pagecount);
                if (res.pagecount==0)
                {
                  $('#{$name}_article span.page').html('0/0');
                  $('#{$name}_article span.search').hide();
                }else{
                  $('#{$name}_article span.page').html($('#{$name}_choose_page').val()+'/'+res.pagecount);
                  $('#{$name}_article span.search').show();
                }
                layui.form.on('radio({$name}_article)', function(data){
                    {$name}_article_add(1,$(data.elem).attr('data-id'),$(data.elem).attr('data-cid'),$(data.elem).attr('title'));
                });
                layui.form.on('checkbox({$name}_article)', function(data){
                     if (data.elem.checked)
                     {
                        {$name}_article_add(0,$(data.elem).attr('data-id'),$(data.elem).attr('data-cid'),$(data.elem).attr('title'));
                     }else{
                        {$name}_article_del($(data.elem).attr('data-id'),$(data.elem).attr('data-cid'));
                     }
                });
            }
        }});
      }
      {if !$module && !$channel && $class}
        layui.form.on('select({$name}_channelselect)', function(data){
            $('#{$name}_choose_page').val(1);
            $('#{$name}_choose_pagecount').val(0);
            $('#{$name}_choose_keyword').val('');
            $('#{$name}_article span.search').html('<a>搜索</a>');
            $('#{$name}_choose_keyword').val('');
            if (data.value)
            {
              $('#{$name}_choose_cid').val(data.value);
              $('#{$name}_article span').show();
              $('#{$name}_article span.choose').hide();
              layer.close(window.{$name}_layerchannel);
              {$name}_article_load();
            }else{
              $('#{$name}_article tbody.article_list').html('');
              $('#{$name}_article span').hide();
              $('#{$name}_article span.choose').show();
            }
            
        });
      {/if}
      {if !$module && !$channel && !$class}
        layui.form.on('select({$name}_channelchose)', function(data){
            $('#{$name}_choose_page').val(1);
            $('#{$name}_choose_pagecount').val(0);
            $('#{$name}_choose_keyword').val('');
            $('#{$name}_article span.search').html('<a>搜索</a>');
            $('#{$name}_choose_keyword').val('');
            if (data.value)
            {
                $('#{$name}_choose_cid').val(data.value);
                $('#{$name}_article span').show();
                $('#{$name}_article span.choose').hide();
                layer.close(window.{$name}_layerchannel);
                {$name}_article_load();
            }else{
                $('#{$name}_article span').hide();
                $('#{$name}_article span.choose').show();
            }
        });
        layui.form.on('select({$name}_classselect)', function(data){
          if (data.value)
          {
            layui.admin.req({type:'post',url:"{$ajax_url}",data:{classhash: data.value,ajaxdo:'choosechannel',name:'{$name}'},async:true,beforeSend:function(){
                $('.{$name}_channel_select').html('<i class="layui-icon layui-icon-loading layui-icon layui-anim layui-anim-rotate layui-anim-loop"></i>');
                $('#{$name}_choose_cid').val('');
            },done: function(res){
                if (res.error==0)
                {
                    $('.{$name}_channel_select').html(res.html);
                    layui.form.render('select');
                }
            }});
          }else{
            $('.{$name}_channel_select').html('');
            $('#{$name}_choose_cid').val('');
            $('#{$name}_article span').hide();
            $('#{$name}_article span.choose').show();
          }
        });
      {/if}
    });
</script>