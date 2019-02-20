<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/13
     * Time: 22:57
     */
    require APPLICATION_PATH . '/modules/manage/controllers/CommonController.php';

    class LinkController extends CommonController
    {
        public function init()
        {
            parent::init();
            $this->linkService = new LinkService();
        }


        public function link_listAction()
        {
            $request    = $this->getRequest();
            $page       = (int)$request->getParam('page');
            $page_size  = 20; //每页显示数
            $param      = array();

            $link_info = $this->linkService->getLinkInfoByPage($page, $page_size, $param);
            $link_list = $link_info['list'];
            $page_info = $link_info['page'];

            foreach ($link_list as &$value) {
                $value['link_img'] = ($value['link_img']) ? DOMAIN_IMG . $value['link_img'] : '';
            }

            $this->view->assign(
                array(
                    'param' => $param,
                    'link_list' => $link_list,
                    'page' => $page_info,
                ));
            $this->render('link_list');
        }


        public function link_addAction()
        {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $link_title  = Star_String::escape($request->getParam('link_title'));
                $link_img    = Star_String::escape($request->getParam('pic'));
                $link_url   = Star_String::escape($request->getParam('link_url'));
                $sort_id     = Star_String::escape($request->getParam('sort_id'));
                $link_category_id     = Star_String::escape($request->getParam('link_category_id'));
                $link_position_id     = (int)Star_String::escape($request->getParam('link_position_id'))?:1;
                $time_create = time();
                $time_update = time();
                $status      = 1;
                $param       = compact('link_position_id','link_title', 'link_img',  'link_url', 'sort_id', 'time_create', 'time_update', 'status','link_category_id');

                if(empty($link_title)){
                    return $this->showWarning('对不起，链接名不能为空。');
                }
                $re = $this->linkService->addLink($param);
                if ($re) {
                    return $this->showMessage('恭喜您，添加链接成功。', '/manage/link/link_list');
                } else {
                    return $this->showWarning('对不起，添加链接失败。');
                }
            }
            $param = array(
                'sort_id' => 255,
                'link_category_id' => 1001,
            );
            $this->view->assign(
                array(
                    'param' => $param,
                ));
            $this->render('link_info');
        }


        public function link_editAction()
        {
            $request = $this->getRequest();
            $link_id=(int)Star_String::escape($request->getParam('link_id'));
            if(empty($link_id)){
                return $this->showWarning('对不起，链接编号不能为空。');
            }

            $link_info=$this->linkService->getLinkInfo($link_id);
            if(empty($link_info)){
                return $this->showWarning('对不起，链接内容为空。');
            }
            if ($request->isPost()) {
                $link_title  = Star_String::escape($request->getParam('link_title'));
                $link_img    = Star_String::escape($request->getParam('pic'));
                $link_url   = Star_String::escape($request->getParam('link_url'));
                $sort_id     = Star_String::escape($request->getParam('sort_id'));
                  $link_position_id     = (int)Star_String::escape($request->getParam('link_position_id'))?:1;
                $link_category_id     = Star_String::escape($request->getParam('link_category_id'));
                $time_update = time();
                $param       = compact('link_position_id','link_title', 'link_img',  'link_url', 'sort_id','time_update','link_category_id');

                if(empty($link_title)){
                    return $this->showWarning('对不起，资料名称为空。');
                }
                $re = $this->linkService->editLink($link_id,$param);
                if ($re) {
                    return $this->showMessage('恭喜您，编辑链接成功。', '/manage/link/link_list');
                } else {
                    return $this->showWarning('对不起，编辑链接失败。');
                }
            }

            $this->view->assign(
                array(
                    'param' => $link_info,
                ));
            $this->render('link_info');
        }


        public function link_delAction()
        {
            $request = $this->getRequest();
            $link_id=(int)Star_String::escape($request->getParam('link_id'));
            if(empty($link_id)){
                return $this->showWarning('对不起，链接编号不能为空。');
            }

            $link_info=$this->linkService->getLinkInfo($link_id);
            if(empty($link_info)){
                return $this->showWarning('对不起，链接内容为空。');
            }
            $re=$this->linkService->delLink($link_id);
            if ($re) {
                return $this->showMessage('恭喜您，删除链接成功。', '/manage/link/link_list');
            } else {
                return $this->showWarning('对不起，删除链接失败。');
            }
        }


    }
