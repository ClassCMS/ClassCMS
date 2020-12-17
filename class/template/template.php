<?php
if(!defined('ClassCms')) {exit();}
class template {
    function install() {
        if(C('cms:class:get','kindeditor')) {
            $content_input='kindeditor';
        }else {
            $content_input='textarea';
        }
        begin();
        //首页
        C('cms:module:add',array('modulename'=>'首页','hash'=>'index'));
        C('cms:route:add',array('hash'=>'channel','modulehash'=>'index','uri'=>'/','classview'=>'index'));
        C('cms:route:add',array('hash'=>'list','modulehash'=>'index','uri'=>'/index_(page).html','classview'=>'index'));
        C('cms:form:add',array('hash'=>'title','formname'=>'标题','kind'=>'var','modulehash'=>'index','inputhash'=>'text'));
        C('cms:form:add',array('hash'=>'keywords','formname'=>'关键词','kind'=>'var','modulehash'=>'index','inputhash'=>'text'));
        C('cms:form:add',array('hash'=>'description','formname'=>'描述','kind'=>'var','modulehash'=>'index','inputhash'=>'textarea'));
        C('cms:form:add',array('hash'=>'logo','formname'=>'logo文字','kind'=>'var','modulehash'=>'index','inputhash'=>'text'));
        C('cms:form:add',array('hash'=>'articlesize','formname'=>'文章数量','kind'=>'var','modulehash'=>'index','inputhash'=>'number','tips'=>'首页显示的文章数量'));
        C('cms:form:add',array('hash'=>'rightad','formname'=>'右侧广告','kind'=>'var','modulehash'=>'index','inputhash'=>'textarea'));
        C('cms:form:add',array('hash'=>'tongji','formname'=>'统计代码','kind'=>'var','modulehash'=>'index','inputhash'=>'textarea'));
        C('cms:channel:add',array('channelname'=>'首页','modulehash'=>'index','var'=>array('title'=>'ClassCMS','logo'=>'ClassCMS','articlesize'=>10)));
        //单页
        C('cms:module:add',array('modulename'=>'单页','hash'=>'page'));
        C('cms:route:add',array('hash'=>'channel','modulehash'=>'page','uri'=>'/($.id)/','classview'=>'page'));
        C('cms:form:add',array('hash'=>'title','formname'=>'标题','kind'=>'var','modulehash'=>'page','inputhash'=>'text'));
        C('cms:form:add',array('hash'=>'keywords','formname'=>'关键词','kind'=>'var','modulehash'=>'page','inputhash'=>'text'));
        C('cms:form:add',array('hash'=>'description','formname'=>'描述','kind'=>'var','modulehash'=>'page','inputhash'=>'textarea'));
        C('cms:form:add',array('hash'=>'content','formname'=>'内容','kind'=>'var','modulehash'=>'page','inputhash'=>$content_input));
        C('cms:channel:add',array('channelname'=>'单页','modulehash'=>'page','var'=>array('title'=>'单页栏目','content'=>'栏目内容...')));
        //文章
        C('cms:module:add',array('modulename'=>'文章','hash'=>'article'));
        C('cms:route:add',array('hash'=>'channel','modulehash'=>'article','uri'=>'/($.id)/','classview'=>'article'));
        C('cms:route:add',array('hash'=>'list','modulehash'=>'article','uri'=>'/($.id)/page_(page).html','classview'=>'article'));
        C('cms:route:add',array('hash'=>'article','modulehash'=>'article','uri'=>'/($.id)/($id).html','classview'=>'article_content'));
        C('cms:form:add',array('hash'=>'title','formname'=>'标题','kind'=>'var','modulehash'=>'article','inputhash'=>'text'));
        C('cms:form:add',array('hash'=>'keywords','formname'=>'关键词','kind'=>'var','modulehash'=>'article','inputhash'=>'text'));
        C('cms:form:add',array('hash'=>'description','formname'=>'描述','kind'=>'var','modulehash'=>'article','inputhash'=>'textarea'));
        C('cms:form:add',array('hash'=>'pagesize','formname'=>'文章数量','kind'=>'var','modulehash'=>'article','inputhash'=>'number','defaultvalue'=>10,'tips'=>'列表页显示的文章数量'));
        //文章字段
        C('cms:form:add',array('hash'=>'title','formname'=>'标题','kind'=>'column','modulehash'=>'article','inputhash'=>'text','indexshow'=>1,'nonull'=>1));
        C('cms:form:add',array('hash'=>'keywords','formname'=>'关键词','kind'=>'column','modulehash'=>'article','inputhash'=>'text'));
        C('cms:form:add',array('hash'=>'description','formname'=>'描述','kind'=>'column','modulehash'=>'article','inputhash'=>'textarea'));
        C('cms:form:add',array('hash'=>'content','formname'=>'内容','kind'=>'column','modulehash'=>'article','inputhash'=>$content_input));
        C('cms:form:add',array('hash'=>'recommend','formname'=>'推荐','kind'=>'column','modulehash'=>'article','inputhash'=>'switch','defaultvalue'=>0));
        C($GLOBALS['C']['DbClass'].':addIndex','article_'.__CLASS__.'_article','recommend');
        C('cms:form:add',array('hash'=>'datetime','formname'=>'时间','kind'=>'column','modulehash'=>'article','inputhash'=>'datetime','indexshow'=>1,'config'=>array('nowtime'=>1)));
        $channel=C('cms:channel:add',array('channelname'=>'文章','modulehash'=>'article','var'=>array('title'=>'文章栏目')));
        if($channel) {
            C('cms:article:add',array('cid'=>$channel,'datetime'=>time(),'uid'=>'0','title'=>'测试文章','content'=>'这是一篇测试文章','recommend'=>1));
        }
        commit();
    }
    function pagelist() {
        $pagelist=pagelist();
        $pageinfo=pageinfo();
        $pagehtml='';
        if(!isset($pageinfo['pagecount']) || $pageinfo['pagecount']==1) {
            Return ;
        }
        $firstshow=true;
        $lastshow=true;
        foreach($pagelist as $thispage) {
            if($thispage['page']==1) {$firstshow=false;}
            if($thispage['page']==$pageinfo['pagecount']) {$lastshow=false;}
        }
        if($firstshow) {
            $pagehtml.='<a href="'.$pageinfo['first']['link'].'" class="'.$pageinfo['first']['class'].'">1</a>';
            if($pagelist[0]['page']-1>1) {
                $pagehtml.='<a href="javascript:;" class="">...</a>';
            }
        }
        foreach($pagelist as $key=>$page) {
            $pagehtml.='<a href="'.$page['link'].'" class="'.$page['class'].'">'.$page['title'].'</a>';
        }
        if($lastshow) {
            if($pageinfo['pagecount']-$pagelist[count($pagelist)-1]['page']>1) {
                $pagehtml.='<a href="javascript:;" class="">....</a>';
            }
            $pagehtml.='<a href="'.$pageinfo['last']['link'].'" class="'.$pageinfo['last']['class'].'">'.$pageinfo['last']['page'].'</a>';
        }
        Return $pagehtml;
    }
}