<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/14
     * Time: 10:00
     */

    class globalFoodModel   extends Star_Model_Abstract
    {
        protected $_name = 'globalfood';

        protected $_primary = 'globalfood_id';

        /*
         * 根据globalfood_id返回结果
        */
        public function getGlobalFoodInfoById($globalfood_id)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('globalfood_id =?', $globalfood_id)
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
        public function getGlobalFoodInfoByPage($page, $page_size, Array $param)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1);
            if ($param) {
                if (isset($param['globalfood_id']) && $param['globalfood_id']) {
                    $select->where('globalfood_id =?', $param['globalfood_id']);
                }

            }
            $select->limitPage($page, $page_size)->order(array('sort_id ASC', 'time_update desc'));
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
                if (isset($param['globalfood_id']) && $param['globalfood_id']) {
                    $select->where('globalfood_id =?', $param['globalfood_id']);
                }
            }
            return $this->fetchOne($select);
        }


    }