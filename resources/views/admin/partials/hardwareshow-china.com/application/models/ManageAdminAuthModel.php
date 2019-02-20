<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/10/10
     * Time: 10:57
     */

    class ManageAdminAuthModel extends Star_Model_Abstract
    {
        protected $_name = 'manage_admin_auth';
        protected $_primary = 'auth_id';


        public function getAuthInfoByAdminId($admin_id)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1)
                ->where('admin_id =?', $admin_id)
                ->limit(1);
            return $this->fetchRow($select);
        }


    }