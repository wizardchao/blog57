<?php

    class ManageMenuModel extends Star_Model_Abstract
    {
        protected $_name = 'manage_menu';
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


        /**
         * 取出所有记录，分页
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
                if (isset($param['menu_id']) && $param['menu_id']) {
                    $select->where('id =?', $param['menu_id']);
                }
                if (isset($param['pid']) && $param['pid']) {
                    $select->where('pid =?', $param['pid']);
                }
                if (isset($param['menu_name']) && $param['menu_name']) {
                    $select->where('menu_name LIKE ?', '%' . $param['menu_name'] . '%');
                }
                if (isset($param['view']) && $param['view']) {
                    $select->where('view =?', $param['view']);
                }
            }
            $select->order(array( 'sort_id asc', 'id desc' ))->limitPage($page, $page_size);
            return $this->fetchAll($select);
        }


        /**
         * 取出所有记录
         *
         * @param type $page
         * @param type $page_size
         * @return type
         */
        public function getInfoByAll(Array $param)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1);
            if ($param) {
                if (isset($param['menu_id']) && $param['menu_id']) {
                    $select->where('id =?', $param['menu_id']);
                }
                if (isset($param['pid']) && $param['pid']) {
                    $select->where('pid =?', $param['pid']);
                }
                if (isset($param['menu_name']) && $param['menu_name']) {
                    $select->where('menu_name LIKE ?', '%' . $param['menu_name'] . '%');
                }
                if (isset($param['view']) && $param['view']) {
                    $select->where('view =?', $param['view']);
                }
            }
            $sort_param = array( 'menu_level desc', 'sort_id asc' );
            if ($param['sort_id'] && ($param['sort_id'] == 1)) {
                unset($sort_param);
                $sort_param = array( 'menu_level desc', 'id desc' );
            }

            $select->order($sort_param);
            return $this->fetchAll($select);
        }


        /*
        * 取出总数
        */
        public function getAllCounts($param = NULL)
        {
            $select = $this->select();
            $select->from($this->getTableName(), "COUNT(1)")
                ->where('status >=?', 1);
            if ($param) {
                if (isset($param['menu_id']) && $param['menu_id']) {
                    $select->where('id =?', $param['menu_id']);
                }
                if (isset($param['pid']) && $param['pid']) {
                    $select->where('pid =?', $param['pid']);
                }
                if (isset($param['menu_name']) && $param['menu_name']) {
                    $select->where('menu_name LIKE ?', '%' . $param['menu_name'] . '%');
                }
                if (isset($param['view']) && $param['view']) {
                    $select->where('view =?', $param['view']);
                }
            }
            return $this->fetchOne($select);
        }


        /**
         * 根据controller查找相同信息
         */
        public function getInfoByController($controller_name, $action_name)
        {
            $select = $this->select();
            $select->from($this->getTableName() . " AS m")
                ->where('m.controller =?', $controller_name)
                ->where('m.action =?', $action_name)
                ->where('m.status >=?', 1);
            // ->joinInner($this->getTableName("manage_menu") . " AS d", "m.pid = d.id and d.pid > 0");
            return $this->fetchRow($select);
        }


        public function getPinfoByController($controller_name, $action_name)
        {
            $select = $this->select();
            $select->from($this->getTableName() . " AS m")
                ->where('m.controller =?', $controller_name)
                ->where('m.action =?', $action_name)
                ->where('m.status >=?', 1)
                ->joinInner($this->getTableName("manage_menu") . " AS d", "m.pid = d.id and d.pid = 0");

            return $this->fetchRow($select);
        }


        public function getplist($pid)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('pid =?', $pid)
                ->where('status >=?', 1);
            return $this->fetchAll($select);
        }


        public function getAllParam($param)
        {
            $select = $this->select();
            $select->from($this->getTableName(), $param['menu_relation'])
                ->where('status >=?', 1);
            return $this->fetchAll($select);
        }


        public function getChild($menu_id, $level)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('menu_relation like ?', '%' . $menu_id . '%')
                ->where('menu_level >?', $level)
                ->where('status >=?', 1);
            return $this->fetchAll($select);
        }

        public function getAllParentCounts($param)
        {
            $select = $this->select();
            $select->from($this->getTableName(), "COUNT(1)")
                ->where('pid =?', 0)
                ->where('status >=?', 1);
            return $this->fetchOne($select);
        }


        public function getParentInfoByPage($page, $page_size, $param)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('pid =?', 0)
                ->where('status >=?', 1)
                ->order(array( 'sort_id asc', 'id desc' ))->limitPage($page, $page_size);
            return $this->fetchAll($select);
        }


        public function getAllChildren($pid)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('menu_relation like ?', '%' . $pid . '%')
                ->where('pid > ?', 0)
                ->where('menu_level >?', 1)
                ->where('status >=?', 1)
                ->order(array( 'menu_level asc', 'sort_id desc' ));
            return $this->fetchAll($select);
        }


        public function getSecondMenu($pid)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('pid = ?', $pid)
                ->where('menu_level = ?', 2)
                ->where('status >=?', 1)
                ->order(array( 'sort_id asc', 'id desc' ));
            return $this->fetchAll($select);
        }


        public function getSecondChild($id)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('pid = ?', $id)
                ->where('view= ?', 1)
                ->where('status >=?', 1)
                ->order(array( 'sort_id asc' ));
            return $this->fetchAll($select);
        }


        public function getMenuChild($id)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('pid =?', $id)
                ->where('view =?', 1)
                ->where('status >=?', 1)
                ->order(array( 'sort_id asc' ))
                ->limit(1);
            return $this->fetchRow($select);
        }


    }
