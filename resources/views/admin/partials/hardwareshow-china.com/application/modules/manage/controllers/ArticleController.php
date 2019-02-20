<?php
/*
* author: chason
* date: 2018-08-13
*/
require APPLICATION_PATH . '/modules/manage/controllers/CommonController.php';
class ArticleController extends CommonController
{
    protected $list_page_size; // 活动文章列表分页个数
    protected $clist_page_size; // 活动文章子活动列表分页个数
    protected $articleService;


    public function init()
    {
        parent::init();
        $this->list_page_size = 10;
        $this->clist_page_size = 10;
        $this->articleService = new ArticleService();
    }


    /*
    * index
    */
    public function indexAction()
    {
        $req = $this->getRequest();
        $page = (int)$req->getParam('page'); // page

        // search
        $param = array();
        if( $req->isGet() ){
            $param = array(
                'article_id' => Star_String::escape( $req->getParam('article_id') ),
                'title' => Star_String::escape( $req->getParam('title') ),
                'article_category_id' => Star_String::escape( $req->getParam('article_category_id') ),
            );
        }

        // get list
        $getInfo = $this->articleService->getArticlePage( $page, $this->list_page_size, $param );

        // 处理所属分类
        foreach($getInfo['list'] as &$val){
            $articleService = new ArticleService();
            $result= ($val['article_category_id']) ? $articleService->getCategoryById($val['article_category_id']) : 0;
            $val['category_title'] = ($result) ? $result['title'] : " ";
        }
//        array_walk(
//            $getInfo['list'],
//            function (&$val, $key, $param) {
//            $articleService = new ArticleService();
//            $result= ($val['article_category_id']) ? $articleService->getCategoryById($val['article_category_id']) : 0;
//            $val[$param['key']] = ($result) ? $result['title'] : " ";
//        }, array('key' => 'category_title'));

        // 列出子活动
        foreach( $getInfo['list'] as &$val){
            $articleService = new ArticleService();
            $result= ($val['article_id']) ? $articleService->getArticleChildList(array( 'article_id' => $val['article_id'] )) : 0;
            $val['child_list'] = ($result) ? $result : array();
        }
//        array_walk(
//            $getInfo['list'],
//            function (&$val, $key, $param) {
//            $articleService = new ArticleService();
//            $result= ($val['article_id']) ? $articleService->getArticleChildList(array( 'article_id' => $val['article_id'] )) : 0;
//            $val[$param['key']] = ($result) ? $result : array();
//        }, array('key' => 'child_list'));

        // view
        $category_list = $this->articleService->getCategoryList(array());
        $this->view->assign(
            array(
                'category_list' => $category_list,
                'param' => $param,
                'list' => $getInfo['list'],
                'page' => $getInfo['page']
            )
        );
        $this->render('index');
    }


    /*
    * add & edit
    */
    public function aeAction()
    {
        $req = $this->getRequest();
        $article_id = Star_String::escape($req->getParam('article_id'));
        $article_category_id = Star_String::escape($req->getParam('article_category_id'));
        $type = Star_String::escape($req->getParam('type'));
        $c_desc = stripslashes(Star_String::escape($req->getParam('c_desc')));
        $c_name = Star_String::escape($req->getParam('c_name'));
        $c_position = Star_String::escape($req->getParam('c_position'));
        $c_company = Star_String::escape($req->getParam('c_company'));
        $title = Star_String::escape($req->getParam('title'));
        $logo = Star_String::escape($req->getParam('logo'));
        $pic = Star_String::escape($req->getParam('pic'));
        $desc = Star_String::escape($req->getParam('content'));
        $sort_id = Star_String::escape($req->getParam('sort_id'));
        $is_banner = Star_String::escape($req->getParam('is_banner'));
        $banner_img = Star_String::escape($req->getParam('banner_img'));
        $banner_title = Star_String::escape($req->getParam('banner_title'));
        $banner_url = Star_String::escape($req->getParam('banner_url')); 
        $page_title = Star_String::escape($req->getParam('page_title'));
        $page_keyword = Star_String::escape($req->getParam('page_keyword'));
        $page_description = Star_String::escape($req->getParam('page_description'));
        $template_name = Star_String::escape($req->getParam('template_name'));

        // insert execute
        if( empty($article_id) ){
            if( $req->isPost() ){
                $getInfo = $this->articleService->addArticle(
                    array(
                        'article_category_id' => $article_category_id,
                        'title' => $title,
                        'type' => $type,
                        'c_desc' => $c_desc,
                        'c_name' => $c_name,
                        'c_position' => $c_position,
                        'c_company' => $c_company,
                        'logo' => $logo,
                        'pic' => $pic,
                        'is_banner' => $is_banner,
                        'banner_img' => $banner_img,
                        'banner_title' => $banner_title,
                        'banner_url' => $banner_url,                        
                        'desc' => $desc,
                        'page_title' => $page_title,
                        'page_keyword' => $page_keyword,
                        'page_description' => $page_description,                        
                        'sort_id' => ($sort_id) ? $sort_id : 255,
                        'tm_create' => time(),
                        'tm_update' => time(),
                        'status' => 1,
                        'template_name' => $template_name,
                    )
                );
                if( $getInfo ) return $this->showMessage('操作成功！', '/manage/article/index');
            }
        }

        // update execute
        else {
            if( $req->isPost() ){
                $getInfo = $this->articleService->updateArticle(
                    array(
                        'article_id' => $article_id,
                    ),
                    array(
                        'article_category_id' => $article_category_id,
                        'title' => $title,
                        'type' => $type,
                        'c_desc' => $c_desc,
                        'c_name' => $c_name,
                        'c_position' => $c_position,
                        'c_company' => $c_company,
                        'is_banner' => $is_banner,
                        'banner_img' => $banner_img,
                        'banner_title' => $banner_title,
                        'banner_url' => $banner_url,
                        'logo' => $logo,
                        'pic' => $pic,
                        'desc' => $desc,
                        'page_title' => $page_title,
                        'page_keyword' => $page_keyword,
                        'page_description' => $page_description,
                        'sort_id' => ($sort_id) ? $sort_id : 255,
                        'tm_update' => time(),
                        'template_name' => $template_name,
                    )
                );
                if( $getInfo ) return $this->showMessage('操作成功！', '/manage/article/index');
            }
        }

        // edit preview
        $editViewInfo = array();
        if( !empty($article_id) ) $editViewInfo = $this->articleService->getArticleById($article_id);
        $this->view->assign(
            array(
                'category_list' => $this->articleService->getCategoryList(),
                'param' => $editViewInfo,
            )
        );
        $this->render('ae');
    }


    /*
    * del
    */
    public function delAction()
    {
        $req = $this->getRequest();
        $id = (int) $req->getParam('article_id');

        $arr = array(
            'article_id' => $id,
        );
        if( $this->articleService->delArticle($arr) ){
            header('Location: /manage/article/index');
        }
    }


    /*
    * 活动子活动列表
    */
    public function clistAction()
    {
        $req = $this->getRequest();
        $article_id = (int)$req->getParam('article_id');
        $page = (int)$req->getParam('page'); // page

        // search
        $param = array();
        if( $req->isGet() ){
            $param = array(
                'article_child_id' => Star_String::escape( $req->getParam('article_child_id') ),
                'title' => Star_String::escape( $req->getParam('title') ),
            );
        }

        // get list
        $param['article_id'] = $article_id;
        $getInfo = $this->articleService->getArticleChildPage( $page, $this->clist_page_size, $param );

        // view
        $this->view->assign(
            array(
                'param' => $param,
                'article' => $this->articleService->getArticleById($article_id), // 所属活动
                'list' => $getInfo['list'],
                'page' => $getInfo['page']
            )
        );
        $this->render('clist');
    }


    /*
    * 子活动add & edit
    */
    public function caeAction()
    {
        $req = $this->getRequest();
        $article_child_id = Star_String::escape($req->getParam('article_child_id'));
        $article_id = Star_String::escape($req->getParam('article_id'));
        $view_article_id = Star_String::escape($req->getParam('view_article_id'));
        $title = Star_String::escape($req->getParam('title'));
        $is_banner = Star_String::escape($req->getParam('is_banner'));
        $banner_img = Star_String::escape($req->getParam('banner_img'));
        $banner_title = Star_String::escape($req->getParam('banner_title'));
        $banner_url = Star_String::escape($req->getParam('banner_url'));        
        $desc = Star_String::escape($req->getParam('content'));
        $page_title = Star_String::escape($req->getParam('page_title'));
        $page_keyword = Star_String::escape($req->getParam('page_keyword'));
        $page_description = Star_String::escape($req->getParam('page_description'));        
        $sort_id = Star_String::escape($req->getParam('sort_id'));

        // insert execute
        if( empty($article_child_id) ){
            if( $req->isPost() ){
                $getInfo = $this->articleService->addArticleChild(
                    array(
                        'article_id' => $article_id,
                        'title' => $title,
                        'is_banner' => $is_banner,
                        'banner_img' => $banner_img,
                        'banner_title' => $banner_title,
                        'banner_url' => $banner_url,
                        'desc' => $desc,
                        'page_title' => $page_title,
                        'page_keyword' => $page_keyword,
                        'page_description' => $page_description,                        
                        'sort_id' => ($sort_id) ? $sort_id : 255,
                        'tm_create' => time(),
                        'tm_update' => time(),
                        'status' => 1,
                    )
                );
                if( $getInfo ) return $this->showMessage('操作成功！', '/manage/article/clist/?article_id='.$view_article_id);
            }
        }

        // update execute
        else {
            if( $req->isPost() ){
                $getInfo = $this->articleService->updateArticleChild(
                    array(
                        'article_child_id' => $article_child_id,
                    ),
                    array(
                        'article_id' => $article_id,
                        'title' => $title,
                        'is_banner' => $is_banner,
                        'banner_img' => $banner_img,
                        'banner_title' => $banner_title,
                        'banner_url' => $banner_url,
                        'desc' => $desc,
                        'page_title' => $page_title,
                        'page_keyword' => $page_keyword,
                        'page_description' => $page_description,                        
                        'sort_id' => ($sort_id) ? $sort_id : 255,
                        'tm_update' => time(),
                    )
                );
                if( $getInfo ) return $this->showMessage('操作成功！', '/manage/article/clist/?article_id='.$view_article_id);
            }
        }

        // edit preview
        $editViewInfo = array();
        if( !empty($article_child_id) ) $editViewInfo = $this->articleService->getArticleChildById($article_child_id);
        
        $editViewInfo['view_article_id'] = $view_article_id;
        $this->view->assign(
            array(
                'article' => $this->articleService->getArticleById($view_article_id), // 所属活动
                'article_list' => $this->articleService->getArticleList(),
                'param' => $editViewInfo,
            )
        );
        $this->render('cae');
    }


    /*
    * 子活动 del
    */
    public function cdelAction()
    {
        $req = $this->getRequest();
        $article_id = (int) $req->getParam('article_id');
        $id = (int) $req->getParam('article_child_id');

        $arr = array(
            'article_child_id' => $id,
        );
        if( $this->articleService->delArticleChild($arr) ){
            header('Location: /manage/article/clist/?article_id='.$article_id);
        }
    }


    /*
    * category list
    */
    public function categoryAction()
    {
        $category_list = $this->articleService->getCategoryList();
        $data = array(
            'category_list' => $category_list,
        );
        $this->view->assign($data);
        $this->render('category');
    }


    /*
    * category add & edit
    */
    public function category_aeAction()
    {
        $req = $this->getRequest();
        $article_category_id = Star_String::escape($req->getParam('article_category_id'));
        $title = Star_String::escape($req->getParam('title'));
        $title_sub = Star_String::escape($req->getParam('title_sub'));
        $desc_s = Star_String::escape($req->getParam('desc_s'));
        $desc = Star_String::escape($req->getParam('content'));
        $template = Star_String::escape($req->getParam('template'));
        $sort_id = Star_String::escape($req->getParam('sort_id'));
        $banner_img = Star_String::escape($req->getParam('banner_img'));
        $banner_title = Star_String::escape($req->getParam('banner_title'));
        $banner_url = Star_String::escape($req->getParam('banner_url')); 
        $page_title = Star_String::escape($req->getParam('page_title'));
        $page_keyword = Star_String::escape($req->getParam('page_keyword'));
        $page_description = Star_String::escape($req->getParam('page_description'));

        // insert execute
        if( empty($article_category_id) ){
            if( $req->isPost() ){
                $getInfo = $this->articleService->addCategory(
                    array(
                        'title' => $title,
                        'title_sub' => $title_sub,
                        'desc_s' => $desc_s,
                        'desc' => $desc,
                        'template' => $template,
                        'banner_img' => $banner_img,
                        'banner_title' => $banner_title,
                        'banner_url' => $banner_url,                        
                        'sort_id' => ($sort_id) ? $sort_id : 255,
                        'page_title' => $page_title,
                        'page_keyword' => $page_keyword,
                        'page_description' => $page_description,                         
                        'tm_create' => time(),
                        'tm_update' => time(),
                        'status' => 1,
                    )
                );
                if( $getInfo ) return $this->showMessage('操作成功！', '/manage/article/category');
            }
        }

        // update execute
        else {
            if( $req->isPost() ){
                $getInfo = $this->articleService->updateCategory(
                    array(
                        'article_category_id' => $article_category_id,
                    ),
                    array(
                        'title' => $title,
                        'title_sub' => $title_sub,
                        'desc_s' => $desc_s,
                        'desc' => $desc,                        
                        'template' => $template,
                        'banner_img' => $banner_img,
                        'banner_title' => $banner_title,
                        'banner_url' => $banner_url,                        
                        'page_title' => $page_title,
                        'page_keyword' => $page_keyword,
                        'page_description' => $page_description,                         
                        'sort_id' => ($sort_id) ? $sort_id : 255,
                        'tm_update' => time(),
                    )
                );
                if( $getInfo ) return $this->showMessage('操作成功！', '/manage/article/category');
            }
        }

        // edit preview
        $editViewInfo = array();
        if( !empty($article_category_id) ) $editViewInfo = $this->articleService->getCategoryById($article_category_id);
        $this->view->assign( array('param' => $editViewInfo) );
        $this->render('category_ae');
    }


    /*
    * category del
    */
    public function category_delAction()
    {
        $req = $this->getRequest();
        $id = (int) $req->getParam('article_category_id');

        $arr = array(
            'article_category_id' => $id,
        );
        if( $this->articleService->delCategory($arr) ){
            header('Location: /manage/article/category');
        }
    }


}