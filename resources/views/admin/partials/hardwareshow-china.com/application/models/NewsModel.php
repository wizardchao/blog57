<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/1/25
     * Time: 15:40
     */


    class NewsModel extends Star_Model_Abstract
    {

        protected $_name = 'news';

        protected $_primary = 'news_id';


        /*
         * 根据news_id返回结果
         */
        public function getNewsInfoById($news_id)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('news_id =?', $news_id)
                ->where('status >=?', 1);
            return $this->fetchRow($select);
        }


        public function getNewsInfoByName($name)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('news_title =?', $name)
                ->where('status >=?', 0);
            return $this->fetchRow($select);
        }


        public function getNewsListsByCateName($name)
        {
            $select = $this->select();
            $select->from($this->getTableName() . " AS n")
                ->where('c.status >=?', 0)
                ->joinInner($this->getTableName('news_category') . " AS c", "c.category_id = n.category_id")
                ->where('c.category_name =?', $name);
            return $this->fetchAll($select);

        }


        public function getNewsListsByCateId($id)
        {
            $select = $this->select();
            $select->from($this->getTableName() . " AS n")
                ->where('c.status >=?', 0)
                ->joinInner($this->getTableName('news_category') . " AS c", "c.category_id = n.category_id")
                ->where('c.category_id =?', $id);
            return $this->fetchAll($select);

        }

        /**
         * 返回有关信息
         *
         * @param type $page
         * @param type $page_size
         * @param type $params
         * @return type
         */
        public function getNewsInfoByPage($page, $page_size, Array $param)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1);
            if ($param) {
                if (isset($param['category_id']) && $param['category_id']) {
                    $select->where('category_id =?', $param['category_id']);
                }
                if (isset($param['news_id']) && $param['news_id']) {
                    $select->where('news_id =?', $param['news_id']);
                }
                if (isset($param['news_title']) && $param['news_title']) {
                    $select->where('news_title like ?', '%' . $param['news_title'] . '%');
                }

                if (isset($param['is_recommend']) && is_numeric($param['is_recommend'])) {
                    $select->where('is_recommend =?', $param['is_recommend']);
                }
                if (isset($param['home_show']) && is_numeric($param['home_show'])) {
                    $select->where('home_show =?', $param['home_show']);
                }
                if (isset($param['is_up']) && is_numeric($param['is_up'])) {
                    $select->where('is_up =?', $param['is_up']);
                }
                if (isset($param['search']) && ($param['search'])) {
                    $select->where('news_title like ?', '%' . $param['search'] . '%');
                }
            }

            if ($param['sort_flag']) {
                $select->limitPage($page, $page_size)->order(array( 'news_tm_publish desc', 'is_up DESC', 'time_create DESC', 'news_id DESC' ));
            } else {
                $select->limitPage($page, $page_size)->order(array( 'news_tm_publish desc', 'is_up DESC', 'sort_id ASC', 'time_create DESC', 'news_id DESC' ));
            }

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
                if (isset($param['category_id']) && $param['category_id']) {
                    $select->where('category_id =?', $param['category_id']);
                }
                if (isset($param['news_id']) && $param['news_id']) {
                    $select->where('news_id =?', $param['news_id']);
                }
                if (isset($param['news_title']) && $param['news_title']) {
                    $select->where('news_title like ?', '%' . $param['news_title'] . '%');
                }
                if (isset($param['is_recommend']) && is_numeric($param['is_recommend'])) {
                    $select->where('is_recommend =?', $param['is_recommend']);
                }
                if (isset($param['home_show']) && is_numeric($param['home_show'])) {
                    $select->where('home_show =?', $param['home_show']);
                }
                if (isset($param['is_up']) && is_numeric($param['is_up'])) {
                    $select->where('is_up =?', $param['is_up']);
                }
                if (isset($param['search']) && ($param['search'])) {
                    $select->where('news_title like ?', '%' . $param['search'] . '%');
                }
            }
            return $this->fetchOne($select);
        }


        /*
         * 获取news_id
         */
        public function getNewsIdAByTitle($news_title)
        {
            $select = $this->select();
            $select->from($this->getTableName(), "news_id")
                ->where('status >=?', 1)
                ->where('news_title =?', $news_title)
                ->limit(1);
            return $this->fetchOne($select);
        }


        /*
         * 获取最新一条新闻
         */
        public function getLastNews()
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1)
                ->where("is_up =?", 0)
                ->order('time_create desc')
                ->limit(1);
            return $this->fetchRow($select);
        }


        public function getNewsInfoByCate($category_id)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1)
                ->where("category_id =?", $category_id)
                ->order('sort_id desc');
            return $this->fetchAll($select);
        }


        public function getNewsInfoByTemplate($template_name)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1)
                ->where("template_name =?", $template_name)
                ->limit(1);
            return $this->fetchRow($select);
        }

    }

?>