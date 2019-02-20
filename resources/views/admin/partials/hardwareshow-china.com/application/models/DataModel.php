<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/13
     * Time: 20:14
     */

    class DataModel  extends Star_Model_Abstract
    {
        protected $_name = 'data';

        protected $_primary = 'data_id';


        /*
   * 根据data_id返回结果
   */
        public function getDataInfoById($data_id)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('data_id =?', $data_id)
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
        public function getDataInfoByPage($page, $page_size, Array $param)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1);
            if ($param) {
                if (isset($param['data_id']) && $param['data_id']) {
                    $select->where('data_id =?', $param['data_id']);
                }

                if (isset($param['data_title']) && $param['data_title']) {
                    $select->where('data_title like ?', '%'.$param['data_title'].'%');
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
                if (isset($param['data_id']) && $param['data_id']) {
                    $select->where('data_id =?', $param['data_id']);
                }

                if (isset($param['data_title']) && $param['data_title']) {
                    $select->where('data_title like ?', '%'.$param['data_title'].'%');
                }
            }
            return $this->fetchOne($select);
        }
    }