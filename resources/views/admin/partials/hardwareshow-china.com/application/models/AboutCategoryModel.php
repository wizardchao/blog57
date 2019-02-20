<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/2/27
     * Time: 13:01
     */

    class AboutCategoryModel extends Star_Model_Abstract
    {
        protected $_name = 'about_category';

        protected $_primary = 'about_category_id';

        public function getAboutCateInfoById($about_categoryid)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('about_category_id =?', $about_categoryid)
                ->where('status >=?', 1);
            return $this->fetchRow($select);
        }


        public function getAboutAll()
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1)
                ->order("sort_id asc");
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
            if (isset($param['about_category_id']) && $param['about_category_id']) {
                $select->where('about_category_id =?', $param['about_category_id']);
            }
            if (isset($param['about_category_title']) && $param['about_category_title']) {
                $select->where('about_category_title like ?', "%" . $param['about_category_title'] . "%");
            }
            return $this->fetchOne($select);
        }


        /**
         * 返回有关信息
         *
         * @param type $page
         * @param type $page_size
         * @param type $params
         * @return type
         */
        public function getAboutInfoByPage($page, $page_size, Array $param)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1);
            if (isset($param['about_category_id']) && $param['about_category_id']) {
                $select->where('about_category_id =?', $param['about_category_id']);
            }
            if (isset($param['about_category_title']) && $param['about_category_title']) {
                $select->where('about_category_title like ?', "%" . $param['about_category_title'] . "%");
            }
            $select->order(array( 'sort_id ASC', 'time_create asc' ))
                ->limitPage($page, $page_size);
            return $this->fetchAll($select);
        }


        public function getAboutCateInfoByName($catename){
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1);
            if (isset($catename) && $catename) {
                $select->where('about_category_title =?',$catename );
            }
            return $this->fetchRow($select);
        }

    }