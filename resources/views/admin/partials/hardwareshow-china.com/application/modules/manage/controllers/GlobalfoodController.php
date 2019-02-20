<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/14
     * Time: 9:44
     */
    require APPLICATION_PATH . '/modules/manage/controllers/CommonController.php';

    class GlobalfoodController extends CommonController
    {

        public function init()
        {
            parent::init();
            $this->homeService = new HomeService();
        }


        public function globalfood_listAction()
        {
            $request      = $this->getRequest();
            $page         = (int)$request->getParam('page');
            $page_size    = 20; //每页显示数
            $param        = array();

            $global_food_info = $this->homeService->getGlobalFoodInfoByPage($page, $page_size, $param);
            $global_food_list = $global_food_info['list'];
            $page_info   = $global_food_info['page'];

            foreach ($global_food_list as &$value) {
                $value['globalfood_img'] = ($value['globalfood_img']) ? DOMAIN_IMG . $value['globalfood_img'] : '';
            }

            $this->view->assign(
                array(
                    'param' => $param,
                    'global_food_list' => $global_food_list,
                    'page' => $page_info,
                ));
            $this->render('globalfood_list');
        }


        public function globalfood_addAction()
        {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $globalfood_title         = Star_String::escape($request->getParam('globalfood_title'));
                $globalfood_intro         = Star_String::escape($request->getParam('globalfood_intro'));
                $globalfood_img           = Star_String::escape($request->getParam('globalfood_img'));
                $globalfood_link          = Star_String::escape($request->getParam('globalfood_link'));
                $sort_id     = Star_String::escape($request->getParam('sort_id'));
                $time_create = time();
                $time_update = time();
                $status      = 1;
                $param       = compact('globalfood_title', 'globalfood_img', 'globalfood_intro', 'globalfood_link', 'sort_id', 'time_create', 'time_update', 'status');

                if (empty($globalfood_img)) {
                    return $this->showWarning('对不起，图片不能为空。');
                }
                $re = $this->homeService->addGlobalFood($param);
                if ($re) {
                    return $this->showMessage('恭喜您，添加站点成功。', '/manage/globalfood/globalfood_list');
                } else {
                    return $this->showWarning('对不起，添加站点失败。');
                }
            }
            $param = array(
                'globalfood_type' => 2,
                'sort_id' => 255,
            );
            $this->view->assign(
                array(
                    'param' => $param,
                ));
            $this->render('globalfood_info');
        }


        public function globalfood_editAction()
        {
            $request = $this->getRequest();
            $globalfood_id=(int)Star_String::escape($request->getParam('globalfood_id'));
            if(empty($globalfood_id)){
                return $this->showWarning('对不起，编号不能为空。');
            }

            $link_info=$this->homeService->getGlobalFoodInfo($globalfood_id);
            if(empty($link_info)){
                return $this->showWarning('对不起，链接内容为空。');
            }
            if ($request->isPost()) {
                $globalfood_title         = Star_String::escape($request->getParam('globalfood_title'));
                $globalfood_intro         = Star_String::escape($request->getParam('globalfood_intro'));
                $globalfood_img           = Star_String::escape($request->getParam('globalfood_img'));
                $globalfood_link          = Star_String::escape($request->getParam('globalfood_link'));
                $sort_id     = Star_String::escape($request->getParam('sort_id'));
                $time_update = time();
                $param       = compact('globalfood_title', 'globalfood_img', 'globalfood_intro', 'globalfood_link', 'sort_id',  'time_update');

                if (empty($globalfood_img)) {
                    return $this->showWarning('对不起，图片不能为空。');
                }
                $re = $this->homeService->editGlobalFood($globalfood_id,$param);
                if ($re) {
                    return $this->showMessage('恭喜您，编辑站点成功。', '/manage/globalfood/globalfood_list');
                } else {
                    return $this->showWarning('对不起，编辑站点失败。');
                }
            }

            $this->view->assign(
                array(
                    'param' => $link_info,
                ));
            $this->render('globalfood_info');
        }


        public function globalfood_delAction()
        {
            $request = $this->getRequest();
            $globalfood_id=(int)Star_String::escape($request->getParam('globalfood_id'));
            if(empty($globalfood_id)){
                return $this->showWarning('对不起，编号不能为空。');
            }

            $link_info=$this->homeService->getGlobalFoodInfo($globalfood_id);
            if(empty($link_info)){
                return $this->showWarning('对不起，链接内容为空。');
            }

            $re=$this->homeService->delGlobalFood($globalfood_id);
            if ($re) {
                return $this->showMessage('恭喜您，删除站点成功。', '/manage/globalfood/globalfood_list');
            } else {
                return $this->showWarning('对不起，删除站点失败。');
            }

        }
    }