<?php

    class ManageAdminModel extends Star_Model_Abstract
    {
        protected $_name = 'manage_admin';
        protected $_primary = 'id';


        /*
         * 根据id返回结果
         */
        public function getInfoById($id)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('id =?', $id)
                ->where('status >=?', 1);
            return $this->fetchRow($select);
        }


        /*
         * 根据username返回结果
         */
        public function getInfoByUsername($username)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('username =?', $username)
                ->where('status >=?', 1);
            return $this->fetchRow($select);
        }


        /**
         * 返回有关信息
         *
         * @param type $page
         * @param type $page_size
         * @return type
         */
        public function getInfoByPage($page, $page_size, Array $param)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1);
            if ($param) {
                if (isset($param['id']) && $param['id']) {
                    $select->where('id = ?', $param['id']);
                }
                if (isset($param['username']) && $param['username']) {
                    $select->where('username LIKE ?', '%' . $param['username'] . '%');
                }
            }
            $select->order(array( 'sort_id asc', 'id desc' ))->limitPage($page, $page_size);
            return $this->fetchAll($select);
        }


        /**
         * 取出总数
         */
        public function getAllCounts($param = NULL)
        {
            $select = $this->select();
            $select->from($this->getTableName(), "COUNT(1)")
                ->where('status >=?', 1);
            if ($param) {
                if (isset($param['id']) && $param['id']) {
                    $select->where('id = ?', $param['id']);
                }
                if (isset($param['username']) && $param['username']) {
                    $select->where('username LIKE ?', '%' . $param['username'] . '%');
                }
            }
            return $this->fetchOne($select);
        }


        /**获取所有记录
         * @param array $param
         * @return type
         */
        public function getInfoByAll(Array $param)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status =?', 1);
            $select->order(array( 'sort_id asc', 'id desc' ));
            return $this->fetchAll($select);
        }


        /**
         * 获取username相同个数
         */
        public function getUserCounts($param)
        {
            $select = $this->select();
            $select->from($this->getTableName(), "COUNT(1)")
                ->where('status >=?', 1);
            if ($param) {
                if (isset($param['id']) && $param['id']) {
                    $select->where('id = ?', $param['id']);
                }
                if (isset($param['username']) && $param['username']) {
                    $select->where('username =?', $param['username']);
                }
            }
            return $this->fetchOne($select);
        }





    }