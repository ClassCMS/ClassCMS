<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>{this:head(出错了)}</head>
<body>
<div class="layui-fluid">
  <div class="layadmin-tips">
    <i class="layui-icon" face>{$ico}</i>
    <div class="layui-text" style="font-size: 20px;width:100%">
      {$msg}
    </div>
  </div>
</div>
{this:body:~()}
</body>
</html>