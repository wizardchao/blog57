<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/6/5
     * Time: 11:10
     */
    require APPLICATION_PATH . '/modules/manage/controllers/CommonController.php';
    class BrochureController extends CommonController
    {
        protected $brochureService;

        public function init()
        {
            parent::init();
            $this->brochureService = new BrochureService();
        }


        /*
         * 媒体列表页
         */
        public function brochure_listAction()
        {
            $request   = $this->getRequest();
            $page      = (int)$request->getParam('page');
            $page_size = 20; //每页显示数
            $param     = array();
            if ($request->isGet()) {
                $brochure_title = Star_String::escape($request->getParam('brochure_title'));
                $brochure_id    = Star_String::escape($request->getParam('brochure_id'));
                $is_recommend   = Star_String::escape($request->getParam('is_recommend'));
                $is_up          = Star_String::escape($request->getParam('is_up'));
                $home_show      = Star_String::escape($request->getParam('home_show'));
                $category_id    = Star_String::escape($request->getParam('category_id'));
                $param          = array(
                    'category_id' => $category_id,
                    'brochure_title' => $brochure_title,
                    'brochure_id' => $brochure_id,
                    'is_recommend' => $is_recommend,
                    'is_up' => $is_up,
                    'home_show' => $home_show,
                );

            }

            $parent_info   = $this->brochureService->getParentInfo();
            $brochure_info = $this->brochureService->getbrochureInfoByPage($page, $page_size, $param);
            $brochure_list = $brochure_info['list'];
            foreach ($brochure_list as &$val){
                $result             = ($val['category_id']) ? $this->brochureService->getCategoryById($val['category_id']) : 0;
                $val[ 'category_name' ] = ($result) ? $result['category_name'] : " ";
                $val['brochure_img']=DOMAIN_IMG. $val['brochure_img'];
            }
//            array_walk($brochure_list, function (&$val, $key, $param) {
//                $brochureService    = new brochureService();
//                $result             = ($val['category_id']) ? $this->brochureService->getCategoryById($val['category_id']) : 0;
//                $val[$param['key']] = ($result) ? $result['category_name'] : " ";
//                $val['brochure_img']=DOMAIN_IMG. $val['brochure_img'];
//            }, array( 'key' => 'category_name' ));

            $page_info = $brochure_info['page'];
            $this->view->assign('parent_info', $parent_info);
            $category_info=$this->brochureService->getAllCateInfo();
            $this->view->assign(
                array(
                    'category_info' => $category_info,
                    'param' => $param,
                    'brochure_list' => $brochure_list,
                    'page' => $page_info,
                ));
            $this->render('list');
        }


        /*
         * 添加媒体
         */
        public function brochure_addAction()
        {
            $parent_info     = $this->brochureService->getParentInfo();
            $request         = $this->getRequest();
            if ($request->isPost()) {
                $brochure_title   = Star_String::escape($request->getParam('brochure_title'));
                $brochure_content = stripslashes(Star_String::escape($request->getParam('content')));
                $is_recommend     = (int)Star_String::escape($request->getParam('is_recommend'));
                $home_show        = (int)Star_String::escape($request->getParam('home_show'));
                $sort_id         = Star_String::escape($request->getParam('sort_id'));
                $img_url          = Star_String::escape($request->getParam('pic'));
                $category_id      = Star_String::escape($request->getParam('category_id'));
                $link      = Star_String::escape($request->getParam('link'));
                if (empty($img_url)) {
                    return $this->showWarning('图片不能为空！');
                }

                $param           = array(
                    'link' => $link,
                    'category_id' => ($category_id) ? $category_id : 0,
                    'brochure_title' => $brochure_title,
                    'brochure_content' => $brochure_content,
                    'is_recommend' => $is_recommend,
                    'home_show' => $home_show,
                    'brochure_img' => $img_url,
                    'sort_id' => ($sort_id) ? $sort_id : 255,
                    'time_create' => time(),
                    'time_update' => time(),
                );
                $this->brochureService = new brochureService();
                $brochure_info   = $this->brochureService->insertbrochure($param);
                if ($brochure_info) {
                    return $this->showMessage('恭喜您，添加媒体成功。', '/manage/brochure/brochure_list');
                } else {
                    return $this->showWarning('对不起，添加媒体失败。');
                }
            }

            $param = array(
                "sort_id" => 255,
            );
            $category_info=$this->brochureService->getAllCateInfo();
            $this->view->assign('parent_info', $parent_info);
            $this->view->assign(
                array(
                    'category_info' => $category_info,
                    'param' => $param,
                ));
            $this->render('info');
        }


        /*
         * 编辑媒体
         */
        public function brochure_editAction()
        {
            $this->brochureService = new brochureService();
            $parent_info     = $this->brochureService->getParentInfo();
            $request         = $this->getRequest();
            $brochure_id     = (int)$request->getParam('brochure_id');
            if ($request->isPost()) {
                $brochure_title   = Star_String::escape($request->getParam('brochure_title'));
                $brochure_content = stripslashes(Star_String::escape($request->getParam('content')));
                $is_recommend     = (int)Star_String::escape($request->getParam('is_recommend'));
                $home_show        = (int)Star_String::escape($request->getParam('home_show'));
                $sort_id         = Star_String::escape($request->getParam('sort_id'));
                $category_id      = Star_String::escape($request->getParam('category_id'));
                $img_url          = Star_String::escape($request->getParam('pic'));
                $link      = Star_String::escape($request->getParam('link'));
                if (empty($img_url)) {
                    return $this->showWarning('图片不能为空！');
                }

                $param = array(
                    'link' => $link,
                    'category_id' => ($category_id) ? $category_id : 0,
                    'brochure_title' => $brochure_title,
                    'brochure_content' => $brochure_content,
                    'is_recommend' => $is_recommend,
                    'home_show' => $home_show,
                    'brochure_img' => $img_url,
                    'sort_id' => ($sort_id) ? $sort_id : 255,
                    'time_create' => time(),
                    'time_update' => time(),
                );
                $arr   = array(
                    'brochure_id' => $brochure_id,
                );

                $brochure_update_info = $this->brochureService->updatebrochure($arr, $param);
                if (isset($brochure_update_info) && is_numeric($brochure_update_info)) {
                    return $this->showMessage('恭喜您,修改媒体成功。', '/manage/brochure/brochure_list');
                } else {
                    return $this->showWarning('对不起，修改媒体失败。');
                }
            }

            $brochure_info = $this->brochureService->getbrochureInfoById($brochure_id);
            $this->view->assign('parent_info', $parent_info);

            $category_info=$this->brochureService->getAllCateInfo();
            $this->view->assign(
                array(
                    'category_info' => $category_info,
                    'param' => $brochure_info,
                ));
            $this->render('info');
        }


        /*
         * 删除
         */
        public function brochure_delAction()
        {
            $request    = $this->getRequest();
            $brochure_id          = (int)$request->getParam('brochure_id');
            $this->brochureService      = new brochureService();
            $arr                  = array(
                'brochure_id' => $brochure_id,
            );
            $brochure_update_info = $this->brochureService->delbrochure($arr);
            if ($brochure_update_info) {
                return $this->showMessage('恭喜您，删除成功。', '/manage/brochure/brochure_list');
            } else {
                return $this->showWarning('很遗憾，删除失败。', '/manage/brochure/brochure_list');
            }
        }


        /**
         * 分类列表
         */
        public function cate_listAction()
        {
            $this->brochureService = new brochureService();
            $request         = $this->getRequest();
            $page            = (int)$request->getParam('page');
            //        $category_name = Star_String::escape($request->getParam('category_name'));
            $page_size     = 20;
            $params        = array(//            'category_name' => $category_name,
            );
            $category_data = $this->brochureService->getCategoryByPage($page, $page_size, $params);
            foreach($category_data['category_list'] as &$val){
                $this->brochureService    = new brochureService();
                $val['parent_name'] = ($val['parent_id']) ? $this->brochureService->getInfo($val['parent_id']) : "一级分类";
            }
//            array_walk($category_data['category_list'], function (&$val, $key, $param) {
//                $this->brochureService    = new brochureService();
//                $val[$param['key']] = ($val['parent_id']) ? $this->brochureService->getInfo($val['parent_id']) : "一级分类";
//            }, array( 'key' => 'parent_name' ));

            $this->view->assign($category_data);
            $this->view->assign($params);
            $this->render('cate_list');
        }


        /**
         * 添加分类
         */
        public function cate_addAction()
        {
            $parent_info     = $this->brochureService->getParentInfo();

            $request = $this->getRequest();
            if ($request->isPost()) {
                $category_name = Star_String::escape($request->getParam('category_name'));
                $category_key  = Star_String::escape($request->getParam('category_key'));
                $number        = (int)$request->getParam('number');
                $sort_id          = (int)$request->getParam('sort_id');
                $is_show       = (int)$request->getParam('is_show');
                $parent_id     = (int)$request->getParam('parent_id');
                if (empty($category_name)) {
                    return $this->showWarning('分类名称不能为空');
                }

                $category_data = array(
                    'category_name' => $category_name,
                    'category_key' => $category_key,
                    'parent_id' => $parent_id,
                    'number' => $number,
                    'sort_id' => $sort_id,
                    'is_show' => $is_show,
                    'time_create' => time(),
                    'time_update' => time(),
                );

                $category_id = $this->brochureService->insertCategory($category_data);
                if ($category_id) {
                    return $this->showMessage('恭喜您，添加成功','/manage/brochure/cate_list');
                } else {
                    return $this->showWarning('很遗憾，添加失败');
                }
            }
            $this->view->assign('parent_info', $parent_info);
            $this->view->assign('category', array());
            $this->render('cate_info');
        }


        public function cate_editAction()
        {
            $parent_info     = $this->brochureService->getParentInfo();

            $request     = $this->getRequest();
            $category_id = (int)$request->getParam('category_id');
            $category    = $this->brochureService->getCategoryById($category_id);

            if (empty ($category)) {
                return $this->showWarning('分类不存在');
            }

            if ($request->isPost()) {
                $category_name = Star_String::escape($request->getParam('category_name'));
                $category_key  = Star_String::escape($request->getParam('category_key'));
                $number        = (int)$request->getParam('number');
                $sort_id          = (int)$request->getParam('sort_id');
                $is_show       = (int)$request->getParam('is_show');
                $parent_id     = (int)$request->getParam('parent_id');

                if ($parent_id == $category_id) {
                    return $this->showWarning('父类编号与子类编号不能相同！');
                }

                if (empty($category_name)) {
                    return $this->showWarning('分类名称不能为空');
                }

                $category_data = array(
                    'category_name' => $category_name,
                    'category_key' => $category_key,
                    'parent_id' => $parent_id,
                    'number' => $number,
                    'sort_id' => $sort_id,
                    'is_show' => $is_show,
                    'time_update' => time(),
                );
                $rs            = $this->brochureService->updateCategory($category_id, $category_data);
                if ($rs) {
                    return $this->showMessage('恭喜你，编辑成功','/manage/brochure/cate_list');
                } else {
                    return $this->showWarning('很遗憾，编辑失败');
                }
            }

            $this->view->assign('parent_info', $parent_info);
            $this->view->assign('category', $category);
            $this->render('cate_info');
        }


        public function cate_delAction()
        {
            $request         = $this->getRequest();
            $category_id     = (int)$request->getParam('category_id');
            $category = $this->brochureService->getCategoryById($category_id);
            if (empty($category)) {
                return $this->showWarning('分类不存在');
            }

            $rs = $this->brochureService->deleteCategory($category_id);
            if ($rs) {
                return $this->showMessage('删除成功','/manage/brochure/cate_list');
            } else {
                return $this->showWarning('删除失败');
            }
        }

    }
