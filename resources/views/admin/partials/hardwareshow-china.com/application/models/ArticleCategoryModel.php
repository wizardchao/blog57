<?php

    /*
    * author: chason
    * date: 2018-08-13
    */

    class ArticleCategoryModel extends Star_Model_Abstract
    {

        protected $_name = 'article_category';
        protected $_primary = 'article_category_id';


        /**
         * 通过id获取信息
         * @param $about_id
         * @return type
         */
        public function getById($id)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('article_category_id =?', $id)
                ->where('status >=?', 1);
            return $this->fetchRow($select);
        }


        /**
         * 返回所有信息
         */
        public function getAll()
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1)
                ->order("sort_id asc");
            return $this->fetchAll($select);
        }


        public function getCategoryByTemplate($template_name)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('template_name =?', $template_name)
                ->where('status >=?', 1);
            return $this->fetchRow($select);
        }


    }
