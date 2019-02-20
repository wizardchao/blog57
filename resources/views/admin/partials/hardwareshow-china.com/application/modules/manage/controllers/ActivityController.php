<?php
/*
* author: chason
* date: 2018-08-13
*/
require APPLICATION_PATH . '/modules/manage/controllers/CommonController.php';
class ActivityController extends CommonController
{
    protected $list_page_size; // 活动列表分页个数
    protected $clist_page_size; // 活动子活动列表分页个数
    protected $activityService;


    public function init()
    {
        parent::init();
        $this->list_page_size = 10;
        $this->clist_page_size = 10;
        $this->activityService = new ActivityService();
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
                'activity_id' => Star_String::escape( $req->getParam('activity_id') ),
                'title' => Star_String::escape( $req->getParam('title') ),
                'activity_category_id' => Star_String::escape( $req->getParam('activity_category_id') ),
            );
        }

        // get list
        $getInfo = $this->activityService->getActivityPage( $page, $this->list_page_size, $param );

        // 处理所属分类
        foreach($getInfo['list'] as &$val){
            $activityService = new ActivityService();
            $result= ($val['activity_category_id']) ? $activityService->getCategoryById($val['activity_category_id']) : 0;
            $val['category_title'] = ($result) ? $result['title'] : " ";
        }
//        array_walk(
//            $getInfo['list'],
//            function (&$val, $key, $param) {
//            $activityService = new ActivityService();
//            $result= ($val['activity_category_id']) ? $activityService->getCategoryById($val['activity_category_id']) : 0;
//            $val[$param['key']] = ($result) ? $result['title'] : " ";
//        }, array('key' => 'category_title'));

        // 列出子活动
        foreach( $getInfo['list'] as &$val){
            $activityService = new ActivityService();
            $result= ($val['activity_id']) ? $activityService->getActivityChildList(array( 'activity_id' => $val['activity_id'] )) : 0;
            $val['child_list'] = ($result) ? $result : array();
        }
//        array_walk(
//            $getInfo['list'],
//            function (&$val, $key, $param) {
//            $activityService = new ActivityService();
//            $result= ($val['activity_id']) ? $activityService->getActivityChildList(array( 'activity_id' => $val['activity_id'] )) : 0;
//            $val[$param['key']] = ($result) ? $result : array();
//        }, array('key' => 'child_list'));

        // view
        $category_list = $this->activityService->getCategoryList(array());
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
        $activity_id = Star_String::escape($req->getParam('activity_id'));
        $activity_category_id = Star_String::escape($req->getParam('activity_category_id'));
        $title = Star_String::escape($req->getParam('title'));
        $is_banner = Star_String::escape($req->getParam('is_banner'));
        $banner_img = Star_String::escape($req->getParam('banner_img'));
        $banner_title = Star_String::escape($req->getParam('banner_title'));
        $banner_url = Star_String::escape($req->getParam('banner_url'));
        $is_home = Star_String::escape($req->getParam('is_home'));
        $desc = stripslashes(Star_String::escape($req->getParam('content')));
        $page_title = Star_String::escape($req->getParam('page_title'));
        $page_keyword = Star_String::escape($req->getParam('page_keyword'));
        $page_description = Star_String::escape($req->getParam('page_description'));
        $sort_id = Star_String::escape($req->getParam('sort_id'));
        $template_name = Star_String::escape($req->getParam('template_name'));
        $img = Star_String::escape($req->getParam('img'));
        $banner_m_img = Star_String::escape($req->getParam('banner_m_img'));

        // insert execute
        if( empty($activity_id) ){
            if( $req->isPost() ){
                $getInfo = $this->activityService->addActivity(
                    array(
                        'activity_category_id' => $activity_category_id,
                        'title' => $title,
                        'img' => $img,
                        'is_banner' => $is_banner,
                        'banner_img' => $banner_img,
                        'banner_title' => $banner_title,
                        'banner_url' => $banner_url,
                        'is_home' => $is_home,
                        'desc' => $desc,
                        'page_title' => $page_title,
                        'page_keyword' => $page_keyword,
                        'page_description' => $page_description,
                        'sort_id' => ($sort_id) ? $sort_id : 255,
                        'template_name' => $template_name,
                        'banner_m_img' => $banner_m_img,
                        'tm_create' => time(),
                        'tm_update' => time(),
                        'status' => 1,
                    )
                );
                if( $getInfo ) return $this->showMessage('操作成功！', '/manage/activity/index');
            }
        }

        // update execute
        else {
            if( $req->isPost() ){
                $getInfo = $this->activityService->updateActivity(
                    array(
                        'activity_id' => $activity_id,
                    ),
                    array(
                        'activity_category_id' => $activity_category_id,
                        'title' => $title,
                          'img' => $img,
                        'is_banner' => $is_banner,
                        'banner_img' => $banner_img,
                        'banner_title' => $banner_title,
                        'banner_url' => $banner_url,
                        'is_home' => $is_home,
                        'desc' => $desc,
                        'template_name' => $template_name,
                        'page_title' => $page_title,
                        'page_keyword' => $page_keyword,
                        'page_description' => $page_description,
                        'sort_id' => ($sort_id) ? $sort_id : 255,
                        'tm_update' => time(),
                          'banner_m_img' => $banner_m_img,
                    )
                );
                if( $getInfo ) return $this->showMessage('操作成功！', '/manage/activity/index');
            }
        }

        // edit preview
        $editViewInfo = array();
        if( !empty($activity_id) ) $editViewInfo = $this->activityService->getActivityById($activity_id);
        $this->view->assign(
            array(
                'category_list' => $this->activityService->getCategoryList(),
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
        $id = (int) $req->getParam('activity_id');

        $arr = array(
            'activity_id' => $id,
        );
        if( $this->activityService->delActivity($arr) ){
            header('Location: /manage/activity/index');
        }
    }


    /*
    * 活动子活动列表
    */
    public function clistAction()
    {
        $req = $this->getRequest();
        $activity_id = (int)$req->getParam('activity_id');
        $page = (int)$req->getParam('page'); // page

        // search
        $param = array();
        if( $req->isGet() ){
            $param = array(
                'activity_child_id' => Star_String::escape( $req->getParam('activity_child_id') ),
                'title' => Star_String::escape( $req->getParam('title') ),
            );
        }

        // get list
        $param['activity_id'] = $activity_id;
        $getInfo = $this->activityService->getActivityChildPage( $page, $this->clist_page_size, $param );

        // view
        $this->view->assign(
            array(
                'param' => $param,
                'activity' => $this->activityService->getActivityById($activity_id), // 所属活动
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
        $activity_child_id = Star_String::escape($req->getParam('activity_child_id'));
        $activity_id = Star_String::escape($req->getParam('activity_id'));
        $view_activity_id = Star_String::escape($req->getParam('view_activity_id'));
        $title = Star_String::escape($req->getParam('title'));
        $pic = Star_String::escape($req->getParam('pic'));
        $desc = stripslashes(Star_String::escape($req->getParam('content')));
        $link = Star_String::escape($req->getParam('link'));
        $sort_id = Star_String::escape($req->getParam('sort_id'));

        // insert execute
        if( empty($activity_child_id) ){
            if( $req->isPost() ){
                $getInfo = $this->activityService->addActivityChild(
                    array(
                        'activity_id' => $activity_id,
                        'title' => $title,
                        'pic' => $pic,
                        'desc' => $desc,
                        'link' => $link,
                        'sort_id' => ($sort_id) ? $sort_id : 255,
                        'tm_create' => time(),
                        'tm_update' => time(),
                        'status' => 1,
                    )
                );
                if( $getInfo ) return $this->showMessage('操作成功！', '/manage/activity/clist/?activity_id='.$view_activity_id);
            }
        }

        // update execute
        else {
            if( $req->isPost() ){
                $getInfo = $this->activityService->updateActivityChild(
                    array(
                        'activity_child_id' => $activity_child_id,
                    ),
                    array(
                        'activity_id' => $activity_id,
                        'title' => $title,
                        'pic' => $pic,
                        'desc' => $desc,
                        'link' => $link,
                        'sort_id' => ($sort_id) ? $sort_id : 255,
                        'tm_update' => time(),
                        'status' => 1,
                    )
                );
                if( $getInfo ) return $this->showMessage('操作成功！', '/manage/activity/clist/?activity_id='.$view_activity_id);
            }
        }

        // edit preview
        $editViewInfo = array();
        if( !empty($activity_child_id) ) $editViewInfo = $this->activityService->getActivityChildById($activity_child_id);

        $editViewInfo['view_activity_id'] = $view_activity_id;
        $this->view->assign(
            array(
                'activity' => $this->activityService->getActivityById($view_activity_id), // 所属活动
                'activity_list' => $this->activityService->getActivityList(),
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
        $activity_id = (int) $req->getParam('activity_id');
        $id = (int) $req->getParam('activity_child_id');

        $arr = array(
            'activity_child_id' => $id,
        );
        if( $this->activityService->delActivityChild($arr) ){
            header('Location: /manage/activity/clist/?activity_id='.$activity_id);
        }
    }


    /*
    * category list
    */
    public function categoryAction()
    {
        $category_list = $this->activityService->getCategoryList();
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
        $activity_category_id = Star_String::escape($req->getParam('activity_category_id'));
        $title = Star_String::escape($req->getParam('title'));
        $template = Star_String::escape($req->getParam('template'));
        $sort_id = Star_String::escape($req->getParam('sort_id'));

        // insert execute
        if( empty($activity_category_id) ){
            if( $req->isPost() ){
                $getInfo = $this->activityService->addCategory(
                    array(
                        'title' => $title,
                        'template' => $template,
                        'sort_id' => ($sort_id) ? $sort_id : 255,
                        'tm_create' => time(),
                        'tm_update' => time(),
                        'status' => 1,
                    )
                );
                if( $getInfo ) return $this->showMessage('操作成功！', '/manage/activity/category');
            }
        }

        // update execute
        else {
            if( $req->isPost() ){
                $getInfo = $this->activityService->updateCategory(
                    array(
                        'activity_category_id' => $activity_category_id,
                    ),
                    array(
                        'title' => $title,
                        'template' => $template,
                        'sort_id' => ($sort_id) ? $sort_id : 255,
                        'tm_update' => time(),
                    )
                );
                if( $getInfo ) return $this->showMessage('操作成功！', '/manage/activity/category');
            }
        }

        // edit preview
        $editViewInfo = array();
        if( !empty($activity_category_id) ) $editViewInfo = $this->activityService->getCategoryById($activity_category_id);
        $this->view->assign( array('param' => $editViewInfo) );
        $this->render('category_ae');
    }


    /*
    * category del
    */
    public function category_delAction()
    {
        $req = $this->getRequest();
        $id = (int) $req->getParam('activity_category_id');

        $arr = array(
            'activity_category_id' => $id,
        );
        if( $this->activityService->delCategory($arr) ){
            header('Location: /manage/activity/category');
        }
    }


}
