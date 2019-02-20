<?php

    /*
    * author: chason
    * date: 2018-08-13
    */

    class ArticleModel extends Star_Model_Abstract
    {

        protected $_name = 'article';
        protected $_primary = 'article_id';


        /**
         * 通过id获取信息
         * @param $about_id
         * @return type
         */
        public function getById($id)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('article_id =?', $id)
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

            if (isset($params['article_id']) && $params['article_id']) {
                $select->where('article_id =?', $params['article_id']);
            }
            if (isset($params['article_category_id']) && $params['article_category_id']) {
                $select->where('article_category_id =?', $params['article_category_id']);
            }
            if (isset($params['title']) && $params['title']) {
                $select->where('title like ?', '%' . $params['title'] . '%');
            }
            $select->limitPage($page, $page_size)->order(array('sort_id asc', 'article_id DESC'));
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

            if (isset($param['article_category_id']) && $param['article_category_id']) {
                $select->where('article_category_id =?', $param['article_category_id']);
            }
                
            $select->order(array("sort_id asc", "article_id DESC"));
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
            if (isset($param['article_category_id']) && $param['article_category_id']) {
                $select->where('article_category_id =?', $param['article_category_id']);
            }
            return $this->fetchOne($select);
        }


        public function getChild($article_category_id)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1)
                ->where('article_category_id =?', $article_category_id)
                ->order(array("sort_id asc", "article_id DESC"));
            return $this->fetchAll($select);
        }


        public function getArticleByTemplate($template_name)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('template_name =?', $template_name)
                ->where('status >=?', 1);
            return $this->fetchRow($select);
        }

    }
