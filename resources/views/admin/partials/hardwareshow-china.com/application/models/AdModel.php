<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/15
     * Time: 17:19
     */

    class AdModel extends Star_Model_Abstract
    {
        protected $_name = 'ad';
        protected $_primary = 'ad_id';


        /**
         * 通过id获取信息
         * @param $id
         * @return type
         */
        public function getById($id)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('ad_id =?', $id)
                ->where('status >=?', 1);
            return $this->fetchRow($select);
        }


        /**
         * 返回有关信息
         *
         * @param type $page
         * @param type $page_size
         * @param type $param
         * @return type
         */
        public function getList($page, $page_size, array $param)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1);
            if (isset($param['ad_key']) && $param['ad_key']) {
                $select->where('ad_key =?', $param['ad_key']);
            }


            if (isset($param['ad_home']) && $param['ad_home']) {
                $select->where('ad_home =?', $param['ad_home']);
            } elseif (isset($param['ad_switch']) && $param['ad_switch']) {
                $select->where('ad_switch =?', $param['ad_switch']);
            }
            $select->limitPage($page, $page_size)->order(array( 'sort_id desc' ));
            return $this->fetchAll($select);
        }


        /**
         * 返回所有信息
         */
        public function getAll($param)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1);
            if (isset($param['ad_home']) && $param['ad_home']) {
                $select->where('ad_home =?', $param['ad_home']);
            } elseif (isset($param['ad_switch']) && $param['ad_switch']) {
                $select->where('ad_switch =?', $param['ad_switch']);
            }

            $select->order("ad_id DESC");
            return $this->fetchAll($select);
        }


        /*
        * 取出总数
         */
        public function getAllCounts($param = null)
        {
            $select = $this->select();
            $select->from($this->getTableName(), "COUNT(1)")
                ->where('status >=?', 1);
            if (isset($param['ad_switch']) && $param['ad_switch']) {
                $select->where('ad_switch =?', $param['ad_switch']);
            }

            if (isset($param['ad_key']) && $param['ad_key']) {
                $select->where('ad_key =?', $param['ad_key']);
            }
            return $this->fetchOne($select);
        }
    }
