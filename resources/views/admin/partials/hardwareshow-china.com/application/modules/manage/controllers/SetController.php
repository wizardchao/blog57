<?php
    require APPLICATION_PATH . '/modules/manage/controllers/CommonController.php';

    class SetController extends CommonController
    {
        protected $page_size;
        protected $manageConfigService;

        public function init()
        {
            parent::init();
            $this->page_size           = 20;
            $this->manageService       = new ManageService();
            $this->manageConfigService = new ManageConfigService();
        }


        /**
         * 菜单设置
         */
        public function menu_listAction()
        {
            $request   = $this->getRequest();
            $page      = (int)$request->getParam('page');
            $menu_id   = $request->getParam('menu_id');
            $menu_name = $request->getParam('menu_name');
            $pid       = (int)$request->getParam('pid');
            $param     = array(
                'menu_id' => $menu_id,
                'menu_name' => $menu_name,
                'pid' => $pid,
            );

            $menu_info = $this->manageService->checkPage($page, $this->page_size, $param);
            $menu_list = $menu_info['list'];
            $page      = $menu_info['page'];

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
                unset($label_class);
            }
            $data = array(
                'menu_list' => $menu_list,
                'page' => $page,
                'param' => $param,
            );
            $this->view->assign($data);
            $this->render('menu');
        }


        /**
         * 添加菜单
         */
        public function menu_addAction()
        {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $pid            = (int)$request->getParam('pid');
                $menu_name      = trim(Star_String::escape($request->getParam('menu_name')));
                $menu_classname = trim(Star_String::escape($request->getParam('menu_classname')));
                $controller     = trim(Star_String::escape($request->getParam('controller')));
                $action         = trim(Star_String::escape($request->getParam('action')));
                $view           = (int)(Star_String::escape($request->getParam('view')));
                $sort_id        = (int)(Star_String::escape($request->getParam('sort_id')));
                if (empty($menu_name)) {
                    return $this->showWarning('菜单名不能为空！');
                }

                if (!in_array($view, array( 0, 1 ))) {
                    return $this->showWarning('可见类型错误错误！');
                }

                $param = array(
                    'pid' => $pid,
                    'menu_name' => $menu_name,
                    'menu_classname' => $menu_classname,
                    'controller' => $controller,
                    'action' => $action,
                    'view' => $view,
                    'sort_id' => ($sort_id) ? $sort_id : 2555,
                    'status' => 1,
                );

                $re = $this->manageService->menuAdd($param);

                if ($re) {
                    if ($pid) {
                        $pmenu_info    = $this->manageService->getMenuInfoById($pid);
                        $menu_relation = $pmenu_info['menu_relation'] . ',' . $re;
                        $arr_count     = explode(',', $menu_relation);
                        $level         = count($arr_count);
                        //                        $this->manageService->cleanCache();
                    } else {
                        $menu_relation = $re;
                        $level         = 1;
                    }
                    $re          = $this->manageService->menuEdit($re, array(
                        'menu_relation' => $menu_relation,
                        'menu_level' => $level,
                    ));
                    $treeService = new TreeService();
                    //                    $tree_info   = $treeService->tree();
                    if ($re) {
                        $p_ar = explode(",", $menu_relation);
                        $this->manageService->cleanCache($p_ar[0]);
                        unset($p_ar);
                        return $this->showMessage('恭喜您，添加菜单成功。', '/manage/set/menu_list');
                    }
                } else {
                    return $this->showWarning('对不起，添加菜单失败。');
                }
            }
            $param = array(
                //                'sort_id' => 255,
                'view' => 0,
            );

            $plist = $this->manageService->menuList();

            $this->view->assign(
                array(
                    'param' => $param,
                    'plist' => $plist,
                )
            );
            $this->render('menu_info');
        }


        /**
         * 编辑菜单
         */
        public function menu_editAction()
        {
            $request = $this->getRequest();
            $menu_id = (int)$request->getParam('menu_id');
            if (empty($menu_id)) {
                return $this->showWarning('菜单编号不能为空！');
            }

            $menu_info = $this->manageService->getMenuInfoById($menu_id);
            if (empty($menu_info)) {
                return $this->showWarning('菜单信息有误！');
            }
            if ($request->isPost()) {
                $pid            = (int)$request->getParam('pid');
                $menu_name      = trim(Star_String::escape($request->getParam('menu_name')));
                $menu_classname = trim(Star_String::escape($request->getParam('menu_classname')));
                $controller     = trim(Star_String::escape($request->getParam('controller')));
                $action         = trim(Star_String::escape($request->getParam('action')));
                $view           = (int)(Star_String::escape($request->getParam('view')));
                $sort_id        = (int)(Star_String::escape($request->getParam('sort_id')));

                if (empty($menu_name)) {
                    return $this->showWarning('菜单名不能为空！');
                }

                if (!in_array($view, array( 0, 1 ))) {
                    return $this->showWarning('可见类型错误错误！');
                }

                $param = array(
                    'pid' => $pid,
                    'menu_name' => $menu_name,
                    'menu_classname' => $menu_classname,
                    'controller' => $controller,
                    'action' => $action,
                    'view' => $view,
                    'sort_id' => ($sort_id) ? $sort_id : 2555,
                    'status' => 1,
                );


                if ($pid != $menu_info['pid']) {
                    $p_relation = '';
                    $p_level    = 0;
                    if ($pid) {
                        $p_menu_info = $this->manageService->getMenuInfoById($pid);
                        $p_relation  = $p_menu_info['menu_relation'];
                        $p_level     = $p_menu_info['menu_level'];
                        $p_arr       = explode(',', $p_relation);
                        if (in_array($menu_id, $p_arr)) {
                            return $this->showWarning('对不起，上级编号不能为父级编号。');
                        }
                    }
                    $child_arr = $this->manageService->getChild($menu_id, $menu_info['menu_level']);

                    $level = $menu_info['menu_level'];
                    foreach ($child_arr as $item) {
                        if ($item['menu_level'] > $level) {
                            if ($pid) {
                                $arr_relation = $p_relation . "," . strstr($item['menu_relation'], "$menu_id");
                            } else {
                                $arr_relation = $p_relation . strstr($item['menu_relation'], "$menu_id");
                            }

                            $child_param = array(
                                'menu_level' => $p_level + 2,
                                'menu_relation' => trim($arr_relation),
                            );
                            $this->manageService->menuEdit($item['id'], $child_param);
                        }

                    }

                    if ($p_relation) {
                        $param['menu_relation'] = trim($p_relation . "," . $menu_info['id']);
                    } else {
                        $param['menu_relation'] = trim($menu_info['id']);
                    }

                    $param['menu_level'] = $p_level + 1;

                }

                $re = $this->manageService->menuEdit($menu_id, $param);
                if (isset($re)) {
                    if (isset($p_level)) {
                        $treeService = new TreeService();
                        $tree_info   = $treeService->tree();
                        if ($tree_info) {
                            return $this->showMessage('恭喜您，编辑菜单成功。', '/manage/set/menu_list');
                        } else {
                            return $this->showWarning('对不起，编辑菜单失败。');
                        }
                    }
                    return $this->showMessage('恭喜您，编辑菜单成功。', '/manage/set/menu_list');

                } else {
                    return $this->showWarning('对不起，编辑菜单失败。');
                }
            }


            $plist = $this->manageService->menuList();

            $this->view->assign(
                array(
                    'param' => $menu_info,
                    'plist' => $plist,
                )
            );
            $this->render('menu_info');
        }


        /**
         * 删除菜单
         */
        public function menu_delAction()
        {
            $request = $this->getRequest();
            $menu_id = (int)$request->getParam('menu_id');
            if (empty($menu_id)) {
                return $this->showWarning('菜单编号不能为空！');
            }

            $menu_info = $this->manageService->getMenuInfoById($menu_id);
            if (empty($menu_info)) {
                return $this->showWarning('菜单信息有误！');
            }

            $rs = $this->manageService->menuDel($menu_id);
            if ($rs) {
                return $this->showMessage('恭喜您，删除成功。', '/manage/set/menu_list');
            } else {
                return $this->showWarning('很遗憾，删除失败。');
            }

        }


        public function indexAction()
        {
            $this->redirect('/manage/config/header_config');exit;
            $config_id = 1;
            $request   = $this->getRequest();
            if ($request->isPost()) {
                $logo        = Star_String::escape($request->getParam('logo'));
                $slogan      = Star_String::escape($request->getParam('slogan'));
                $home_desc   = Star_String::escape($request->getParam('content'));
                $home_data_count   = Star_String::escape($request->getParam('home_data_count'));
                $home_activity_count = Star_String::escape($request->getParam('home_activity_count'));
                $home_news_count = Star_String::escape($request->getParam('home_news_count'));
                $copyright   = Star_String::escape($request->getParam('copyright'));
                $home_ad_link   = Star_String::escape($request->getParam('home_ad_link'));
                $home_ad_img   = Star_String::escape($request->getParam('pic'));
                $home_ad_title   = Star_String::escape($request->getParam('home_ad_title'));
                $home_title   = Star_String::escape($request->getParam('home_title'));
                $home_keywords   = Star_String::escape($request->getParam('home_keywords'));
                $home_description   = Star_String::escape($request->getParam('home_description'));
                $common_title  = Star_String::escape($request->getParam('common_title'));
                $common_keywords   = Star_String::escape($request->getParam('common_keywords'));
                $common_description   = Star_String::escape($request->getParam('common_description'));
                $header_quick_title   = Star_String::escape($request->getParam('header_quick_title'));
                $header_quick_link   = Star_String::escape($request->getParam('header_quick_link'));
                $time_update = time();
                $param       = compact('header_quick_link','header_quick_title','logo', 'slogan', 'home_desc', 'time_update', 'home_activity_count','home_ad_title','copyright','contact','home_data_count','home_ad_img','home_ad_link','home_news_count','copyright','home_description','home_title','home_keywords','common_keywords','common_title','common_description');

               $rs          = $this->manageConfigService->editConfigInfo($config_id, $param);
                if (isset($rs)) {
                    return $this->showMessage('恭喜您，修改配置成功。', '/manage/set/');
                } else {
                    return $this->showWarning('很遗憾，修改失败。');
                }
            }
            $config_info = $this->manageConfigService->getConfig($config_id);

            $this->view->assign(
                array(
                    'param' => $config_info,
                )
            );
            $this->render('index');
        }


        public function home_editAction()
        {
            $request = $this->getRequest();
            $id     = (int)$request->getParam('id');
            $config_id=1;
            $config_info=$this->manageConfigService->getConfigInfo($config_id);
            if ($request->isPost()) {

                $content=$request->getParam('content');
                switch($id){
                    case 1:
                        $param=array(
                            'partner' => $content,
                        );

                        break;
                    case 2:
                        $param=array(
                            'contact' => $content,
                        );
                        break;
                    case 3:
                        $param=array(
                            'company' => $content,
                        );
                        break;
                    case 4 :
                        $param=array(
                            'contact_way' => $content,
                        );
                }


                $rs=$this->manageConfigService->editConfigInfo($config_id, $param);
                if (isset($rs)) {
                    return $this->showWarning('修改成功！');
                } else {
                    return $this->showWarning('很遗憾，修改失败。');
                }
            }
            switch($id){
                case 1:
                    $param=array(
                        'title' => '合作伙伴',
                        'desc' => $config_info['partner'],
                    );

                    break;
                case 2:
                    $param=array(
                        'title' => '联系我们',
                        'desc' => $config_info['contact'],
                    );
                    break;
                case 3:
                    $param=array(
                        'title' => '主办单位',
                        'desc' => $config_info['company'],
                    );
                    break;
                case 4:
                    $param=array(
                        'title' => '侧边栏联系方式',
                        'desc' => $config_info['contact_way'],
                    );
                    break;
            }
            $this->view->assign(
                array(
                    'param' => $param,
                )
            );
            $this->render('home_set');

        }


        public function home_setAction(){
            $config_id = 1;
            $request   = $this->getRequest();
            if ($request->isPost()) {
                $home_desc   = stripslashes(Star_String::escape($request->getParam('content')));
                $home_data_count   = Star_String::escape($request->getParam('home_data_count'));
                $home_activity_count = Star_String::escape($request->getParam('home_activity_count'));
                $home_news_count = Star_String::escape($request->getParam('home_news_count'));
                $home_ad_link   = Star_String::escape($request->getParam('home_ad_link'));
                $home_ad_img   = Star_String::escape($request->getParam('pic'));
                $home_ad_title   = Star_String::escape($request->getParam('home_ad_title'));
                $home_page_video_link   = Star_String::escape($request->getParam('home_page_video_link'));
                $home_page_video_img   = Star_String::escape($request->getParam('home_page_video_img'));
                $home_page_video_title   = Star_String::escape($request->getParam('home_page_video_title'));
                $home_page_video_desc   = Star_String::escape($request->getParam('home_page_video_desc'));
                $home_page_banner_title1  = Star_String::escape($request->getParam('home_page_banner_title1'));
                $home_page_banner_title2  = Star_String::escape($request->getParam('home_page_banner_title2'));
                $home_page_banner_link1  = Star_String::escape($request->getParam('home_page_banner_link1'));
                $home_page_banner_link2  = Star_String::escape($request->getParam('home_page_banner_link2'));
                $countdown_time=strtotime(Star_String::escape($request->getParam('countdown_time')));
                $countdown_show=(int)Star_String::escape($request->getParam('countdown_show'));
                $miss_show=(int)Star_String::escape($request->getParam('miss_show'));
                $countdown_bg=Star_String::escape($request->getParam('countdown_bg'));
                $countdown_title=Star_String::escape($request->getParam('countdown_title'));
                $home_page_banner_link1_bg=Star_String::escape($request->getParam('home_page_banner_link1_bg'));
                $home_page_banner_link2_bg=Star_String::escape($request->getParam('home_page_banner_link2_bg'));




                $time_update = time();
                $param=compact('home_page_banner_link1_bg','home_page_banner_link2_bg','miss_show','home_page_video_title','home_page_video_desc','countdown_bg','countdown_show','countdown_title','countdown_time','home_page_banner_link1','home_page_banner_link2','home_page_banner_title1','home_page_banner_title2','home_page_video_img','home_desc','home_data_count','home_activity_count','home_news_count','home_ad_link','home_ad_img','home_ad_title','home_page_video_link','time_update');

                $param['miss_title']=Star_String::escape($request->getParam('miss_title'));
                $param['miss_bg']=Star_String::escape($request->getParam('miss_bg'));
                $param['header_quick_video_link']=Star_String::escape($request->getParam('header_quick_video_link'));
                $param['header_quick_video_title']=Star_String::escape($request->getParam('header_quick_video_title'));
                $param['header_quick_pic_title']=Star_String::escape($request->getParam('header_quick_pic_title'));
                $param['header_quick_pic_link']=Star_String::escape($request->getParam('header_quick_pic_link'));
                $param['pic_bg']=Star_String::escape($request->getParam('pic_bg'));
                $param['pic_title']=Star_String::escape($request->getParam('pic_title'));
                $param['header_quick_pic_title']=Star_String::escape($request->getParam('header_quick_pic_title'));
                $param['header_quick_pic_link']=Star_String::escape($request->getParam('header_quick_pic_link'));
                $param['activity_title']=Star_String::escape($request->getParam('activity_title'));
                $param['activity_bg']=Star_String::escape($request->getParam('activity_bg'));
                $param['header_quick_activity_link']=Star_String::escape($request->getParam('header_quick_activity_link'));
                $param['header_quick_activity_title']=Star_String::escape($request->getParam('header_quick_activity_title'));
                $param['home_page_activity_desc']=Star_String::escape($request->getParam('home_page_activity_desc'));
                $param['news_title']=Star_String::escape($request->getParam('news_title'));
                $param['news_bg']=Star_String::escape($request->getParam('news_bg'));
                $param['header_quick_news_link']=Star_String::escape($request->getParam('header_quick_news_link'));
                $param['header_quick_news_title']=Star_String::escape($request->getParam('header_quick_news_title'));
                $param['home_page_news_desc']=Star_String::escape($request->getParam('home_page_news_desc'));
                $param['exhibitor_bg']=Star_String::escape($request->getParam('exhibitor_bg'));
                $param['exhibitor_title']=Star_String::escape($request->getParam('exhibitor_title'));
                $param['header_quick_exhibitor_title']=Star_String::escape($request->getParam('header_quick_exhibitor_title'));
                $param['header_quick_exhibitor_link']=Star_String::escape($request->getParam('header_quick_exhibitor_link'));
                $rs          = $this->manageConfigService->editConfigInfo($config_id, $param);
                if (isset($rs)) {
                    echo "<script>alert('恭喜您，修改配置成功!');history.back();</script>";
                    exit;
                } else {
                    return $this->showWarning('很遗憾，修改失败。');
                }
            }
            $config_info = $this->manageConfigService->getConfig($config_id);
            $config_info['countdown_time']=$config_info['countdown_time']?date('Y/m/d H:i',$config_info['countdown_time']):date('Y/m/d H:i',time());
            $this->view->assign(
                array(
                    'param' => $config_info,
                )
            );
            $this->render('home_page_set');
        }

    }
