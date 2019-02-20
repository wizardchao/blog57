<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/13
     * Time: 23:49
     */

    class BannerModel extends Star_Model_Abstract
    {

        protected $_name = 'banner';

        protected $_primary = 'banner_id';


        /*
* 根据banner_id返回结果
*/
        public function getBannerInfoById($banner_id)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('banner_id =?', $banner_id)
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
        public function getBannerInfoByPage($page, $page_size, Array $param)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1);
            if ($param) {
                if (isset($param['banner_id']) && $param['banner_id']) {
                    $select->where('banner_id =?', $param['banner_id']);
                }

                if (isset($param['banner_type']) && $param['banner_type']) {
                    $select->where('banner_type =?', $param['banner_type']);
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
                if (isset($param['banner_id']) && $param['banner_id']) {
                    $select->where('banner_id =?', $param['banner_id']);
                }
                if (isset($param['banner_type']) && $param['banner_type']) {
                    $select->where('banner_type =?', $param['banner_type']);
                }
            }
            return $this->fetchOne($select);
        }
    }