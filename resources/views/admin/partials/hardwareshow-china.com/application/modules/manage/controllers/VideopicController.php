<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/13
     * Time: 21:52
     */
    require APPLICATION_PATH . '/modules/manage/controllers/CommonController.php';

    class VideopicController extends CommonController
    {
        protected $page_size;
        protected $vpService;

        public function init()
        {
            parent::init();
            $this->page_size     = 20;
            $this->vpService = new VideoPicService();
        }


        public function pic_listAction()
        {
            $request    = $this->getRequest();
            $page       = (int)$request->getParam('page');
            $param      = array();

            $vp_info = $this->vpService->getVpInfoByPage($page, $this->page_size, $param);
//            $this->dump($vp_info);
            $vp_list = $vp_info['list'];
            $page_info = $vp_info['page'];

            foreach($vp_list as &$item){
                $item['vp_img']=($item['vp_img'])?DOMAIN_IMG.$item['vp_img']:'';
            }

            $this->view->assign(
                array(
                    'param' => $param,
                    'vp_list' => $vp_list,
                    'page' => $page_info,
                ));
            $this->render('pic');
        }


        public function pic_addAction(){
            $request = $this->getRequest();
            if ($request->isPost()) {
                $vp_title  = Star_String::escape($request->getParam('vp_title'));
                $vp_img    = Star_String::escape($request->getParam('pic'));
                $vp_link    = Star_String::escape($request->getParam('vp_link'));
                $sort_id     = Star_String::escape($request->getParam('sort_id'));
                $type     = Star_String::escape($request->getParam('type'));
                $time_create = time();
                $time_update = time();
                $status      = 1;
                if(empty($vp_img)){
                    return $this->showWarning('对不起，图片为空。');
                }
                $param       = compact('vp_title', 'vp_img', 'sort_id', 'time_create', 'time_update', 'status','vp_link','type');

                $re = $this->vpService->addVp($param);
                if ($re) {
                    return $this->showMessage('恭喜您，添加图片成功。', '/manage/videopic/pic_list');
                } else {
                    return $this->showWarning('对不起，添加图片失败。');
                }
            }
            $param = array(
                'sort_id' => 255,
            );
            $this->view->assign(
                array(
                    'param' => $param,
                ));
            $this->render('pic_info');
        }


        public function pic_editAction(){
            $request = $this->getRequest();
            $vp_id = (int)Star_String::escape($request->getParam('pic_id'));

            $vp_info = $this->vpService->getVpInfo($vp_id);
            if (empty($vp_info)) {
                return $this->showWarning('对不起，无效参数。');
            }
            if ($request->isPost()) {
                $vp_title  = Star_String::escape($request->getParam('vp_title'));
                $vp_img    = Star_String::escape($request->getParam('pic'));
                $vp_link    = Star_String::escape($request->getParam('vp_link'));
                $sort_id     = Star_String::escape($request->getParam('sort_id'));
                $type     = Star_String::escape($request->getParam('type'));
                $time_update = time();
                if(empty($vp_img)){
                    return $this->showWarning('对不起，图片为空。');
                }
                $param       = compact('vp_title', 'vp_img', 'sort_id', 'time_update','vp_link','type');

                $re = $this->vpService->editVp($vp_id,$param);
                if ($re) {
                    return $this->showMessage('恭喜您，修改图片成功。', '/manage/videopic/pic_list');
                } else {
                    return $this->showWarning('对不起，修改图片失败。');
                }
            }

            $this->view->assign(
                array(
                    'param' => $vp_info,
                ));
            $this->render('pic_info');
        }


        public function pic_delAction(){
            $request = $this->getRequest();
            $vp_id = (int)Star_String::escape($request->getParam('pic_id'));

            $vp_info = $this->vpService->getVpInfo($vp_id);
            if (empty($vp_info)) {
                return $this->showWarning('对不起，无效参数。');
            }

            $re=$this->vpService->delVp($vp_id);
            if ($re) {
                return $this->showMessage('恭喜您，删除图片成功。', '/manage/videopic/pic_list');
            } else {
                return $this->showWarning('对不起，删除图片失败。');
            }
        }

    }