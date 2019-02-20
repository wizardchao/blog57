<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/1/25
     * Time: 15:48
     */

    class NewsService
    {
        protected $newsModel;


        /*
        * 构造函数
        */
        public function __construct()
        {
            $this->news_model          = new NewsModel();
            $this->news_category_model = new NewsCategoryModel();
            $this->thumb_up_model      = new ThumbUpModel();
            $this->commentModel        = new CommentModel();
            $this->utilHelpers         = new UtilsHelper();
            $this->tree                = new TreeService();
        }


        /**
         * 返回分页数据
         *
         * @return type
         */
        public function getNewsInfoByPage($page, $page_size, $param)
        {
            $total     = $this->news_model->getAllCounts($param);
            $page      = Star_Page::setPage($page, $page_size, $total);
            $list      = $this->news_model->getNewsInfoByPage($page, $page_size, $param);
            $page_info = compact('page', 'page_size', 'total');
            $page_data = Star_Page::show($page_info);
            return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
        }


        /*
         * 添加数据
         */
        public function insertNews($param)
        {
            return $this->news_model->insert($param);
        }


        /*
         * 编辑新闻
         */
        public function updateNews($arr, $param)
        {
            return $this->news_model->update($arr, $param);
        }


        /*
         * 查找新闻
         */

        public function delNews($arr)
        {
            $data = array(
                'status' => -1,
            );
            return $this->news_model->update($arr, $data);
        }


        /*
         * 删除
         */

        public function addHits($news_id)
        {
            $hits_re = $this->getNewsInfoById($news_id);
            $hits    = $hits_re['hits'] + 1;

            $where = array(
                'news_id' => $news_id,
            );
            $param = array(
                'hits' => $hits,
            );
            return $this->news_model->update($where, $param);
        }


        /**
         * 通过id获取新闻信息
         * @param $news_id
         * @return type
         */
        public function getNewsInfoById($news_id)
        {
            return $this->news_model->getNewsInfoById($news_id);
        }

        /**
         * 通过新闻title获取新闻信息
         * @param $name
         * @return type
         */
        public function getNewsInfoByname($name)
        {
            return $this->news_model->getNewsInfoByname($name);
        }


        /**
         * 通过栏目名称获取新闻信息
         * @param $name
         * @return type
         */
        public function getNewsListsByCateName($name)
        {
            return $this->news_model->getNewsListsByCateName($name);
        }


        /**
         * 通过栏目id获取新闻信息
         * @param $id
         * @return type
         */
        public function getNewsListsByCateId($id)
        {
            return $this->news_model->getNewsListsByCateId($id);
        }


        /**
         * 根据条件返回分类列表
         * @param int $page
         * @param int $page_size
         * @param array $params
         * @return array
         */
        public function getCategoryByPage($page, $page_size, array $params)
        {
            $total         = $this->news_category_model->getCategoryCount($params);
            $page          = Star_Page::setPage($page, $page_size, $total);
            $page_info     = array(
                'page' => $page,
                'page_size' => $page_size,
                'total' => $total,
            );
            $page_data     = Star_Page::show($page_info);
            $category_list = $this->news_category_model->getCategoryByPage($page, $page_size, $params);
            return array(
                'page' => $page_data,
                'category_list' => $category_list,
                'total' => $total,
            );
        }


        /**
         * 获取无限极分类列表，通过一级菜单分页取出对应分类列表，并返回
         * @param int $page
         * @param int $page_size
         * @return array
         */
        public function getFirstCateByPage($page = 1, $page_size = 5)
        {
            $res   = $this->getCateTreeInCache();
            $total = count($res);
            $page  = Star_Page::setPage($page, $page_size, $total);

            if (is_array($res)) {
                $start     = $page - 1;
                $lenth     = $page_size;
                $list      = array_slice($res, $start * $lenth, $lenth);
                $page_info = compact('page', 'page_size', 'total');
                $page_data = Star_Page::show($page_info);
                return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
            }
            return array();

        }

        /**
         * 减少查询cpu的IO开销，将分类信息的数据存入缓存，并且形成树形结构
         * @return array|type
         */
        public function getCateTreeInCache()
        {
            $back_menu = Star_Cache::get(DOMAIN_MANAGE . 'news_cate');
            if (!empty($back_menu)) {
                return $back_menu;
            } else {
                $res = $this->news_category_model->getAllCate();
                $res = $this->tree->generateTree($res, 'category_id', 'pid');
                Star_Cache::set('news_cate', $res);
                return $res;
            }

        }


        /**
         * @param $menu_list
         * @return mixed
         * 加icon样式
         */
        public function addIcon(&$menu_list)
        {
            foreach ($menu_list as &$list) {

                switch ($list['level']) {
                    case 1:
                        $label_class   = '';
                        $list['class'] = "fz14 fwb";
                        break;
                    case 2:
                        $label_class   = '<i class="level-label">—</i>';
                        $list['class'] = "fz14";
                        break;
                    default:
                        $level         = $list['level'] - 2;
                        $list['class'] = '';
                        $label_class   = str_repeat('<i class="level-label"></i>', $level) . '<i class="level-label">—</i>';

                }
                $list['category_name'] = $label_class . $list['category_name'];
                unset($label_class);
                if ($list['son_cate']) {
                    self::addIcon($list['son_cate']);
                }
            }
            return $menu_list;
        }


        /**
         * 添加分类
         * @param array $data
         * @return int
         */
        public function insertCategory($data)
        {

            Star_Cache::set(DOMAIN_MANAGE . 'news_cate', '');

            return $this->news_category_model->insert($data);
        }

        /**
         * 删除分类
         * @param string $where
         * @return int
         */
        public function deleteCategory($news_cate_id)
        {
            $where = "relation LIKE " . "'%{$news_cate_id}%'";
            $param = array(
                'status' => -1,
            );
            Star_Cache::set(DOMAIN_MANAGE . 'news_cate', '');
            return $this->news_category_model->update($where, $param);

        }

        /**
         * 更新分类
         * @param string $where
         * @param array $data
         * @return int
         */
        public function updateCategory($where, $data)
        {
            Star_Cache::set(DOMAIN_MANAGE . 'news_cate', '');
            return $this->news_category_model->update($where, $data);
        }


        /**
         * 根据ID返回分类信息
         * @param int $category_id
         * @return array
         */
        public function getCategoryById($category_id)
        {
            return $this->news_category_model->getPk($category_id);
        }

        public function getAllCategoryInfo()
        {
            return $this->news_category_model->getAllCategoryInfo();
        }

        public function getInfo($parent_id)
        {
            $result = $this->news_category_model->getInfo($parent_id);
            return $result['category_name'];
        }

        public function getParentInfo()
        {
            return $this->news_category_model->getParentInfo();
        }

        /**
         * 根据条件返回点赞列表
         * @param int $page
         * @param int $page_size
         * @param array $params
         * @return array
         */
        public function getThumbUpByPage($page, $page_size, array $params)
        {
            $total     = $this->thumb_up_model->getAllCount($params);
            $page      = Star_Page::setPage($page, $page_size, $total);
            $page_info = array(
                'page' => $page,
                'page_size' => $page_size,
                'total' => $total,
            );
            $page_data = Star_Page::show($page_info);
            $list      = $this->thumb_up_model->getThumbUpByPage($page, $page_size, $params);
            return array(
                'page' => $page_data,
                'list' => $list,
                'total' => $total,
            );
        }


        /**
         * 根据条件返回评论列表
         * @param int $page
         * @param int $page_size
         * @param array $params
         * @return array
         */
        public function getCommentByPage($page, $page_size, array $params)
        {
            $total     = $this->commentModel->getAllCounts($params);
            $page      = Star_Page::setPage($page, $page_size, $total);
            $page_info = array(
                'page' => $page,
                'page_size' => $page_size,
                'total' => $total,
            );
            $page_data = Star_Page::show($page_info);
            if (isset($params['reply_id']) && !$params['reply_id'] == 0) {
                $news_comms = $this->commentModel->getCommentReplyByPage($page, $page_size, $params);
            } else {
                $news_comms = $this->commentModel->getNewsCommentByPage($page, $page_size, $params);
            }


            if (!empty($news_comms)) {
                foreach ($news_comms as &$news_com) {
                    $reply                         = array( 'reply_id' => $news_com['comment_id'], 'news_id' => $news_com['news_id'] );
                    $news_com['reply_counts']      = $this->commentModel->getCommentReplyCounts($reply);
                    $thumb_up                      = array( 'comment_id' => $news_com['comment_id'], 'up_type' => 1 );
                    $news_com['thumb_up_counts']   = $this->thumb_up_model->getAllCount($thumb_up);
                    $thumb_down                    = array( 'comment_id' => $news_com['comment_id'], 'up_type' => 2 );
                    $news_com['thumb_down_counts'] = $this->thumb_up_model->getAllCount($thumb_down);
                }
            }
            //print_r($news_comms);exit;
            return array(
                'page' => $page_data,
                'list' => $news_comms,
                'total' => $total,
            );
        }


        public function getNewsIdAByTitle($news_title)
        {
            return $this->news_model->getNewsIdAByTitle($news_title);
        }


        public function getCommentInfoById($comment_id)
        {
            return $this->commentModel->getPk($comment_id);
        }


        public function getThumbUpById($up_id)
        {
            return $this->thumb_up_model->getPk($up_id);
        }


        public function deleteThumbUp($up_id)
        {
            $arr  = array(
                'up_id' => $up_id,
            );
            $data = array(
                'status' => -1,
            );

            return $this->thumb_up_model->update($arr, $data);
        }


        public function deleteComment($comment_id)
        {
            $arr = array(
                'comment_id' => $comment_id,
            );

            $data = array(
                'status' => -1,
            );
            return $this->commentModel->update($arr, $data);
        }


        public function getThumbCounts($obj)
        {
            return $this->thumb_up_model->getThumbCounts($obj);
        }


        public function getCommentCounts($obj)
        {
            return $this->commentModel->getCommentCounts($obj);
        }


        public function getLastNews()
        {
            return $this->news_model->getLastNews();
        }

        public function getAllCate()
        {
            $category_list = $this->getCateTreeInCache();
            if ($category_list) {
                $this->addSelectIcon($category_list);
            }
            return $category_list;
        }

        public function addSelectIcon(&$menuLists)
        {
            foreach ($menuLists as &$item) {
                if ($item['level'] > 1) {
                    $level                 = $item['level'] - 1;
                    $item['category_name'] = str_repeat('&nbsp;&nbsp;&nbsp;', $level) . "|—" . $item['category_name'];
                }
                if (isset($item['son_cate'])) {
                    self::addSelectIcon($item['son_cate']);
                }
            }

            return $menuLists;
        }

        public function getCategoryByTemplate($template_name)
        {
            return $this->news_category_model->getCategoryByTemplate($template_name);
        }


        public function getNewsInfoByTemplate($template_name)
        {
            return $this->news_model->getNewsInfoByTemplate($template_name);
        }


        public function getAllCategory(){
          return $this->news_category_model->getAllCategory();
        }


    }
