<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/15
     * Time: 17:15
     */
    require APPLICATION_PATH . '/modules/manage/controllers/CommonController.php';

    class AdController extends CommonController
    {
        protected $adService;

        public function init()
        {
            parent::init();
            $this->adService = new AdService();
        }


        /*
         * 轮播列表页
         */
        public function ad_listAction()
        {
            $request = $this->getRequest();
            $page    = (int)$request->getParam('page');
            $ad_id   = (int)$request->getParam('id');
            if ($ad_id) {
                $ad_switch = (int)$request->getParam('ad_switch');
                $arr       = array( 0, 1 );
                if (!in_array($ad_switch, $arr)) {
                    return $this->showWarning('对不起，参数错误。');
                }
                $time_update=time();
                $switch_param = compact('ad_switch', 'time_update');
                $this->adService->editAd($ad_id, $switch_param);
                unset($switch_param);
                unset($arr);
                unset($ad_switch);
                unset($time_update);
            }
            $page_size = 20; //每页显示数
            $ad_title  = trim(Star_String::escape($request->getParam('ad_title')));
            $param     = array(
                'ad_title' => $ad_title,
            );

            $ad_info = $this->adService->getAdInfoByPage($page, $page_size, $param);

            $ad_list     = $ad_info['list'];
            $page_info   = $ad_info['page'];
            $cate_info   = $this->adService->getAdCate();
            $ad_cate_arr = array();
            foreach ($cate_info as $info) {
                $ad_cate_arr[$info['id']] = $info['title'];
            }
            unset($cate_info);
            foreach ($ad_list as &$value) {
                $value['ad_img'] = ($value['ad_img']) ? DOMAIN_IMG . $value['ad_img'] : '';
                $value['ad_key'] = ($ad_cate_arr[$value['ad_key']]) ?$ad_cate_arr[$value['ad_key']]: '';
            }

            $this->view->assign(
                array(
                    'param' => $param,
                    'ad_list' => $ad_list,
                    'page' => $page_info,
                )
            );
            $this->render('ad_list');
        }


        public function ad_addAction()
        {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $ad_title    = $request->getParam('ad_title');
                $ad_link     = $request->getParam('ad_link');
                $ad_desc     = $request->getParam('ad_desc');
                $ad_img      = $request->getParam('ad_img');
                $ad_switch     = $request->getParam('ad_switch');
                $ad_home     = (int)$request->getParam('ad_home');
                $sort_id     = $request->getParam('sort_id')?:255;
                $time_update = time();
                $time_create = time();
                $status      = 1;
                $param       = compact('ad_home', 'sort_id', 'ad_img', 'ad_link', 'ad_title', 'time_update', 'time_create', 'status', 'ad_desc', 'ad_switch');
                $re          = $this->adService->addAd($param);
                if ($re) {
                    return $this->showMessage('恭喜您，添加广告位成功。', '/manage/ad/ad_list');
                } else {
                    return $this->showWarning('对不起，添加广告位失败。');
                }
            }
            $cate_info = $this->adService->getAdCate();

            $param = array(
                'sort_id' => 255,
            );
            $this->view->assign(
                array(
                    'cate_info' => $cate_info,
                    'param' => $param,
                )
            );
            $this->render('ad_info');
        }


        public function ad_delAction()
        {
            $request = $this->getRequest();
            $ad_id   = (int)$request->getParam('id');
            if (empty($ad_id)) {
                return $this->showWarning('对不起，参数不能为空。');
            }

            $ad_info = $this->adService->getAdInfo($ad_id);
            if (empty($ad_info)) {
                return $this->showWarning('无效参数。');
            }

            $re = $this->adService->delAd($ad_id);
            if ($re) {
                return $this->showMessage('恭喜您，删除广告位成功。', '/manage/ad/ad_list');
            } else {
                return $this->showWarning('对不起，删除广告位失败。');
            }
        }


        public function ad_editAction()
        {
            $request = $this->getRequest();
            $ad_id   = (int)$request->getParam('id');
            if (empty($ad_id)) {
                return $this->showWarning('对不起，参数不能为空。');
            }

            $ad_info = $this->adService->getAdInfo($ad_id);
            if (empty($ad_info)) {
                return $this->showWarning('无效参数。');
            }
            if ($request->isPost()) {
                $ad_title    = $request->getParam('ad_title');
                $ad_link     = $request->getParam('ad_link');
                $ad_img      = $request->getParam('ad_img');
                $ad_desc     = $request->getParam('ad_desc');
                $ad_switch     = $request->getParam('ad_switch');
                $ad_home     = (int)$request->getParam('ad_home');
                $sort_id     = $request->getParam('sort_id')?:255;
                $time_update = time();

                $param = compact('ad_home', 'sort_id', 'ad_img', 'ad_link', 'ad_title', 'time_update', 'ad_desc', 'ad_switch');
                $re    = $this->adService->editAd($ad_id, $param);
                if ($re) {
                    return $this->showMessage('恭喜您，编辑广告位成功。', '/manage/ad/ad_list');
                } else {
                    return $this->showWarning('对不起，编辑广告位失败。');
                }
            }
            $param     = $ad_info;
            $cate_info = $this->adService->getAdCate();
            $this->view->assign(
                array(
                    'param' => $param,
                    'cate_info' => $cate_info,
                )
            );
            $this->render('ad_info');
        }
    }
