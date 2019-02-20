<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/13
     * Time: 21:45
     */

    class VideoPicModel  extends Star_Model_Abstract
    {
        protected $_name = 'video_pic';
        protected $_primary = 'vp_id';


        /*
         * 根据vp_id返回结果
         */
        public function getVpInfoById($vp_id)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('vp_id =?', $vp_id)
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
        public function getVpInfoByPage($page, $page_size, Array $param)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1);
            if ($param) {
                if (isset($param['vp_id']) && $param['vp_id']) {
                    $select->where('vp_id =?', $param['vp_id']);
                }

                if (isset($param['vp_title']) && $param['vp_title']) {
                    $select->where('vp_title like ?', '%'.$param['vp_title'].'%');
                }

                if (isset($param['type']) && $param['type']) {
                    $select->where('type =?', $param['type']);
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
                if (isset($param['vp_id']) && $param['vp_id']) {
                    $select->where('vp_id =?', $param['vp_id']);
                }

                if (isset($param['vp_title']) && $param['vp_title']) {
                    $select->where('vp_title like ?', '%'.$param['vp_title'].'%');
                }

                if (isset($param['type']) && $param['type']) {
                    $select->where('type =?', $param['type']);
                }
            }
            return $this->fetchOne($select);
        }
    }