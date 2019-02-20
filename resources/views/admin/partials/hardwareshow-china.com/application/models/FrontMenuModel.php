<?php
/**
 * Created by PhpStorm.
 * User: Chris_Chiang
 * Date: 2018/7/25
 * Time: 16:59
 */
    class FrontMenuModel extends Star_Model_Abstract
    {
        protected $_name = 'front_menu';
        protected $_primary = 'id';

        /*
         * 根据id返回结果
         */
        public function getInfoById($id)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('id =?', $id)
                ->where('status >=?', 0);
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
            $sort_param = array( 'menu_level asc', 'id asc' );

            $select->order($sort_param);
            return $this->fetchAll($select);
        }


        /**
         * @param $page
         * @param $page_size
         * @param string $sort_id_order
         * @param string $id_order
         * @return type
         * 取出所有栏目列表
         */
        public function getAllCate( $sort_id_order='asc',$id_order='desc')
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 0);
            $select->order(array( "sort_id $sort_id_order", "id $id_order" ));
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


        public function getFirstCateCounts()
        {
            $select = $this->select();
            $select->from($this->getTableName(), "COUNT(1)")
                ->where('status >=?', 1)
                ->where("pid = ?", 0);
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


    }
