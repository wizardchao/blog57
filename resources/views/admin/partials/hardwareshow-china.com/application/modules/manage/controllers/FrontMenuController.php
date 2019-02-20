<?php
    require APPLICATION_PATH . '/modules/manage/controllers/CommonController.php';

    class FrontMenuController extends CommonController
    {
        protected $page_size;

        public function init()
        {
            parent::init();
            $this->page_size     = 3;
            $this->FrontMenuService = new FrontMenuService();
        }




        /**
         *  获取菜单列表，默认获取3个一级菜单和其所有的子菜单，根据ID排倒序，sort_id 排正序
         * @param string $sort_id_order
         * @param string $id_order
         *
         */
        public function menu_listsAction()
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
            $page_size=$this->page_size;
            $menu_list=$this->FrontMenuService->getFirstCateByPage($page, $page_size);
            $this->FrontMenuService->addIcon($menu_list['list']);
            $data = array(
                'menu_list' => $menu_list['list'],
                'page' => $menu_list['page'],
                'param' => $param,
                'current_page'=>$page
            );

            $this->view->assign($data);
            $this->render('menu');

        }


        /**
         * 搜索菜单
         */
        public function search_menuAction(){
            $request   = $this->getRequest();
            $menu_id   = $request->getParam('menu_id');
            $menu_name = $request->getParam('menu_name');
            $param     = array(
                'menu_id' => $menu_id,
                'menu_name' => $menu_name,
            );
            if(empty($param['menu_id'])&&empty($param['menu_name']))
            {
                $data = array(
                    'menu_list' =>array(),
                    'page' => '',
                    'param' => array(),
                    'current_page'=>'',
                );
                $this->view->assign($data);
                return $this->render('search_menu');
            }else{
                $res=$this->FrontMenuService->search_menu($param);
                $data = array(
                    'menu_list' =>$res,
                    'page' => '',
                    'param' => $param,
                    'current_page'=>'',
                );
                $this->view->assign($data);
                return $this->render('search_menu');
            }

        }


        /**
         * @return type
         * 菜单进行排序
         */
        public function menu_orderAction(){
            $request = $this->getRequest();
            $res=$request->getParams();
            foreach($res as $k=>$v){
                $me=explode('_',$k);
                $id=$me[2];
                foreach($res as $ke=>$ve){
                    $m=explode('_',$ke);
                    if($m[0]=='sort'&&$m[2]==$id){
                        $param=array('id'=>$id,'sort_id'=>$ve);
                        $this->FrontMenuService->updateMenuListOrder($param);
                    }
                }

            }
            $gourl = $_SERVER['HTTP_REFERER'];
            return $this->showMessage('排序已经成功修改。', $gourl);
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
                    //                    'sort_id' => ($sort_id) ? $sort_id : 255,
                    'status' => 1,
                );

                $re = $this->FrontMenuService->menuAdd($param);

                if ($re) {
                    if ($pid) {
                        $pmenu_info    = $this->FrontMenuService->getMenuInfoById($pid);
                        $menu_relation = $pmenu_info['menu_relation'] . ',' . $re;
                        $arr_count     = explode(',', $menu_relation);
                        $level         = count($arr_count);
                    } else {
                        $menu_relation = $re;
                        $level         = 1;
                    }
                    $re          = $this->FrontMenuService->menuEdit($re, array(
                        'menu_relation' => $menu_relation,
                        'menu_level' => $level,
                    ));
//                    $treeService = new TreeService();
//                    $tree_info   = $treeService->tree();
                    if ($re) {
                        return $this->showMessage('恭喜您，添加菜单成功。', '/manage/frontmenu/menu_lists');
                    }
                } else {
                    return $this->showWarning('对不起，添加菜单失败。');
                }
            }
            $param = array(
                //                'sort_id' => 255,
                'view' => 0,
            );

            $plist = $this->FrontMenuService->menuLists();

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
            $page=($request->getParam('page'));
            $menu_id = (int)$request->getParam('menu_id');
            if (empty($menu_id)) {
                return $this->showWarning('菜单编号不能为空！');
            }

            $menu_info = $this->FrontMenuService->getMenuInfoById($menu_id);
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
                //                $sort_id        = (int)(Star_String::escape($request->getParam('sort_id')));

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
                    //                    'sort_id' => ($sort_id) ? $sort_id : 255,
                    'status' => 1,
                );

                if ($pid != $menu_info['pid']) {
                    $p_relation = '';
                    $p_level    = 0;
                    if ($pid) {
                        $p_menu_info = $this->FrontMenuService->getMenuInfoById($pid);
                        $p_relation  = $p_menu_info['menu_relation'];
                        $p_level     = $p_menu_info['menu_level'];
                        $p_arr       = explode(',', $p_relation);
                        if (in_array($menu_id, $p_arr)) {
                            return $this->showWarning('对不起，上级编号不能为父级编号。');
                        }
                    }
                    $child_arr = $this->FrontMenuService->getChild($menu_id, $menu_info['menu_level']);

                    $level = $menu_info['menu_level'];
                    foreach ($child_arr as $item) {
                        if ($item['menu_level'] > $level) {
                            if ($pid) {
                                $arr_relation =$p_relation. "," . strstr($item['menu_relation'], "$menu_id");
                            } else {
                                $arr_relation =$p_relation.strstr($item['menu_relation'], "$menu_id");
                            }


                            $child_param = array(
                                'menu_level' => $p_level + 2,
                                'menu_relation' => trim($arr_relation),
                            );
                            $this->FrontMenuService->menuEdit($item['id'], $child_param);
                        }

                    }

                    $param['menu_relation'] = trim($p_relation . "," . $menu_info['id']);
                    $param['menu_level']    = $p_level + 1;

                }


                $re = $this->FrontMenuService->menuEdit($menu_id, $param);
                if (isset($re)) {
//                    if($pid){
//                        $cur_index        = '/' . $this->request->getModuleName() . '/' . $controller . '/' . $action . '/'; // Current location path
//                        $relation_arr=explode(",", $param['menu_relation'] );
//                        $pid=$relation_arr[1];
//                        unset($relation_arr);
//                        $info=$this->manageService->getMenuInfoById($pid);
//                        $cur        = '/' . $this->request->getModuleName() . '/' . $info['controller'] . '/' . $info['action'] . '/'; // Current location path
//                        Star_Cache::set($cur_index, $cur);
//                    }

//                    if (isset($p_level)) {
//                        $treeService = new TreeService();
//                        $tree_info   = $treeService->tree();
//                        if ($tree_info) {
//                            return $this->showMessage('恭喜您，编辑菜单成功。', '/manage/set/menu_lists');
//                        } else {
//                            return $this->showWarning('对不起，编辑菜单失败。');
//                        }
//                    }
                    return $this->showMessage('恭喜您，编辑菜单成功。','/manage/frontmenu/menu_lists?page='.$page);


                } else {
                    return $this->showWarning('对不起，编辑菜单失败。');
                }
            }


            $plist = $this->FrontMenuService->menuLists();

            //print_r($menu_info);exit;

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

            $menu_info = $this->FrontMenuService->getMenuInfoById($menu_id);
            //print_r($menu_info);exit;
            if (empty($menu_info)) {
                return $this->showWarning('菜单信息有误！');
            }

            $rs = $this->FrontMenuService->menuDel($menu_id);
            if ($rs) {
                return $this->showMessage('恭喜您，删除成功。', '/manage/frontmenu/menu_lists');
            } else {
                return $this->showWarning('很遗憾，删除失败。');
            }

        }


        public function searchMenu(){

        }


    }
