<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/15
     * Time: 9:38
     */
    require APPLICATION_PATH . '/modules/manage/controllers/CommonController.php';

    class VisitorController extends CommonController
    {
        protected $page_size;

        public function init()
        {
            parent::init();
            $this->page_size      = 20;
            $this->visitorService = new VisitorService();
        }


        public function visitor_listAction()
        {
            $request   = $this->getRequest();
            $page      = (int)$request->getParam('page');
            $page_size = 20; //每页显示数
            $param     = array();

            $visitor_info = $this->visitorService->getVisitorInfoByPage($page, $page_size, $param);
            $visitor_list = $visitor_info['list'];
            $page_info    = $visitor_info['page'];

            foreach ($visitor_list as &$value) {
                $value['visitor_img'] = ($value['visitor_img']) ? DOMAIN_IMG . $value['visitor_img'] : '';
            }

            $this->view->assign(
                array(
                    'param' => $param,
                    'visitor_list' => $visitor_list,
                    'page' => $page_info,
                ));
            $this->render('visitor_list');
        }


        public function visitor_addAction()
        {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $visitor_title = Star_String::escape($request->getParam('visitor_title'));
                $visitor_img   = Star_String::escape($request->getParam('pic'));
                $visitor_link  = Star_String::escape($request->getParam('visitor_link'));
                $sort_id  = Star_String::escape($request->getParam('sort_id'));
                $time_create  = time();
                $time_update=time();
                $status=1;
                $param=compact('visitor_title','visitor_link','visitor_img','status','time_update','time_create');
                $rs=$this->visitorService->addVisitor($param);
                if ($rs) {
                    return $this->showMessage('恭喜您，添加展团成功。', '/manage/visitor/visitor_list');
                } else {
                    return $this->showWarning('对不起，添加展团失败。');
                }
            }
            $this->view->assign(
                array(
                    'param' => array(
                        'sort_id' => 255,
                    ),
                ));
            $this->render('visitor_info');
        }


        public function visitor_editAction()
        {
            $request = $this->getRequest();
            $id      = (int)$request->getParam('id');

            if(empty($id)){
                return $this->showWarning('对不起，参数不能为空。');
            }
            $visitor_info=$this->visitorService->getVisitorInfo($id);
            if(empty($visitor_info)){
                return $this->showWarning('对不起，无效参数。');
            }
            if ($request->isPost()) {
                $visitor_title = Star_String::escape($request->getParam('visitor_title'));
                $visitor_img   = Star_String::escape($request->getParam('pic'));
                $visitor_link  = Star_String::escape($request->getParam('visitor_link'));
                $sort_id  = Star_String::escape($request->getParam('sort_id'));
                $time_update=time();
                $param=compact('visitor_title','visitor_link','visitor_img','time_update','sort_id');
                $rs=$this->visitorService->editVisitor($id,$param);
                if ($rs) {
                    return $this->showMessage('恭喜您，编辑展团成功。', '/manage/visitor/visitor_list');
                } else {
                    return $this->showWarning('对不起，编辑展团失败。');
                }
            }
            $this->view->assign(
                array(
                    'param' => $visitor_info,
                ));
            $this->render('visitor_info');
        }


        public function visitor_delAction()
        {
            $request = $this->getRequest();
            $id      = (int)$request->getParam('id');

            if(empty($id)){
                return $this->showWarning('对不起，参数不能为空。');
            }
            $visitor_info=$this->visitorService->getVisitorInfo($id);
            if(empty($visitor_info)){
                return $this->showWarning('对不起，无效参数。');
            }

            $re=$this->visitorService->delVisitor($id);
            if ($re) {
                return $this->showMessage('恭喜您，删除展团成功。', '/manage/visitor/visitor_list');
            } else {
                return $this->showWarning('对不起，删除展团失败。');
            }
        }
    }