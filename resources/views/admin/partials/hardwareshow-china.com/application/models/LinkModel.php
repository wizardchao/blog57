<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/13
     * Time: 23:00
     */

    class LinkModel extends Star_Model_Abstract
    {
        protected $_name = 'link';
        protected $_primary = 'link_id';

        /*
         * 根据id返回结果
         */
        public function getLinkInfoById($id)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('link_id =?', $id)
                ->where('status >=?', 0);
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
        public function getLinkInfoByPage($page, $page_size, Array $param)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1);
           if(isset($param['link_category_id']) && $param['link_category_id']){
             $select->where('link_category_id =?',$param['link_category_id']);
           }
           if(isset($param['link_position_id']) && $param['link_position_id']){
             $select->where('link_position_id =?',$param['link_position_id']);
           }
            $select->limitPage($page, $page_size)->order(array( 'sort_id ASC' ));
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
                if(isset($param['link_category_id']) && $param['link_category_id']){
                  $select->where('link_category_id =?',$param['link_category_id']);
                }
                if(isset($param['link_position_id']) && $param['link_position_id']){
                  $select->where('link_position_id =?',$param['link_position_id']);
                }

            return $this->fetchOne($select);
        }


        public function getAllLink()
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1)
                ->order(array( 'sort_id asc' ));
            return $this->fetchAll($select);
        }


        public function getLinkByCate($cate_id){
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('link_category_id =?',$cate_id)
                ->where('status >=?', 1)
                ->order(array( 'sort_id asc' ));
            return $this->fetchAll($select);
        }
    }
