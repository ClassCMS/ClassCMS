<?php if(!defined('ClassCms')) {exit();}?>
<div class="layui-container footer">
	© {date(Y)} <a href="http://classcms.com" target="_blank">ClassCMS</a> All rights reserved. {$.0.tongji}
</div>
{layui:js()}
<script>
layui.use(['jquery'],function(){
    layui.$('.header .menu').click(function(){
        layui.$('.nav').toggle();
    });
});
</script>
<!--[if lt IE 9]>
<script src="https://cdn.staticfile.org/html5shiv/r29/html5.min.js"></script>
<script src="https://cdn.staticfile.org/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->