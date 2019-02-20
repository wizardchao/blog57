<?php

    class ManageService
    {
        protected $cacheKey;
        protected $loginToken = 'e4ac2f047cf82cff2952c228a837cf16';
        protected $dd;


        /*
        * 构造函数
        */
        public function __construct()
        {
            $this->manageAdmin    = new ManageAdminModel();
            $this->manageAdminLog = new ManageAdminLogModel();
            $this->manageMenu     = new ManageMenuModel();
            $this->cacheKey       = Star_Cookie::get('session_id') . 'hardwareshow_huixiang_admin';
            $this->dd             = new Dd();
        }


        /*
        * 登录
        */
        public function loginAdmin($uid, $pwd)
        {
            $getInfo = $this->manageAdmin->getInfoByUsername($uid);
            $where   = array( 'username' => $uid );
            $getIp   = $_SERVER['REMOTE_ADDR'];

            // 用户名不存在
            if (empty($getInfo)) {
                return 501;
                exit;
            }

            $logArr = array(
                'id' => $getInfo['id'],
                'login_tm' => time(),
                'login_ip' => $getIp,
            );

            // 账户异常
            if ($getInfo['status'] == 2) {
                $logArr['fail_msg'] = '账户异常';
                $this->manageAdminLog->insert($logArr);
                return 502;
            }

            // 登录成功
            if (md5($this->loginToken . $pwd . $this->loginToken) == $getInfo['password']) {
                $this->manageAdminLog->insert($logArr);
                $this->manageAdmin->update($where, array(
                    'login_num' => $getInfo['login_num'] + 1,
                    'last_login_time' => time(),
                    'last_login_ip' => $getIp,
                ));

                $uid_str = array(
                    'uid' => $uid,
                    'admin_id' => $getInfo['id'],
                );
                Star_Cache::set($this->cacheKey, $this->dd->arr_json($uid_str));
                return 200;
            } // 密码错误
            else {
                $logArr['fail_msg'] = '密码错误';
                $this->manageAdminLog->insert($logArr);
                return 503;
            }
        }


        /*
        * 检测登录
        */
        public function loginCheck()
        {
            $data = Star_Cache::get($this->cacheKey);
            if (empty($data)) {
                return false;
            } else {
                return $data;
            }
        }


        /*
        * 退出
        */
        public function logout()
        {
            Star_Cache::set($this->cacheKey, '');
        }


        /*
        * 管理员列表
        */
        public function adminList($page, $page_size, $param)
        {
            $total     = $this->manageAdmin->getAllCounts($param);
            $page      = Star_Page::setPage($page, $page_size, $total);
            $list      = $this->manageAdmin->getInfoByPage($page, $page_size, $param);
            $page_info = compact('page', 'page_size', 'total');
            $page_data = Star_Page::show($page_info);
            return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
        }


        /*
        * 添加管理员
        */
        public function adminAdd($param)
        {
            return $this->manageAdmin->insert($param);
        }


        /*
        * 编辑管理员
        */
        public function adminEdit($id, $param)
        {
            $where = array(
                'id' => $id,
            );
            return $this->manageAdmin->update($where, $param);
        }


        /*
        * 删除管理员
        */
        public function adminDel($id)
        {
            $where = array(
                'id' => $id,
            );
            $param = array(
                'status' => -1,
            );
            return $this->manageAdmin->update($where, $param);
        }


        /**
         * 查找管理员
         */
        public function adminFindById($id)
        {
            return $this->manageAdmin->getInfoById($id);
        }


        /**
         * 遍历所有管理员
         */
        public function getAdminInfoByAll($param)
        {
            return $this->manageAdmin->getInfoByAll($param);
        }


        /*
        * 管理菜单列表
        */
        public function menuList()
        {
            $page      = 0;
            $param     = array();
            $page_size = count($this->getInfoByAll($param));
            $menu_info = $this->getParentByPage($page, $page_size, $param);
            foreach ($menu_info['list'] as &$item) {
                if ($item['menu_level'] > 1) {
                    $level             = $item['menu_level'] - 1;
                    $item['menu_name'] = str_repeat('&nbsp;&nbsp;&nbsp;', $level) . "|—" . $item['menu_name'];
                }

                //                switch ($item['menu_level']) {
                //                    case 1:
                //                        $label_class   = '';
                //                        $item['class'] = "fz14 fwb";
                //                        break;
                //                    case 2:
                //                        $label_class   = '<i class="level-label">—</i>';
                //                        $item['class'] = "fz14";
                //                        break;
                //                    default:
                //                        $level         = $item['menu_level'] - 2;
                //                        $item['class'] = '';
                //                        $label_class   = str_repeat('<i class="level-label"></i>', $level) . '<i class="level-label">—</i>';
                //
                //                }
                //                $item['menu_name'] = $label_class . $item['menu_name'];
            }

            return $menu_info['list'];
        }


        /*
        * 管理菜单添加
        */

        /**返回所有菜单信息
         * @param $param
         * @return type
         */
        public function getInfoByAll($param)
        {
            return $this->manageMenu->getInfoByAll($param);
        }


        /*
        * 管理菜单编辑
        */

        public function getParentByPage($page, $page_size, $param)
        {
            $total     = $this->manageMenu->getAllParentCounts($param);
            $page      = Star_Page::setPage($page, $page_size, $total);
            $list      = $this->getAllChildren($page, $page_size, $param);
            $page_info = compact('page', 'page_size', 'total');
            $page_data = Star_Page::show($page_info);
            return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
        }

        public function getAllChildren($page, $page_size, $param)
        {
            $list   = $this->manageMenu->getParentInfoByPage($page, $page_size, $param);
            $re_arr = array();
            foreach ($list as $item) {
                $id                   = $item['id'];
                $child_arr            = $this->manageMenu->getAllChildren($item['id']);
                $menu_arr             = array();
                $new_arr[$item['id']] = $item;
                foreach ($child_arr as $value) {
                    $new_arr[$value['id']] = $value;
                    $menu_arr[]            = explode(',', $value['menu_relation']);
                }

                $sort_arr = UtilsHelper::treeSort($id, $menu_arr);
                foreach ($sort_arr as $key => $value) {
                    $re_arr[$value] = $new_arr[$value];
                }
            }
            unset($list);
            return $re_arr;
        }

        /**
         * @param $page
         * @param $page_size
         * @param $param
         * @return array
         */
        public function checkPage($page, $page_size, $param)
        {
            if (empty($param['menu_id']) && empty($param['menu_name']) && empty($param['pid'])) {
                return $this->getParentByPage($page, 15, $param);
            }
            return $this->getManageInfoByPage($page, $page_size, $param);
        }

        /**
         * 返回分页数据
         *
         * @return array
         */
        public function getManageInfoByPage($page, $page_size, $param)
        {
            $total     = $this->manageMenu->getAllCounts($param);
            $page      = Star_Page::setPage($page, $page_size, $total);
            $list      = $this->manageMenu->getInfoByPage($page, $page_size, $param);
            $page_info = compact('page', 'page_size', 'total');
            $page_data = Star_Page::show($page_info);
            return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
        }

        public function menuAdd($param)
        {
            return $this->manageMenu->insert($param);
        }


        /*
        * 管理菜单编辑
        */

        public function menuEdit($menu_id, $param)
        {
            $where   = array(
                'id' => $menu_id,
            );
            $re      = $this->manageMenu->update($where, $param);
            $info    = $this->getMenuInfoById($menu_id);
            $cur_url = '/manage/' . $info['controller'] . '/' . $info['action'] . '/';
            $cur     = Star_Cache::get($cur_url);
            if ($cur) {
                Star_Cache::delete($cur_url);
            }

            $id = $this->explodeArr($info);
            $this->cleanCache($id);
            unset($id);
            return $re;
        }


        /*
        * 读取管理菜单(framework left)
        */

        /**
         * 根据编号查询菜单信息
         */
        public function getMenuInfoById($menu_id)
        {
            return $this->manageMenu->getInfoById($menu_id);
        }

        public function explodeArr($arr)
        {
            $p_arr = explode(",", $arr['menu_relation']);
            return $p_arr[0];
        }

        public function cleanCache($id)
        {
            $child_cached = "children" . $id;
            $child_arr    = Star_Cache::get($child_cached);
            if ($child_arr) {
                Star_Cache::delete($child_cached);
            }

        }

        public function menuDel($menu_id)
        {
            $menu_info = $this->getMenuInfoById($menu_id);
            $cur_url   = '/manage/' . $menu_info['controller'] . '/' . $menu_info['action'] . '/';
            $cur       = Star_Cache::get($cur_url);
            if ($cur) {
                Star_Cache::delete($cur_url);
            }
            $id = $this->explodeArr($menu_info);
            $this->cleanCache($id);
            unset($id);
            $where = "menu_relation LIKE " . "'%{$menu_id}%'";

            $param = array(
                'status' => -1,
            );
            return $this->manageMenu->update($where, $param);
        }

        public function getMenuByList()
        {
            $getAllList = $this->manageMenu->getInfoByAll(array( 'view' => 1 ));
            $arr        = array();

            foreach ($getAllList as $rs) {
                if ($rs['pid'] == 0) {
                    // 所属子级
                    $rs['child'] = array();
                    foreach ($getAllList as $rb) {
                        if ($rb['pid'] == $rs['id']) {
                            array_push($rs['child'], $rb);
                        }
                    }
                    array_push($arr, $rs);
                }
            }

            return $arr;
        }

        /**
         * 加密
         */
        public function encrytPassword($pwd)
        {
            return md5($this->loginToken . $pwd . $this->loginToken);
        }

        /**
         * 通过controller查找菜单信息
         */
        public function getAdminInfoByController($controller_name, $action_name)
        {
            return $this->manageMenu->getInfoByController($controller_name, $action_name);
        }

        /**
         * 登录日志
         */
        public function adminLog($page, $page_size, $param)
        {
            $total     = $this->manageAdminLog->getAllCounts($param);
            $page      = Star_Page::setPage($page, $page_size, $total);
            $list      = $this->manageAdminLog->getInfoByPage($page, $page_size, $param);
            $page_info = compact('page', 'page_size', 'total');
            $page_data = Star_Page::show($page_info);
            return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
        }

        /**
         * 删除人员有关登录日志
         */
        public function adminlogDel($id)
        {
            $where = array(
                'id' => $id,
            );
            $param = array(
                'status' => -1,
            );
            return $this->manageAdminLog->update($where, $param);
        }

        public function gePadminInfoByController($controller, $action)
        {
            $info  = $this->manageMenu->getPinfoByController($controller, $action);
            $pinfo = ($info) ? $this->getMenuInfoById($info['pid']) : '';
            return array(
                'info' => $info,
                'pinfo' => $pinfo,
            );
        }

        /**
         * 获取父级相同菜单
         */
        public function getplist($pid)
        {
            return $this->manageMenu->getplist($pid);
        }

        /**
         *获取菜单递归
         */
        public function getMenuList()
        {
            $p_info = $this->manageMenu->getplist(0);
            foreach ($p_info as &$p) {
                $child = $this->manageMenu->getplist($p['id']);
                if ($child) {
                    $p['children'] = $child;
                    unset($child);
                }
            }
            return $p_info;
        }

        /**验证管理员名称与编号是否一致
         * @param $username
         * @param $admin_id
         * @return array|int
         */
        public function ck_name($username, $admin_id)
        {
            //获取username相同个数
            $user_counts = $this->getUserCounts(array(
                'username' => $username,
            ));

            switch ($user_counts) {
                case 0:
                    //个数等于0，即没有重复，直接返回0
                    return 0;
                    break;
                case 1:
                    //个数等于1，需要与admin_id一起判断
                    $t_user_counts = $this->getUserCounts(array(
                        'username' => $username,
                        'id' => $admin_id,
                    ));
                    if ($t_user_counts != 1) {
                        return array( 'message' => '人员名称重复！' );
                    }
                    unset($t_user_counts);
                    break;
                default:
                    //个数超过2，即肯定重复
                    return array( 'message' => '人员名称重复！' );
            }
        }

        /**
         * 获取username相同个数
         */
        public function getUserCounts($param)
        {
            return $this->manageAdmin->getUserCounts($param);
        }


        /**
         * 找寻子id
         */
        public function getChild($menu_id, $level)
        {
            return $this->manageMenu->getChild($menu_id, $level);
        }


        public function getSecondMenu($module, $second_id)
        {
            $list             = $this->manageMenu->getSecondMenu($second_id);
            $menu_arr         = array();
            $menu_arr['flag'] = 0;
            foreach ($list as &$item) {
                if ($item['controller'] && $item['action']) {
                    $item['url'] = '/' . $module . '/' . $item['controller'] . '/' . $item['action'];
                }

                $item['child'] = $this->getSecondChild($item['id']);
                if ($item['child']) {
                    $item['url']      = '/' . $module . '/' . $item['child'][0]['controller'] . '/' . $item['child'][0]['action'];
                    $menu_arr['flag'] = 1;
                }
            }
            $menu_arr['list'] = $list;
            return $menu_arr;
        }


        public function getSecondChild($id)
        {
            return $this->manageMenu->getSecondChild($id);
        }


        public function getMenuChild($id)
        {
            $info=$this->manageMenu->getMenuChild($id);
            if(empty($info['controller']) || empty($info['action'])){
              $c_info=$this->manageMenu->getMenuChild($info['id']);
              $info['controller']=$c_info['controller'];
              $info['action']=$c_info['action'];
              unset($c_info);
            }
            return $info;
            // return $this->manageMenu->getMenuChild($id);
        }


        public function getAdminId()
        {
            $data = Star_Cache::get($this->cacheKey);
            return $data;
        }

        public function clearCache()
        {

        }


        public function getAdminInfoById($admin_id)
        {
            return $this->manageAdmin->getInfoById($admin_id);
        }

    }
