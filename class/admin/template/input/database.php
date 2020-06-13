<?php if(!defined('ClassCms')) {exit();}?>
<div id="{$name}_article" class="database_choose_input">
    <ul id="{$name}_ul">
    {loop $articles as $article}
    <li data-id="{$article.id_va1ue_classcms}"></li>
    {/loop}
    </ul>
    <table class="layui-table" style="width:100%;max-width:{if count($titlecolumns)>5}1200{elseif count($titlecolumns)>2}1000{else}800{/if}px" lay-size="sm">
    <thead class="action">
        <tr>
            <td  colspan=<?php echo(count($titlecolumns)+1); ?>>
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
    <tr data-id="{$article.id_va1ue_classcms}">
        {loop $titlecolumns as $key=>$thistitle}
        <td>
            {if $multiple && !$key}<i class="layui-icon layui-icon-find-fill sortable-color"></i> {/if}
            <?php echo($article[$thistitle]); ?>
        </td>
        {/loop}
        <td style="width:20px;text-align:right">
            <i class="layui-icon close">&#x1006;</i>
        </td>
    </tr>
    {/loop}
    </tbody>
    </table>
</div>
<input id="{$name}_article_input" type="hidden" name="{$name}" value="{$value}">
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
         $('#{$name}_article span').show();
         $('#{$name}_article span.choose').hide();
         {$name}_article_load();
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
                    $('#{$name}_ul').append('<li data-id="'+$(this).attr('data-id')+'"></li>');
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
                $('#{$name}_article_input').val($('#{$name}_article_input').val()+$(this).attr('data-id')+';');
            });
            return true;
      }
      function {$name}_article_add(only,id){
        if (only){$('#{$name}_article li').remove();}
        $('#{$name}_article ul').append('<li data-id="'+id+'"></li>');
        {if $multiple}
          $('#{$name}_article span.count a').text('已选:'+$('#{$name}_article ul li').length);
        {else}
          $('#{$name}_article span.count a').text('已选');
        {/if}
        {$name}_input_reload();
      }
      function {$name}_article_del(id){
        $('#{$name}_article li').each(function(){
            if ($(this).attr('data-id')==id)
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
        layui.admin.req({type:'post',url:"{$ajax_url}",data:{ajaxdo:'articlelist',name:'{$name}',value:$('#{$name}_article_input').val(),page:$('#{$name}_choose_page').val(),keyword:$('#{$name}_choose_keyword').val()},async:true,beforeSend:function(){
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
                    {$name}_article_add(1,$(data.elem).attr('data-id'));
                });
                layui.form.on('checkbox({$name}_article)', function(data){
                     if (data.elem.checked)
                     {
                        {$name}_article_add(0,$(data.elem).attr('data-id'));
                     }else{
                        {$name}_article_del($(data.elem).attr('data-id'));
                     }
                });
            }
        }});
      }
    });
</script>