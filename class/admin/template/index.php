<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>{this:head($title)}</head>
<body class="layui-layout-body">
  <div id="ClassCMS" class="layadmin-tabspage-none">
    <div class="layui-layout layui-layout-admin">
      <div class="layui-header">
        <ul class="layui-nav layui-layout-left" id="LAY-system-ico-menu" lay-filter="layadmin-ico-menu">
          {this:icoNav()}
        </ul>
        <ul class="layui-nav layui-layout-right" id="LAY-system-user-menu" lay-filter="layadmin-layout-right" style="margin-right:20px">
          {this:userNav:()}
        </ul>
      </div>
      
      <div class="layui-side layui-side-menu">
        <div class="layui-side-scroll">
          <div class="layui-logo">
            <i class="layui-icon layui-icon-home"></i> <span>{this:navTitle()}</span>
          </div>
          <ul class="layui-nav layui-nav-tree" lay-shrink="" id="LAY-system-side-menu" lay-filter="layadmin-system-side-menu">
            {this:leftMenu()}
          </ul>
        </div>
      </div>

      <div class="layadmin-pagetabs LAY_app_tabs_noleftright" style="display:none" id="LAY_app_tabs">
        <div class="layui-icon layadmin-tabs-control layui-icon-prev" layadmin-event="leftPage" id="leftPage"></div>
        <div class="layui-icon layadmin-tabs-control layui-icon-next" layadmin-event="rightPage" id="rightPage"></div>
        <div class="layui-icon layadmin-tabs-control layui-icon-down">
          <ul class="layui-nav layadmin-tabs-select" lay-filter="layadmin-pagetabs-nav">
            <li class="layui-nav-item" lay-unselect>
              <a href="javascript:;"></a>
              <dl class="layui-nav-child layui-anim-fadein">
                <dd layadmin-event="closeThisTabs"><a href="javascript:;">关闭当前标签页</a></dd>
                <dd layadmin-event="closeOtherTabs"><a href="javascript:;">关闭其它标签页</a></dd>
                <dd layadmin-event="closeAllTabs"><a href="javascript:;">关闭全部标签页</a></dd>
              </dl>
            </li>
          </ul>
        </div>


        <div class="layui-tab" lay-unauto lay-allowClose="true" lay-filter="layadmin-layout-tabs">
          <ul class="layui-tab-title" id="LAY_app_tabsheader">
            <li lay-id="{this:defaultPage())}" lay-attr="{this:defaultPage())}" class="layui-this"><i class="layui-icon layui-icon-home"></i></li>
          </ul>
        </div>


      </div>
      
      
      <div class="layui-body" id="LAY_app_body">
        <div class="layadmin-tabsbody-item layui-show">
          <iframe name="admin_right_page" src="{this:defaultPage())}" frameborder="0" class="layadmin-iframe"></iframe>
        </div>
      </div>
      
      <div class="layadmin-body-shade" layadmin-event="shade"></div>
    </div>
  </div>
{this:body:~()}
</body>
</html>