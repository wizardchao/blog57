<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/6/1
     * Time: 9:44
     */
    require APPLICATION_PATH . '/modules/manage/controllers/CommonController.php';

    class AdminController extends CommonController
    {
        protected $page_size;
        protected $manageService;
        protected $adminAuthService;
        protected $Dd;

        public function init()
        {
            parent::init();
            $this->page_size        = 20;
            $this->manageService    = new ManageService();
            $this->adminAuthService = new AdminAuthService();
            $this->Dd               = new Dd();
        }


        /**
         * 管理人员列表
         */
        public function admin_listAction()
        {
            $request    = $this->getRequest();
            $page       = (int)$request->getParam('page');
            $id         = Star_String::escape($request->getParam('id'));
            $username   = trim(Star_String::escape($request->getParam('username')));
            $param      = array(
                'id' => $id,
                'username' => $username,
            );
            $admin_info = $this->manageService->adminList($page, $this->page_size, $param);
            $admin_list = $admin_info['list'];
            $page       = $admin_info['page'];
            $data       = array(
                'admin_list' => $admin_list,
                'page' => $page,
                'param' => $param,
            );

            $this->view->assign($data);
            $this->render('list');
        }


        /**
         * 添加管理人员
         */
        public function admin_addAction()
        {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $username  = trim(Star_String::escape($request->getParam('username')));
                $password  = trim(Star_String::escape($request->getParam('password')));
                $sort_id   = (int)Star_String::escape($request->getParam('sort_id'));
                $all_check = (int)Star_String::escape($request->getParam('all_check'));
                $sheet_on = (int)Star_String::escape($request->getParam('sheet_on'));
                $auth      = $request->getParam('auth');
                if (empty($auth)) {
                    return $this->showWarning('请添加权限！');
                }

                if (empty($username)) {
                    return $this->showWarning('人员名称不能为空！');
                }

                if (empty($password)) {
                    return $this->showWarning('密码不能为空！');
                }

                $user_counts = $this->manageService->getUserCounts(array(
                    'username' => $username,
                ));
                if ($user_counts >= 1) {
                    return $this->showWarning('人员名称重复！');
                }
                unset($user_counts);

                $param = array(
                    'username' => $username,
                    'password' => $this->manageService->encrytPassword($password),
                    'tm_update' => time(),
                    'tm_create' => time(),
                    'login_num' => 0,
                    'sort_id' => ($sort_id) ? $sort_id : 255,
                    'status' => 1,
                    'sheet_on' => $sheet_on,
                );

                $re = $this->manageService->adminAdd($param);
                if ($re) {
                    $auth_param = array(
                        'time_create' => time(),
                        'time_update' => time(),
                        'admin_id' => $re,
                        'menu_ids' => $this->Dd->dealImplode($auth),
                        'status' => 1,
                        'all_check' => $all_check,
                    );
                    $auth_re    = $this->adminAuthService->addAuth($auth_param);
                    if ($auth_re) {
                        return $this->showMessage('恭喜您，添加人员成功。', '/manage/admin/admin_list');
                    }
                } else {
                    return $this->showWarning('对不起，添加人员失败。');
                }
            }

            $page      = 0;
            $page_size = 100;
            $menu_info = $this->manageService->checkPage($page, $page_size, array());
            $menu_list = $menu_info['list'];

            $child_list  = array();
            $child_id_list  = array();
            $parent_list = array();
            foreach ($menu_list as $el) {
                $arr             = $this->Dd->dealExplode($el['menu_relation']);
                array_pop($arr);
                $parent_list_pre = ($parent_list) ? array_merge($parent_list, $arr) : $arr;
                $parent_list     = array_unique($parent_list_pre);
            }

            foreach ($menu_list as $el) {
                if (!in_array($el['id'], $parent_list)) {
                    $child_list[] = $el;
                    $child_id_list[]=$el['id'];
                }
            }

            $line_arr=array();
            foreach($child_list as $el){
                $line_arr[$el['pid']][]=$el;
            }

            $re_menu_list=array();
            foreach ($menu_list as &$list) {
                $list['link'] = ($list['controller'] && $list['action']) ? '/' . $this->request->getModuleName() . '/' . $list['controller'] . '/' . $list['action'] : '';
                switch ($list['menu_level']) {
                    case 1:
                        $label_class   = '';
                        $list['class'] = "fz14 fwb";
                        break;
                    case 2:
                        $label_class   = '<i class="level-label">—</i>';
                        $list['class'] = "fz14";
                        break;
                    default:
                        $level         = $list['menu_level'] - 2;
                        $list['class'] = '';
                        $label_class   = str_repeat('<i class="level-label"></i>', $level) . '<i class="level-label">—</i>';

                }
                $list['menu_name'] = $label_class . $list['menu_name'];
                if(!in_array($list['id'],$child_id_list)) {
                    $re_menu_list[$list['id']]=$list;
                    if($line_arr[$list['id']]){
                        $re_menu_list[$list['id']]['line']=$line_arr[$list['id']];
                    }
                }


                unset($list);
                unset($label_class);
            }
            $this->view->assign(
                array(
                    'menu_list' => $re_menu_list,
                    'auth_list' => array(),
                    'param' => array(
                        'sort_id' => 255,
                    ),
                )
            );
            $this->render('info');
        }


        /**
         * 编辑管理人员
         */
        public function admin_editAction()
        {
            $request  = $this->getRequest();
            $admin_id = (int)$request->getParam('id');
            if (empty($admin_id)) {
                return $this->showWarning('人员编号不能为空！');
            }

            $admin_info = $this->manageService->adminFindById($admin_id);
            if (empty($admin_info)) {
                return $this->showWarning('人员信息有误！');
            }
            if ($request->isPost()) {
                $username  = trim(Star_String::escape($request->getParam('username')));
                $password  = trim(Star_String::escape($request->getParam('password')));
                $sort_id   = (int)Star_String::escape($request->getParam('sort_id'));
                $sheet_on = (int)Star_String::escape($request->getParam('sheet_on'));
                $auth      = $request->getParam('auth');
                $all_check = (int)Star_String::escape($request->getParam('all_check'));
                if (empty($username)) {
                    return $this->showWarning('人员名不能为空！');
                }

                //验证用户名唯一性
                $user_ck = $this->manageService->ck_name($username, $admin_id);
                if (is_array($user_ck) && $user_ck['message']) {
                    return $this->showWarning($user_ck['message']);
                }
                unset($user_ck);

                //拼装数组
                $param = array(
                    'username' => $username,
                    'tm_update' => time(),
                    'sort_id' => ($sort_id) ? $sort_id : 255,
                    'status' => 1,
                    'sheet_on' => $sheet_on,
                );

                //有密码则进行加密
                if ($password) {
                    $param['password'] = $this->manageService->encrytPassword($password);
                }

                $re = $this->manageService->adminEdit($admin_id, $param);
                if (isset($re)) {
                    $auth_id    = $this->adminAuthService->getAuthId($admin_id);
                    $auth_param = array(
                        'menu_ids' => $this->Dd->dealImplode($auth),
                        'time_update' => time(),
                        'all_check' => $all_check,
                    );
                    if ($auth_id) {
                        $auth_re = $this->adminAuthService->editAuth($auth_id, $auth_param);
                    } else {
                        $auth_param['admin_id']    = $admin_id;
                        $auth_param['time_create'] = time();
                        $auth_re                   = $this->adminAuthService->addAuth($auth_param);
                    }

                    if ($auth_re) {
                        return $this->showMessage('恭喜您，编辑人员成功。', '/manage/admin/admin_list');
                    }

                } else {
                    return $this->showWarning('对不起，编辑人员失败。');
                }
            }
            $page      = 0;
            $page_size = 100;
            $menu_info = $this->manageService->checkPage($page, $page_size, array());
            $menu_list = $menu_info['list'];

            $child_list  = array();
            $child_id_list  = array();
            $parent_list = array();
            foreach ($menu_list as $el) {
                $arr             = $this->Dd->dealExplode($el['menu_relation']);
                array_pop($arr);
                $parent_list_pre = ($parent_list) ? array_merge($parent_list, $arr) : $arr;
                $parent_list     = array_unique($parent_list_pre);
            }

            foreach ($menu_list as $el) {
                if (!in_array($el['id'], $parent_list)) {
                    $child_list[] = $el;
                    $child_id_list[]=$el['id'];
                }
            }

            $line_arr=array();
            foreach($child_list as $el){
                $line_arr[$el['pid']][]=$el;
            }

            $re_menu_list=array();
            foreach ($menu_list as &$list) {
                $list['link'] = ($list['controller'] && $list['action']) ? '/' . $this->request->getModuleName() . '/' . $list['controller'] . '/' . $list['action'] : '';
                switch ($list['menu_level']) {
                    case 1:
                        $label_class   = '';
                        $list['class'] = "fz14 fwb";
                        break;
                    case 2:
                        $label_class   = '<i class="level-label">—</i>';
                        $list['class'] = "fz14";
                        break;
                    default:
                        $level         = $list['menu_level'] - 2;
                        $list['class'] = '';
                        $label_class   = str_repeat('<i class="level-label"></i>', $level) . '<i class="level-label">—</i>';

                }
                $list['menu_name'] = $label_class . $list['menu_name'];
                if(!in_array($list['id'],$child_id_list)) {
                    $re_menu_list[$list['id']]=$list;
                    if($line_arr[$list['id']]){
                        $re_menu_list[$list['id']]['line']=$line_arr[$list['id']];
                    }
                }


                unset($list);
                unset($label_class);
            }

            $auth_list = $this->adminAuthService->getAuthArrayByAdminId($admin_id);
            $auth_info = $this->adminAuthService->getAuthInfoByAdminId($admin_id);
            //            $admin_info['all_check'] = $auth_info['all_check'];
            $this->view->assign(
                array(
                    'auth_list' => $auth_list,
                    'menu_list' => $re_menu_list,
                    'param' => $admin_info,
                )
            );
            $this->render('info');
        }


        /**
         * 删除管理人员
         */
        public function admin_delAction()
        {
            $request  = $this->getRequest();
            $admin_id = (int)$request->getParam('id');
            if (empty($admin_id)) {
                return $this->showWarning('人员编号不能为空！');
            }

            $admin_info = $this->manageService->adminFindById($admin_id);
            if (empty($admin_info)) {
                return $this->showWarning('人员信息有误！');
            }

            $rs = $this->manageService->adminDel($admin_id);

            //删除日志
            $log_rs = $this->manageService->adminlogDel($admin_id);
            if ($rs && isset($log_rs)) {
                return $this->showMessage('恭喜您，删除成功。', '/manage/admin/admin_list');
            } else {
                return $this->showWarning('很遗憾，删除失败。');
            }
        }


        /**
         * 登录日志
         */
        public function admin_logAction()
        {
            $request  = $this->getRequest();
            $page     = (int)$request->getParam('page');
            $id       = Star_String::escape($request->getParam('id'));
            $param    = array(
                'id' => $id,
            );
            $log_info = $this->manageService->adminLog($page, $this->page_size, $param);
            $log_list = $log_info['list'];
            $page     = $log_info['page'];

            $user_list = $this->manageService->getAdminInfoByAll(array());
            $data      = array(
                'user_list' => $user_list,
                'log_list' => $log_list,
                'page' => $page,
                'param' => $param,
            );

            $this->view->assign($data);
            $this->render('log_list');

        }
    }