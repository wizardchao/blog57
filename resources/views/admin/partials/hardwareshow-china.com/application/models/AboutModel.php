<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/1/25
     * Time: 15:38
     */

    class AboutModel extends Star_Model_Abstract
    {

        protected $_name = 'about';

        protected $_primary = 'about_id';


        /**
         * 通过id获取about信息
         * @param $about_id
         * @return type
         */
        public function getAboutById($about_id)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('about_id =?', $about_id)
                ->where('status >=?', 1);
            return $this->fetchRow($select);
        }


        /**
         * 通过about_title获取about信息
         * @param $name
         * @return type
         */
        public function getAboutByName($name)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('about_title =?', $name)
                ->where('status >=?', 0);
            return $this->fetchRow($select);
        }


        /**
         * 通过cateID获取about详情
         * @param $about_category_id
         * @return type
         */
        public function getAboutByCateId($about_category_id)
        {
            $select = $this->select();
            $select->from($this->getTableName() . " AS a")
                ->where('a.status >=?', 0)
                ->joinInner($this->getTableName('about_category') . " AS c", "c.about_category_id = a.about_category_id")
                ->where('c.about_category_id =?', $about_category_id)
                ->where('c.status >=?', 0);
            return $this->fetchAll($select);

        }


        /**
         * 通过cate的title获取about的信息
         * @param $about_category_title
         * @return type
         */
        public function getAboutByCateName($about_category_title)
        {
            $select = $this->select();
            $select->from($this->getTableName() . " AS a")
                ->where('a.status >=?', 0)
                ->joinInner($this->getTableName('about_category') . " AS c", "c.about_category_id = a.about_category_id")
                ->where('c.about_category_title =?', $about_category_title)
                ->where('c.status >=?', 1);
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
        public function getAboutInfo($page, $page_size, Array $params)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->limitPage($page, $page_size)->order('about_id DESC');
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
        public function getAboutInfoByPage($page, $page_size, Array $params)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1);
            if ($params) {
                if (isset($params['about_category_id']) && $params['about_category_id']) {
                    $select->where('about_category_id =?', $params['about_category_id']);
                }

                if (isset($params['is_show']) && $params['is_show']) {
                    $select->where('is_show =?', $params['is_show']);
                }

                if (isset($params['home_show']) && $params['home_show']) {
                    $select->where('home_show =?', $params['home_show']);
                }
            }

            $select->limitPage($page, $page_size)->order(array( 'sort_id ASC', 'time_create ASC' ));
            return $this->fetchAll($select);
        }


        /*
        * 取出总数
        */
        public function getAllCounts($params = NULL)
        {
            $select = $this->select();
            $select->from($this->getTableName(), "COUNT(1)")
                ->where('status >=?', 1);
            if ($params) {
                if (isset($params['about_category_id']) && $params['about_category_id']) {
                    $select->where('about_category_id =?', $params['about_category_id']);
                }

                if (isset($params['is_show']) && $params['is_show']) {
                    $select->where('is_show =?', $params['is_show']);
                }

                if (isset($params['home_show']) && $params['home_show']) {
                    $select->where('home_show =?', $params['home_show']);
                }
            }
            return $this->fetchOne($select);
        }


        /*
        * 取出总数
         */
        public function getAboutAllInfo($param = NULL)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('status >=?', 1);
            return $this->fetchAll($select);
        }


        public function getAllChildren($pid)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('about_relation like ?', '%' . $pid . '%')
                ->where('pid > ?', 0)
                ->where('about_level >?', 1)
                ->where('status >=?', 1)
                ->order(array( 'about_level asc', 'sort_id desc' ));
            return $this->fetchAll($select);
        }


        public function getAllParentCounts($param)
        {
            $select = $this->select();
            $select->from($this->getTableName(), "COUNT(1)")
                ->where('pid =?', 0)
                ->where('status >=?', 1);
            return $this->fetchOne($select);
        }


        public function getParentInfoByPage($page, $page_size, $param)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('pid =?', 0)
                ->where('status >=?', 1)
                ->order(array( 'sort_id asc' ))->limitPage($page, $page_size);
            return $this->fetchAll($select);
        }


        public function getAboutParent($is_show = NULL)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('pid =?', 0)
                ->where('status >=?', 1);
            if (isset($is_show) && $is_show) {
                $select->where('is_show =?', $is_show);
            }
            $select->order(array( 'sort_id asc' ));
            return $this->fetchAll($select);
        }


        public function getAboutChildren($id, $is_show = NULL)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('pid =?', $id)
                ->where('status >=?', 1);
            if (isset($is_show) && $is_show) {
                $select->where('is_show =?', $is_show);
            }
            $select->order(array( 'sort_id asc' ));
            // echo $select;exit;
            return $this->fetchAll($select);
        }


        public function getAboutInfoByTemplate($template_name)
        {
            $select = $this->select();
            $select->from($this->getTableName())
                ->where('template_name =?', $template_name)
                ->where('status >=?', 1)
                ->limit(1);
            return $this->fetchRow($select);
        }

    }

?>
