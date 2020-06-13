/**

 @Name：layuiAdmin iframe版主入口
 @Author：贤心
 @Site：http://www.layui.com/admin/
 @License：LPPL
    
 */
layui.extend({setter:"config",admin:"lib/admin",view:"lib/view"}).define(["setter","admin","form"],function(m){layui.form.verify({hash:function(a,b){if(!/^[A-Za-z]{1}[A-Za-z0-9_]{0,31}$/.test(a))return"\u683c\u5f0f\u4e3a\u5b57\u6bcd\u6216(\u5b57\u6bcd,\u6570\u5b57,_)\u7ec4\u5408"}});var d=layui.setter,h=layui.element,c=layui.admin,g=c.tabsPage,n=layui.view,q=function(a,b){var k,e=f("#LAY_app_tabsheader>li");a.replace(/(^http(s*):)|(\?[\s\S]*$)/g,"");e.each(function(b){f(this).attr("lay-id")===a&&(k=!0,g.index=b)});b=b||"\u65b0\u6807\u7b7e\u9875";d.pageTabs?k||(f(p).append(['<div class="layadmin-tabsbody-item layui-show">','<iframe src="'+a+'" frameborder="0" class="layadmin-iframe"></iframe>',"</div>"].join("")),g.index=e.length,h.tabAdd(l,{title:"<span>"+b+"</span>",id:a,attr:a})):(e=c.tabsBody(c.tabsPage.index).find(".layadmin-iframe"),0<e.length?e[0].contentWindow.location.href=a:window.open(a));h.tabChange(l,a);c.tabsBodyChange(g.index,{url:a,text:b})},p="#LAY_app_body",l="layadmin-layout-tabs",f=layui.$;f(window);'Cla'+'ss'+'C'+'MS'==d.container&&(2>c.screen()&&c.sideFlexible(),layui.config({base:d.base+"modules/"}),layui.each(d.extend,function(a,b){a={};a[b]="{/}"+d.base+"lib/extend/"+b;layui.extend(a)}),n().autoRender(),m("index",{openTabsPage:q}))});
