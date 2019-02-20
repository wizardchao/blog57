<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/31
     * Time: 22:00
     */
    require APPLICATION_PATH . '/modules/api/controllers/CommonController.php';

    class ArticleController extends CommonController
    {
        protected $articleService;
        private $dd;

        public function init()
        {
            parent::init();
            $artcicleService      = new ArticleService();
            $this->articleService = new ApiArticleService($artcicleService);
            $this->dd             = new Dd();
        }


        /**
         * 文章列表
         */
        public function listAction()
        {
            $request     = $this->getRequest();
            $page        = (int)$request->getParam('page');
            $article_cid = (int)$request->getParam('article_cid');
            $page_size   = 3;
            $param       = array(
                'article_cid' => $article_cid,
            );
            $data        = $this->articleService->getArticleInfoByPage($page, $page_size, $param);
            $ck_page     = $this->utilsHelper->is_page($data['total'], $page, $page_size);
            if ($ck_page) {
                return $this->showJson(223, "超出页数范围！");
            }

            $data['page_total'] = ceil($data['total'] / $page_size);
            $data['page_size']  = (int)$page_size;
            return $this->showJson(200, $data);

        }


        /**
         * 文章详情
         */
        public function detailAction()
        {
            $request    = $this->getRequest();
            $article_id = (int)$request->getParam('id');

            if (empty($article_id)) {
                return $this->showJson(201, '参数不能为空！');
            }

            $article_info = $this->articleService->getArticleById($article_id);

            if (empty($article_info)) {
                return $this->showJson(202, '参数有误！');
            }

            return $this->showJson(200, $article_info);
        }


        /**
         * 文章分类
         */
        public function categoryAction()
        {
            $article_list = $this->articleService->getArticleAllCateInfo();

            return $this->showJson(200, $article_list);
        }


    }