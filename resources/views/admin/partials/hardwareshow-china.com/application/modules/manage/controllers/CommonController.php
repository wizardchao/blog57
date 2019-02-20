<?php

    class CommonController extends Star_Controller_Action
    {
        protected $dd;
        protected $manageService;
        protected $authService;
        protected $admin_id;

        public function init()
        {
            $session_key     = 'session_id';
            $session_content = Star_Cookie::get($session_key);
            $this->admin_id  = '';
            if (empty($session_content)) {
                session_start();
                $session_content = session_id();
                Star_Cookie::set($session_key, $session_content);
            }
            unset($session_key);
            unset($session_content);

            $this->manageService = new ManageService();
            $this->authService   = new AdminAuthService();
            $this->dd            = new Dd();
            // 校验登录态
            $re = $this->loginCheck();
            if ($re == 1) {
                echo "权限不足！";
                exit;
            }
            // Set common config
            $this->setCommon();
        }

        /*
        * 校验登录态
        */
        protected function loginCheck()
        {
            $result     = $this->manageService->loginCheck();
            $module     = $this->request->getModuleName();
            $controller = $this->request->getControllerName();
            $action     = $this->request->getActionName();

            if ($module == 'manage' && $controller == 'index' && ($action == 'login' || $action == 'logincheck' || $action == 'logout')) {
                return 0;
            }

            if (empty($result)) {
                header('Location: /manage/index/login');
            }
            $admin_arr      = $this->dd->json_arr($result);
            $this->admin_id = $admin_arr['admin_id'];
            if (empty($this->admin_id)) {
                $this->manageService->logout();
                header('Location: /manage/index/login');
            }
            $auth_list       = $this->authService->getAuthArray($this->admin_id);
            $controller_info = $this->manageService->getAdminInfoByController($controller, $action);


            //设置菜单
            $this->setMenu($module, $controller, $action);
            if ($controller == 'index' && $action == 'index') {
                return 0;
            }

            if (!in_array($controller_info['id'], $auth_list)) {
                return 1;
            }
        }


        /**
         * 设置菜单
         */
        public function setMenu($module, $controller, $action)
        {
            $controller_info = $this->manageService->getAdminInfoByController($controller, $action);
            $info_arr        = explode(",", $controller_info['menu_relation']);
            array_pop($info_arr);
            $clist = array();
            if (count($info_arr)) {
                foreach ($info_arr as $el) {
                    $el_info = $this->manageService->getMenuInfoById($el);
                    $clist[] = array(
                        'menu_name' => $el_info['menu_name'],
                        'link' => ($el_info['controller']) ? "/" . $this->request->getModuleName() . "/" . $el_info['controller'] . "/" . $el_info['action'] : "javascript:;",
                    );
                }
            }

            $this->view->assign(array(
                'clist' => $clist,
            ));
        }


        /*
         * Set common config
         */
        protected function setCommon()
        {
            $getAllList_pre = $this->manageService->getMenuByList(); // Navigation list

            //            if ($this->admin_id) {
            $auth_list  = $this->authService->getAuthArray($this->admin_id);
            $getAllList = array();
            foreach ($getAllList_pre as $el) {
                if (in_array($el['id'], $auth_list)) {
                    $getAllList[] = $el;
                }
            }

            foreach ($getAllList as &$rs) {
                $child = array();
                foreach ($rs['child'] as $el) {
                    if (in_array($el['id'], $auth_list)) {
                        $child[] = $el;
                    }
                }
                $rs['child'] = $child;
            }
            //            }
  // Dd::dump($getAllList);
            foreach ($getAllList as &$item) {

                foreach ($item['child'] as &$child) {

                    if (empty($child['controller']) || empty($child['action'])) {

                        $element = $this->manageService->getMenuChild($child['id']);
                        // Dd::dump($info);
                        // foreach ($info as $element) {
                            if (in_array($element['id'], $auth_list)) {
                                $child['controller'] = $element['controller'];
                                $child['action']     = $element['action'];
                                // break;
                            }
                        // }
                    }
                }
            }

            $module          = $this->request->getModuleName();
            $controller      = $this->request->getControllerName();
            $action          = $this->request->getActionName();
            $controller_info = $this->manageService->getAdminInfoByController($controller, $action);
            $cur_url         = '/' . $module . '/' . $controller . '/' . $action . '/';
            //            Dd::dump($controller_info);
            if ($controller_info['menu_level'] <= 2) {
                $cur = '/' . $module . '/' . $controller . '/' . $action . '/'; // Current location path
            } else {
                $relation_arr = explode(",", $controller_info['menu_relation']);
                $pid          = $relation_arr[1];
                unset($relation_arr);
                $info = $this->manageService->getMenuInfoById($pid);
                if ($info['controller'] && $info['action']) {
                    $cur = '/' . $module . '/' . $info['controller'] . '/' . $info['action'] . '/'; // Current location path
                } else {
                    $cur = '/' . $module . '/' . $controller . '/' . $action . '/'; // Current location path
                }
            }


            $param = array(
                'menu' => $getAllList,
                'menu_cur' => $cur,
                'module' => $module,
            );
            if ($controller_info['menu_level'] >= 2) {
                $secong_menu_arr           = explode(",", ($controller_info['menu_relation']));
                $seond_id                  = $secong_menu_arr[0];
                $param['menu_id']          = $secong_menu_arr[1];
                $param['p_menu_id']        = $secong_menu_arr[2];
                $second_menu_list_pre      = $this->manageService->getSecondMenu($module, $seond_id);
                $second_menu_list_pre_list = $second_menu_list_pre['list'];
                $second_menu_list          = array();

                foreach ($second_menu_list_pre_list as $el) {
                    if (in_array($el['id'], $auth_list)) {
                        $second_menu_list[] = $el;
                    }
                }

                foreach ($second_menu_list as &$val) {
                    $children = array();
                    foreach ($val['child'] as $el) {
                        if (in_array($el['id'], $auth_list)) {
                            $children[] = $el;
                        }
                    }
                    $val['child'] = $children;
                }
                //                Dd::dump($getAllList);

                $param['second_menu'] = $second_menu_list;
                $param['flag']        = $second_menu_list_pre['flag'];
            }

            $param['cur_menu_id'] = $controller_info['id'];
            $param['preview_img'] = DOMAIN_MANAGE . '/img/yulan.jpg';
            $this->view->assign($param);
        }

    }
