<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/13
     * Time: 20:20
     */
    require APPLICATION_PATH . '/modules/manage/controllers/CommonController.php';

    class DatadownloadController extends CommonController
    {
        protected $dataService;

        public function init()
        {
            parent::init();
            $this->dataService = new DataDownloadService();
        }


        /*
         * 下载列表页
         */
        public function indexAction()
        {
            $request    = $this->getRequest();
            $page       = (int)$request->getParam('page');
            $page_size  = 20; //每页显示数
            $data_title = trim(Star_String::escape($request->getParam('data_title')));
            $param      = array(
                'data_title' => $data_title,
            );

            $data_info = $this->dataService->getDataInfoByPage($page, $page_size, $param);
            $data_list = $data_info['list'];
            $page_info = $data_info['page'];

            foreach ($data_list as &$value) {
                $value['data_img'] = ($value['data_img']) ? DOMAIN_IMG . $value['data_img'] : '';
            }

            $this->view->assign(
                array(
                    'param' => $param,
                    'data_list' => $data_list,
                    'page' => $page_info,
                ));
            $this->render('index');
        }


        public function data_addAction()
        {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $data_title  = Star_String::escape($request->getParam('data_title'));
                $data_img    = Star_String::escape($request->getParam('pic'));
                $data_link   = Star_String::escape($request->getParam('data_link'));
                $data_format = Star_String::escape($request->getParam('data_format'));
                $data_source = Star_String::escape($request->getParam('data_source'));
                $sort_id     = Star_String::escape($request->getParam('sort_id'));
                $time_create = time();
                $time_update = time();
                $status      = 1;
                $param       = compact('data_title', 'data_img', 'data_format', 'data_link', 'data_source', 'sort_id', 'time_create', 'time_update', 'status');

                if(empty($data_title)){
                    return $this->showWarning('对不起，资料名称为空。');
                }
                $re = $this->dataService->addData($param);
                if ($re) {
                    return $this->showMessage('恭喜您，添加资料成功。', '/manage/datadownload/');
                } else {
                    return $this->showWarning('对不起，添加资料失败。');
                }
            }
            $param = array(
                'sort_id' => 255,
            );
            $this->view->assign(
                array(
                    'param' => $param,
                ));
            $this->render('info');
        }


        public function data_delAction()
        {
            $request = $this->getRequest();
            $data_id = (int)Star_String::escape($request->getParam('data_id'));
            if (empty($data_id)) {
                return $this->showWarning('对不起，资料参数为空。');
            }

            $data_info = $this->dataService->getDataInfo($data_id);
            if (empty($data_info)) {
                return $this->showWarning('对不起，无效参数。');
            }

            $re = $this->dataService->delData($data_id);
            if ($re) {
                return $this->showMessage('恭喜您，删除资料成功。', '/manage/datadownload/');
            } else {
                return $this->showWarning('对不起，删除资料失败。');
            }
        }


        public function data_editAction()
        {
            $request = $this->getRequest();
            $data_id = (int)Star_String::escape($request->getParam('data_id'));
            if (empty($data_id)) {
                return $this->showWarning('对不起，资料参数为空。');
            }

            $data_info = $this->dataService->getDataInfo($data_id);
            if (empty($data_info)) {
                return $this->showWarning('对不起，无效参数。');
            }

            if ($request->isPost()) {
                $data_title  = Star_String::escape($request->getParam('data_title'));
                $data_img    = Star_String::escape($request->getParam('pic'));
                $data_link   = Star_String::escape($request->getParam('data_link'));
                $data_format = Star_String::escape($request->getParam('data_format'));
                $data_source = Star_String::escape($request->getParam('data_source'));
                $sort_id     = Star_String::escape($request->getParam('sort_id'));
                $time_update = time();
                $param       = compact('data_title', 'data_img', 'data_format', 'data_link', 'data_source', 'sort_id', 'time_update');
                if(empty($data_title)){
                    return $this->showWarning('对不起，资料名称为空。');
                }
                $re = $this->dataService->editData($data_id, $param);
                if ($re) {
                    return $this->showMessage('恭喜您，编辑资料成功。', '/manage/datadownload/');
                } else {
                    return $this->showWarning('对不起，编辑资料失败。');
                }
            }

            $this->view->assign(
                array(
                    'param' => $data_info,
                ));
            $this->render('info');


        }

    }