<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/15
     * Time: 9:42
     */

    class VisitorModel extends Star_Model_Abstract
    {
        protected $_name = 'visitor';
        protected $_primary = 'visitor_id';


        public function getVisitorInfoById($visitor_id)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('visitor_id =?', $visitor_id)
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
        public function getVisitorInfoByPage($page, $page_size, Array $param)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1);
            $select->limitPage($page, $page_size)->order(array( 'sort_id ASC', 'time_update desc' ));
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
            return $this->fetchOne($select);
        }

    }