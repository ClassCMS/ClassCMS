<?php if(!defined('ClassCms')) {exit();}?>
<div id="{$name}_databasetreeselects"></div>
<script>layui.use(['index','xmselect'],function(){
    xmSelect.render({
      el: '#{$name}_databasetreeselects',name: '{$name}',
      autoRow: true,
      {if $radio}radio: true,{/if}
      size:'small',
      theme: {
        color: '#1E9FFF',
      },
      {if isset($disabled) && $disabled}disabled : true,{/if}
      tree: {
        show: true,
        showFolderIcon: true,
        showLine: true,
        indent: 20,
        expandedKeys: {if $expanded}true{else}false{/if},
        strict:{if $strict}true{else}false{/if},
      },
      filterable: {if $search}true{else}false{/if},
      height: 'auto',
      data: {json_encode($treearticles)}
  })

});
</script>