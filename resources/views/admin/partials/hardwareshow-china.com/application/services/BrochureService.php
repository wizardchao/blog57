<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/1/25
     * Time: 15:48
     */

    class BrochureService
    {
        protected $brochureModel;


        /*
        * 构造函数
        */
        public function __construct()
        {
            $this->brochure_model          = new BrochureModel();
            $this->brochure_category_model = new BrochureCategoryModel();
        }


        /**
         * 返回分页数据
         *
         * @return type
         */
        public function getBrochureInfoByPage($page, $page_size, $param)
        {
            $total     = $this->brochure_model->getAllCounts($param);
            $page      = Star_Page::setPage($page, $page_size, $total);
            $list      = $this->brochure_model->getBrochureInfoByPage($page, $page_size, $param);
            $page_info = compact('page', 'page_size', 'total');
            $page_data = Star_Page::show($page_info);
            return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
        }


        /*
         * 添加数据
         */
        public function insertbrochure($param)
        {
            return $this->brochure_model->insert($param);
        }


        /*
         * 编辑新闻
         */
        public function updatebrochure($arr, $param)
        {
            return $this->brochure_model->update($arr, $param);
        }


        /*
         * 查找新闻
         */
        public function getBrochureInfoById($brochure_id)
        {
            return $this->brochure_model->getBrochureInfoById($brochure_id);
        }


        /*
         * 删除
         */
        public function delBrochure($arr)
        {
            $data = array(
                'status' => -1,
            );
            return $this->brochure_model->update($arr, $data);
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
            $total         = $this->brochure_category_model->getCategoryCount($params);
            $page          = Star_Page::setPage($page, $page_size, $total);
            $page_info     = array(
                'page' => $page,
                'page_size' => $page_size,
                'total' => $total,
            );
            $page_data     = Star_Page::show($page_info);
            $category_list = $this->brochure_category_model->getCategoryByPage($page, $page_size, $params);
            return array(
                'page' => $page_data,
                'category_list' => $category_list,
                'total' => $total,
            );
        }


        /**
         * 添加分类
         * @param array $data
         * @return int
         */
        public function insertCategory($data)
        {
            return $this->brochure_category_model->insert($data);
        }


        /**
         * 更新分类
         * @param string $where
         * @param array $data
         * @return int
         */
        public function updateCategory($where, $data)
        {
            return $this->brochure_category_model->update($where, $data);
        }


        /**
         * 删除分类
         * @param string $where
         * @return int
         */
        public function deleteCategory($where)
        {
            $data = array(
                'status' => -1,
            );
            return $this->brochure_category_model->update($where, $data);
        }


        /*
       * 查找id对应分类信息
       */
        public function getCategoryById($category_id)
        {
            return $this->brochure_category_model->getPk($category_id);
        }


        public function getInfo($parent_id)
        {
            $result = $this->brochure_category_model->getInfo($parent_id);
            return $result['category_name'];
        }


        public function getParentInfo()
        {
            return $this->brochure_category_model->getParentInfo();
        }


        /**
         * 获取所有分类信息
         */
        public function getAllCateInfo()
        {
            $params    = array();
            $page      = 0;
            $page_size = $this->brochure_category_model->getCategoryCount($params);
            return $this->brochure_category_model->getCategoryByPage($page, $page_size, $params);
        }


        public function getAllBrochure($param=array()){
          return $this->brochure_model->getAllBrochure($param=array());
        }

    }
