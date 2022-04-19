<?php if(!defined('ClassCms')) {exit();}?>
<!DOCTYPE html>
<html>
<head>{this:head($title)}</head>
<body>
  <div class="layui-fluid">
    <div class="layui-row">
         <div class="layui-card">

<div class="layui-card-header">
    <div class="layui-row">
        <div id="cms-breadcrumb">{this:breadcrumb($breadcrumb)}</div>
        <div id="cms-right-top-button"></div>
    </div>
</div>


<div class="layui-card-body" style="min-height:450px">
<form id="newvars">
          <div class="layui-tab" lay-filter="vartab">
            <ul class="layui-tab-title" id="tablist">
            {loop $tabs as $key=>$tab}
                <li{if $key==0} class="layui-this"{/if} lay-id="tabsort_{$key}"><i class="layui-icon layui-icon-find-fill sortable-color"></i> <span>{$tab}</span></li>
            {/loop}
            </ul>
            <div class="layui-tab-content" id="varitem">
                {loop $tabs as $key=>$tab}
                    <div class="layui-tab-item{if $key==0} layui-show{/if}">
                        <table class="layui-table" lay-skin="line" >
                            <colgroup>
                            <col>
                            <col>
                            <col>
                            <col>
                            </colgroup>
                            <thead>
                            <tr>
                            <th>变量名</th>
                            <th>标识</th>
                            <th>类型</th>
                            <th></th>
                            </tr> 
                            </thead>
                            <tbody class="varsort" id="varsort_{$key}">
                                {loop $vars as $var}
                                    {if $var.tabname==$tab}
                                        <tr rel="{$var.id}">
                                        <td><i class="layui-icon layui-icon-find-fill sortable-color"></i> <span{if $var.enabled==0} class="cms-text-disabled"{/if}>{$var.formname}</span></td>
                                        <td>{$var.hash}</td>
                                        <td>{$input=cms:input:get($var.inputhash)}{if $input}{$input.inputname}{else}<span class="cms-text-disabled">{$var.inputhash}</span>{/if}</td>
                                        <td class="btn">
                                                <a class="layui-btn layui-btn-sm layui-btn-primary varmove" style="display:none">移动</a>
                                                <a class="layui-btn layui-btn-sm layui-btn-primary" href="?do=admin:var:edit&id={$var.id}">修改</a>
                                                <a class="layui-btn layui-btn-sm layui-btn-primary vardelete">删除</a>
                                        </td>
                                        </tr>
                                    {/if}
                                {/loop}
                            </tbody>
                        </table>
                    </div>
                {/loop}
            </div>
    </div>
</form>

<div class="layui-row">
        <div id="cms-left-bottom-button" class="layui-btn-container">
            <button type="button" id="addvar" class="layui-btn layui-btn-sm layui-btn-normal">增加变量</button>
            <button type="button" id="addtab" class="layui-btn layui-btn-sm layui-btn-normal">增加分组</button>
            <button type="button" id="edittab" class="layui-btn layui-btn-sm layui-btn-normal">修改组名</button>
            <button type="button" id="deltab" class="layui-btn layui-btn-sm layui-btn-normal">删除分组</button>
        </div>
        <div id="cms-right-bottom-button" class="layui-btn-container">
            <button type="button" id="savevars" class="layui-btn layui-btn-sm layui-btn-danger" >保存</button>
        </div>
      </div>

<div class="layui-hide" id="addvar_temp">
<table>
    <tr rel="0" class="layui-form layui-form-pane">
        <td>
            <input type="text" name="formname" placeholder="变量名" value="" class="layui-input formname"  lay-verify="required">
            <input type="hidden" class="tabname" name="tabname">
            <input type="hidden" class="taborder" name="taborder">
        </td>
        <td><input type="text" name="hash" placeholder="标识" class="layui-input hash"  lay-verify="required"></td>
        <td>
            {$inputselect_config.name=inputhash}
            {$inputselect_config.value=text}
            {$inputselect_config.inputhash=inputselect}
            {cms:input:form($inputselect_config)}
        </td>
        <td class="btn">
            <a class="layui-btn layui-btn-sm layui-btn-primary varremove" >删除</a>
        </td>
    </tr>
</table>
</div>
          </div>


     </div>
  </div>
  </div>

<script>
layui.use(['index','sortable'],function(){

layui.$('#addvar').click(function(){
    addvar('','','');
    showbutton();
});

layui.$('body').on('click','.varremove',function(){
    layui.$(this).parents('tr').remove();
    showbutton();
});
function showmovebutton(){
    if (layui.$('#tablist li').length>1)
    {
        layui.$('.varmove').show();
    }else{
        layui.$('.varmove').hide();
    }
}
showmovebutton();
layui.$('.varsort').on('change','input[name=formname]',function(){
    if (layui.$(this).val()=='')
    {
        layui.$(this).css('color','black');
        return true;
    }
    if (layui.$(this).val().indexOf("<") != -1 || layui.$(this).val().indexOf(">") != -1){
        if (layui.$(this).css('color')!='red')
        {
            layui.$(this).css('color','red');
            layui.view.error('变量名不允许包含特殊符号');
        }
        return;
    }
    layui.$(this).css('color','black');
});

layui.$('.varsort').on('change','input[name=hash]',function(){
    if (layui.$(this).val()=='')
    {
        layui.$(this).css('color','black');
        return true;
    }
    if(!new RegExp("^[A-Za-z]{1}[A-Za-z0-9_]{0,31}$").test(layui.$(this).val())){
        if (layui.$(this).css('color')!='red')
        {
            layui.$(this).css('color','red');
            layui.view.error('标识格式错误,格式为字母或(字母,数字,_)组合');
        }
        return;
    }
    layui.$(this).css('color','black');
});

function addvar(name,hash,kind){
    layui.$('#varitem .layui-show tbody').append(layui.$('#addvar_temp tr').prop("outerHTML"));
    layui.$('#varitem .layui-show tbody tr input.formname:last').val(name);
    layui.$('#varitem .layui-show tbody tr input.hash:last').val(hash);
    layui.$('#varitem .layui-show tbody tr input.tabname:last').val(layui.$('#tablist .layui-this span').text());
    layui.$('#varitem .layui-show tbody tr input.taborder:last').val(layui.$('#tablist li.layui-this').index());
    if (kind.length>0)
    {
        layui.$('#varitem .layui-show tbody tr select:last option[value='+kind+']').attr("selected", true);
    }
    layui.form.render();
}

layui.$('#savevars').click(function(){
    savevars();
});

function savevars(){
    layui.admin.req({type:'post',url:"?do=admin:var:addPost",data:{moduleid:{$id},vars:layui.$("#newvars").serializeArray()},async:true,beforeSend:function(){
        layui.admin.load('提交中...');
    },done: function(res){
        if (res.error==0)
        {
            var confirm=layer.confirm(res.msg, {btn: ['好的','返回'],shadeClose:1,end :function(){layui.admin.events.reload();}},function(){layui.layer.close(confirm);},function(){
                layui.admin.events.back();
                });
        }
    }});
}

function showbutton(){
    if (layui.$('#varitem tr[rel=0]').length>0)
    {
        layui.$('#savevars').show();
        layui.$('#tablist i').hide();
    }else{
        layui.$('#savevars').hide();
        layui.$('#tablist i').show();
    }
}

layui.$('#addtab').click(function(){
    layer.prompt({
      value: '',
      title: '新分组名称'
    }, 
      function(value, index, elem){
        allowadd=1;
        layui.$('#tablist li span').each(function(){
            if (layui.$(this).text()==value)
            {
                layui.layer.msg('存在重复的组名');
                allowadd=0;
            }
        });
        if (allowadd)
        {
            newid=parseInt(Math.random()*(999999-99999+1)+99999,10);
            if (layui.$('#varitem tr[rel=0]').length>0)
            {
                stylestr=' style="display:none"';
            }else{
                stylestr='';
            }
            layui.element.tabAdd('vartab',{id:"tabsort_"+newid, title: '<i class="layui-icon layui-icon-find-fill sortable-color"'+stylestr+'></i> <span>'+value+'</span>',content:'<table class="layui-table" lay-skin="line" ><colgroup><col><col><col><col></colgroup><thead><tr><th>变量名</th><th class="layui-hide-xs">标识</th><th class="layui-hide-xs">类型</th><th></th></tr> </thead><tbody class="varsort" id="varsort_'+parseInt(Math.random()*(999999-99999+1)+99999,10)+'"></tbody></table>'});
            varsortable();
            tabsortreset();
            showmovebutton();
            layui.layer.close(index);
        }
    });
});

layui.$('#edittab').click(function(){
        layer.prompt({
            value: layui.$('#tablist .layui-this span').text(),
            title: '分组名称'
        }, 
        function(value, index, elem){
            if (layui.$('#tablist .layui-this span').text()==value)
            {
                layui.layer.close(index);
                return ;
            }
            allowchange=1;
            layui.$('#tablist li span').each(function(){
                if (layui.$(this).text()==value && !layui.$(this).parent().hasClass('layui-this'))
                {
                    layui.layer.msg('存在重复的组名');
                    allowchange=0;
                }
            });
            if (allowchange)
            {
                firstvarid=layui.$('#varitem div.layui-show tbody tr').eq(0).attr('rel');
                layui.admin.req({type:'post',url:"?do=admin:var:editTab",data:{ tabname: value,varid:firstvarid},async:true,beforeSend:function(){
                    layui.admin.load('修改中...');
                },done: function(res){
                    if (res.error==0)
                    {
                        layui.$('#tablist .layui-this span').text(value);
                        layui.$('#varitem .layui-show tbody tr input.tabname').val(layui.$('#tablist .layui-this span').text());
                    }else{
                        layui.layer.msg(res.msg);
                    }
                    
                }});
            }
            layui.layer.close(index);
        });
});

layui.$('#deltab').click(function(){
    if (layui.$('#varitem .layui-show td').length)
    {
        layui.layer.msg('请先移动或删除当前组内变量');
        return;
    }
    if (layui.$('#tablist li').length<2)
    {
        layui.layer.msg('必须存在一个分组');
        return;
    }
    layui.element.tabDelete('vartab',layui.$('#tablist .layui-this').attr('lay-id'));
    showmovebutton();
});


layui.$('#varitem').on('click','.varmove',function(){
    if (layui.$('#tablist li span').length==1)
    {
        layui.layer.msg('请先增加分组');
        return;
    }
    nowtr=layui.$(this).parents('tr');
    nowvarid=layui.$(this).parents('tr').attr('rel');
    nowformname=layui.$(this).parents('tr').find('td').eq(0).text();
    confirmhtml='<div class="layui-btn-container chosetabs">';
    nowtabname=layui.$('#tablist li.layui-this span').text();
    layui.$('#tablist li span').each(function(){
        if (nowtabname==layui.$(this).text())
        {
            confirmhtml=confirmhtml+'<a class="layui-btn layui-btn-sm layui-btn-normal chosetab">'+layui.$(this).text()+'</a>';
        }else{
            confirmhtml=confirmhtml+'<a class="layui-btn layui-btn-sm layui-btn-primary chosetab">'+layui.$(this).text()+'</a>';
        }
    });
    confirmhtml=confirmhtml+'</div>';
    layui.layer.confirm(confirmhtml, {
          btn: ['移动','取消'],skin:'layer-danger',shadeClose:1,title:'移动变量 '+nowformname+' 至'}, function(){
            movetotabname=layui.$('.chosetabs').find('a.layui-btn-normal').eq(0).text();
            movetotabindex=layui.$('.chosetabs').find('a.layui-btn-normal').index();
            if (nowtabname==movetotabname)
            {
                layui.layer.msg('请选择其它分组');
            }else{
                varorder='';
                layui.$('#varitem .layui-tab-item').eq(movetotabindex).find('tr[rel]').each(function(){
                    varorder=varorder+'|'+layui.$(this).attr('rel');
                });
                layui.admin.req({type:'post',url:"?do=admin:var:move",data:{varid: nowvarid,tabname:movetotabname,movetotabindex:movetotabindex,varorder:varorder},async:true,beforeSend:function(){
                    layui.admin.load('移动中...');
                },done: function(res){
                    layui.$('#varitem .layui-tab-item').eq(movetotabindex).find('tbody').append(nowtr.prop("outerHTML"));
                    layui.layer.msg(res.msg);
                    if (res.error==0)
                    {
                        nowtr.remove();
                    }
                }});
            }
        });
});

layui.$('body').on('click','.chosetab',function(){
    layui.$(this).parent().find('a.layui-btn-normal').removeClass('layui-btn-normal').addClass('layui-btn-primary');
    layui.$(this).removeClass('layui-btn-primary').addClass('layui-btn-normal');
});

layui.$('#varitem').on('click','.vardelete',function(){
    nowtr=layui.$(this).parents('tr');
    varid=layui.$(this).parents('tr').attr('rel');
    layui.layer.confirm('是否删除此变量:'+layui.$(this).parents('tr').find('td').eq(0).text(), {
          btn: ['删除','取消'],skin:'layer-danger',title:'请确认',shadeClose:1}, function(){
            layui.admin.req({type:'post',url:"?do=admin:var:del",data:{ varid:varid},async:true,beforeSend:function(){
                layui.admin.load('删除中...');
            },done: function(res){
                if (res.error==0)
                {
                    layui.layer.msg(res.msg);
                    nowtr.remove();
                }
            }});
        });
});

varsortable();

function varsortable(){
    layui.$('.varsort').each(function(){
        new Sortable(document.getElementById(layui.$(this).attr('id')), {
            handle: '.layui-icon',
            onSort: function (evt) {
                varidarray='';
                layui.$('#varitem div.layui-show tbody tr').each(function(){
                    varidarray=varidarray+'|'+layui.$(this).attr('rel');
                });
                layui.admin.req({type:'post',url:"?do=admin:var:order",data:{ varidarray: varidarray,moduleid:{$id}},async:true,beforeSend:function(){
                    layui.admin.load('修改中...');
                },done: function(res){
                    
                }});
            }
        });
    });
}

function tabsortreset(){
    tabnamearray='';
    layui.$('#tablist li span').each(function(){
        if (tabnamearray=='')
        {
            tabnamearray=layui.$(this).text();
        }else{
            tabnamearray=tabnamearray+'|||'+layui.$(this).text();
        }
    });
    layui.admin.req({type:'post',url:"?do=admin:var:tabOrder",data:{ tabnamearray: tabnamearray,moduleid:{$id}},async:true,beforeSend:function(){
        layui.admin.load('修改中...');
    },done: function(res){
        
    }});
}

new Sortable(tablist, {
    handle: '.layui-icon',
    onSort: function (evt) {
        oldhtml=layui.$('#varitem .layui-tab-item').eq(evt.oldIndex).prop("outerHTML");
        layui.$('#varitem .layui-tab-item').eq(evt.oldIndex).remove();
        if (evt.newIndex==0)
        {
            layui.$('#varitem .layui-tab-item').eq(0).before(oldhtml);
        }else{
            layui.$('#varitem .layui-tab-item').eq(evt.newIndex-1).after(oldhtml);
        }
        varsortable();
        tabsortreset();
    }
});

});
</script>
{this:body:~()}
</body>
</html>
