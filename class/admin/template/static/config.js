/**

 @Name：layuiAdmin iframe版全局配置
 @Author：贤心
 @Site：http://www.layui.com/admin/
 @License：LPPL（layui付费产品协议）
    
 */

layui.define(["laytpl","layer","element","util"],function(a){a("setter",{container:"Clas"+"sC"+"MS",base:layui.cache.base,views:layui.cache.base+"tpl/",entry:"index",engine:".html",pageTabs:!1,name:"ClassCmsAdmin",tableName:"ClassCmsAdmin",MOD_NAME:"admin",debug:!0,request:{tokenName:!1},response:{statusName:"code",errorName:"error",statusCode:{ok:0,logout:1001,csrf:1002},msgName:"msg",dataName:"data"},extend:["sortable","xmselect"]})});
