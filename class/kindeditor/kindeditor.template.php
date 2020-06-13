<?php if(!defined('ClassCms')) {exit();}?>
{if !isset($GLOBALS.kindeditor)}
<script charset="utf-8" src="{template}kindeditor.js"></script>
{$GLOBALS.kindeditor=1}
{/if}
<textarea style="{$style}" name="{$name}"  id="{$name}_kindeditor">{$value}</textarea>
<script type="text/javascript">
    var {$name}_kindeditor;
    KindEditor.ready(function(K) {
        {$name}_kindeditor = K.create('#{$name}_kindeditor', {
            {if isset($ajax_url) && $ajax_url}
            uploadJson : '{$ajax_url}',
            {else}
                allowImageUpload:false,
                allowFlashUpload:false,
                allowMediaUpload:false,
                allowFileUpload:false,
            {/if}
            filterMode : false,
            {if isset($disabled) && $disabled}readonlyMode : true,{/if}
            afterChange : function() {
                this.sync();
            },
            {if $simple}
            items : ['source','fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline','removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist','insertunorderedlist', '|', 'image', 'link'],
            {/if}
        });
        {kindeditor:js:~()}
    });
</script>