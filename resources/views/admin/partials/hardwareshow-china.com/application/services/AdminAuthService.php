<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/10/18
     * Time: 9:44
     */

    class AdminAuthService
    {
        protected $adminAuthModel;
        protected $manageService;
        protected $dd;

        /*
        * 构造函数
        */
        public function __construct()
        {
            $this->adminAuthModel = new ManageAdminAuthModel();
            $this->manageService  = new ManageService();
            $this->dd             = new Dd();
        }


        public function addAuth($param)
        {
            return $this->adminAuthModel->insert($param);
        }

        public function delAuth($auth_id)
        {
            $param = array(
                'status' => -1,
            );

            return $this->editAuth($auth_id, $param);
        }

        public function editAuth($auth_id, $param)
        {
            $where = array(
                'auth_id' => $auth_id,
            );
            $re    = $this->adminAuthModel->update($where, $param);
            return ($re) ?: $auth_id;
        }

        public function getAuthArrayByAdminId($admin_id)
        {
            $info      = $this->getAuthInfoByAdminId($admin_id);
            $auth_list = array();
            if ($info) {
                $auth_list = $this->dd->dealExplode($info['menu_ids']);
            }
            return $auth_list;
        }

        public function getAuthInfoByAdminId($admin_id)
        {
            return $this->adminAuthModel->getAuthInfoByAdminId($admin_id);
        }


        public function getAuthId($admin_id)
        {
            $info = $this->getAuthInfoByAdminId($admin_id);
            return $info['auth_id'];
        }


        public function getAuthArray($admin_id)
        {
            $info    = $this->getAuthInfoByAdminId($admin_id);
            $list    = $this->dd->dealExplode($info['menu_ids']);
            $re_list = array();
            foreach ($list as $rs) {
                $info = $this->manageService->getMenuInfoById($rs);
                $arr  = $this->dd->dealExplode($info['menu_relation']);
               $re_list = ($re_list)?array_merge($re_list, $arr):$arr;
            }

            return array_unique($re_list);
        }


    }