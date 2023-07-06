<?php if(!defined('ClassCms')) {exit();}?>
<div id="{$name}_itemlist" class="tags_itemlist">
<?php
foreach($values as $thisvalues) {
    ?>
    <div class="tagsitem{if !$showstyle} tagsiteminline{/if}">
        {if $sortable}<i class="layui-icon layui-icon-find-fill sortable-color"></i>{/if}
        <?php
            for($i=0; $i<$column; $i++) {
                if(!isset($thisvalues[$i])) {
                    $thisvalues[$i]='';
                }
                echo('<input type="text" name="'.$name.'[]" value="'.$thisvalues[$i].'" class="layui-input" '.$width[$i].' placeholder="'.$columntips[$i].'"> ');
            }
        ?>
        {if !$disabled}<i class="layui-icon layui-icon-close delbutton"></i>{/if}
    </div>
    <?php
}
?>

</div>
<button id="{$name}_add" type="button" class="layui-btn layui-btn-sm cms-btn" style="margin-left:20px"><i class="layui-icon layui-icon-addition"></i></button>
<div id="{$name}_newitem" style="display:none">
    <div class="tagsitem{if !$showstyle} tagsiteminline{/if}">
        {if $sortable}<i class="layui-icon layui-icon-find-fill sortable-color"></i>{/if}
        <?php
            for($i=0; $i<$column; $i++) {
                ?>
                    <input type="text" classcms_name="{$name}[]" value=""  placeholder="<?php echo($columntips[$i]);?>" class="layui-input" <?php echo($width[$i]);?>> 
                <?php
            }
        ?>
        {if !$disabled}<i class="layui-icon layui-icon-close delbutton"></i>{/if}
    </div>
</div>

{if !$disabled}
<script>
    layui.use(['index','sortable'],function(){
        layui.$('#{$name}_add').click(function(){
            if (layui.$('#{$name}_itemlist div').length>={$max})
            {
                layui.layer.msg('最多只能增加 {$max} 项');
                return false;
            }
            html=layui.$('#{$name}_newitem').html().replace(/classcms_name/g,"name");
            layui.$('#{$name}_itemlist').append(html);
            layui.$('#{$name}_itemlist .tagsitem:last input:first').focus();
        });
        layui.$('#{$name}_itemlist').on('click','.delbutton',function(){
            layui.$(this).parent().remove();
        });
        {if $sortable}
        new Sortable({$name}_itemlist, {
            handle: '.layui-icon',
            onSort: function (evt) {
            }
        });
        {/if}
    });
</script>
{/if}
