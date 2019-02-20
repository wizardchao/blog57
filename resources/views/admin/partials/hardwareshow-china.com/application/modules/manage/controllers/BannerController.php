<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/13
     * Time: 23:46
     */
    require APPLICATION_PATH . '/modules/manage/controllers/CommonController.php';

    class BannerController extends CommonController
    {
        protected $homeService;

        public function init()
        {
            parent::init();
            $this->homeService = new HomeService();
        }


        /*
         * 轮播列表页
         */
        public function banner_listAction()
        {
            $request      = $this->getRequest();
            $page         = (int)$request->getParam('page');
            $page_size    = 20; //每页显示数
            $banner_title = trim(Star_String::escape($request->getParam('banner_title')));
            $param        = array(
                'banner_title' => $banner_title,
            );

            $banner_info = $this->homeService->getBannerInfoByPage($page, $page_size, $param);
            $banner_list = $banner_info['list'];
            $page_info   = $banner_info['page'];

            foreach ($banner_list as &$value) {
                $value['banner_img'] = ($value['banner_img']) ? DOMAIN_IMG . $value['banner_img'] : '';
            }

            $this->view->assign(
                array(
                    'param' => $param,
                    'banner_list' => $banner_list,
                    'page' => $page_info,
                ));
            $this->render('banner_list');
        }


        /**
         * 添加banner图
         */
        public function banner_addAction()
        {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $banner_title         = Star_String::escape($request->getParam('banner_title'));
                $banner_type         = Star_String::escape($request->getParam('banner_type'))?:1;
                $banner_img           = Star_String::escape($request->getParam('pic'));
                $banner_m_img           = Star_String::escape($request->getParam('m_pic'));
                $banner_link          = Star_String::escape($request->getParam('banner_link'));
                $banner_button1_title = Star_String::escape($request->getParam('banner_button1_title'));
                $banner_button1_href  = Star_String::escape($request->getParam('banner_button1_href'));
                $banner_button2_title = Star_String::escape($request->getParam('banner_button2_title'));
                $banner_button2_href  = Star_String::escape($request->getParam('banner_button2_href'));

                $sort_id     = Star_String::escape($request->getParam('sort_id'));
                $time_create = time();
                $time_update = time();
                $status      = 1;
                $param       = compact('banner_m_img','banner_type','banner_title', 'banner_img', 'banner_format', 'banner_link', 'banner_button1_title', 'banner_button1_href', 'banner_button2_title', 'banner_button2_href', 'sort_id', 'time_create', 'time_update', 'status');

                if (empty($banner_img)) {
                    return $this->showWarning('对不起，图片不能为空。');
                }
                $re = $this->homeService->addBanner($param);
                if ($re) {
                    return $this->showMessage('恭喜您，添加banner成功。', '/manage/banner/banner_list');
                } else {
                    return $this->showWarning('对不起，添加banner失败。');
                }
            }
            $param = array(
                'sort_id' => 255,
            );
            $this->view->assign(
                array(
                    'param' => $param,
                ));
            $this->render('banner_info');
        }


        /**
         * 删除banner图
         */
        public function banner_delAction()
        {
            $request = $this->getRequest();
            $banner_id= (int)Star_String::escape($request->getParam('banner_id'));
            if (empty($banner_id)) {
                return $this->showWarning('对不起，资料参数为空。');
            }

            $banner_info = $this->homeService->getBannerInfo($banner_id);
            if (empty($banner_info)) {
                return $this->showWarning('对不起，无效参数。');
            }

            $re = $this->homeService->delBanner($banner_id);
            if ($re) {
                return $this->showMessage('恭喜您，删除Banner成功。', '/manage/banner/banner_list');
            } else {
                return $this->showWarning('对不起，删除Banner失败。');
            }
        }


        /**
         * 编辑banner图
         */
        public function banner_editAction()
        {
            $request = $this->getRequest();
            $banner_id= (int)Star_String::escape($request->getParam('banner_id'));
            if (empty($banner_id)) {
                return $this->showWarning('对不起，资料参数为空。');
            }

            $banner_info = $this->homeService->getBannerInfo($banner_id);
            if (empty($banner_info)) {
                return $this->showWarning('对不起，无效参数。');
            }

            if ($request->isPost()) {
                $banner_title         = Star_String::escape($request->getParam('banner_title'));
                $banner_img           = Star_String::escape($request->getParam('pic'));
                $banner_m_img           = Star_String::escape($request->getParam('m_pic'));
                $banner_link          = Star_String::escape($request->getParam('banner_link'));
                $banner_button1_title = Star_String::escape($request->getParam('banner_button1_title'));
                $banner_button1_href  = Star_String::escape($request->getParam('banner_button1_href'));
                $banner_button2_title = Star_String::escape($request->getParam('banner_button2_title'));
                $banner_button2_href  = Star_String::escape($request->getParam('banner_button2_href'));
                $banner_type         = Star_String::escape($request->getParam('banner_type'))?:1;

                $sort_id     = Star_String::escape($request->getParam('sort_id'));
                $time_update = time();
                $param       = compact('banner_m_img','banner_type','banner_title', 'banner_img', 'banner_format', 'banner_link', 'banner_button1_title', 'banner_button1_href', 'banner_button2_title', 'banner_button2_href', 'sort_id',  'time_update');

                if (empty($banner_img)) {
                    return $this->showWarning('对不起，图片不能为空。');
                }
                $re = $this->homeService->editBanner($banner_id,$param);
                if ($re) {
                    return $this->showMessage('恭喜您，编辑Banner成功。', '/manage/banner/banner_list');
                } else {
                    return $this->showWarning('对不起，编辑Banner失败。');
                }
            }

            $this->view->assign(
                array(
                    'param' => $banner_info,
                ));
            $this->render('banner_info');

        }
    }
