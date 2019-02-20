<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/9/19
     * Time: 22:27
     */

    require APPLICATION_PATH . '/modules/api/controllers/CommonController.php';

    class BrandController extends CommonController
    {
        protected $articleService;
        protected $brandService;
        private $dd;

        public function init()
        {
            parent::init();
            $brandService       = new GoodsBrandService();
            $this->brandService = new ApiBrandService($brandService);
            $this->dd           = new Dd();
        }


        /**
         * 品牌列表
         */
        public function listAction()
        {
            $request   = $this->getRequest();
            $page      = (int)$request->getParam('page');
            $page_size = 6;
            $param     = array();
            $data      = $this->brandService->getBrandInfoByPage($page, $page_size, $param);
            $ck_page    = $this->utilsHelper->is_page($data['total'], $page, $page_size);
            if ($ck_page) {
                return $this->showJson(223, "超出页数范围！");
            }
            return $this->showJson(200, $data);
        }


        /**
         * 品牌详情
         */
        public function detailAction()
        {
            $request   = $this->getRequest();
            $page      = (int)$request->getParam('page');
            $brand_id      = (int)$request->getParam('brand_id');
            if (empty($brand_id)) {
                return $this->showJson(201, '参数不能为空！');
            }
            $page_size = 6;
            $data      = $this->brandService->getGoodsBrandInfoById($brand_id,$page,$page_size);
            unset($data['goods_info']);
            if (empty($data)) {
                return $this->showJson(202, '参数有误！');
            }
            return $this->showJson(200, $data);
        }
    }