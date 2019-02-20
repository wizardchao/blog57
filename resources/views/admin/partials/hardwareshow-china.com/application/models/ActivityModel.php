<?php

    /*
    * author: chason
    * date: 2018-08-13
    */

    class ActivityModel extends Star_Model_Abstract
    {

        protected $_name = 'activity';
        protected $_primary = 'activity_id';


        /**
         * 通过id获取信息
         * @param $about_id
         * @return type
         */
        public function getById($id)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('activity_id =?', $id)
                ->where('status >=?', 1);
            return $this->fetchRow($select);
        }


        /**
         * 返回有关信息
         *
         * @param type $page
         * @param type $page_size
         * @param type $params
         * @return type
         */
        public function getList($page, $page_size, Array $params)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1);

            if (isset($params['activity_id']) && $params['activity_id']) {
                $select->where('activity_id =?', $params['activity_id']);
            }
            if (isset($params['activity_category_id']) && $params['activity_category_id']) {
                $select->where('activity_category_id =?', $params['activity_category_id']);
            }
            if (isset($params['title']) && $params['title']) {
                $select->where('title like ?', '%' . $params['title'] . '%');
            }

            if (isset($params['is_home']) && $params['is_home']) {
                $select->where('is_home =?', $params['is_home']);
            }
            $select->limitPage($page, $page_size)->order(array( 'sort_id asc', 'activity_id DESC' ));

            return $this->fetchAll($select);
        }


        /**
         * 返回所有信息
         */
        public function getAll()
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1)
                ->order("sort_id asc", "activity_id DESC");
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
            if (isset($param['activity_category_id']) && $param['activity_category_id']) {
                $select->where('activity_category_id =?', $param['activity_category_id']);
            }

            if (isset($param['is_home']) && $param['is_home']) {
                $select->where('is_home =?', $param['is_home']);
            }
            return $this->fetchOne($select);
        }


        public function getChild($activity_category_id)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1)
                ->where('activity_category_id =?', $activity_category_id)
                ->order("sort_id asc", "activity_id DESC");
            return $this->fetchAll($select);
        }


        public function getActivityByTemplate($template_name)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('template_name =?', $template_name)
                ->where('status >=?', 1);
            return $this->fetchRow($select);
        }


    }
