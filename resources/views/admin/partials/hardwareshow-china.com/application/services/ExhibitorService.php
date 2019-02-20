<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/1/25
     * Time: 15:48
     */

    class ExhibitorService
    {

        /*
        * 构造函数
        */
        public function __construct()
        {
            $this->exhibitorModel = new ExhibitorModel();
            $this->exhibitorCategoryModel = new ExhibitorCategoryModel();

            $this->utilHelpers         = new UtilsHelper();
            $this->tree                = new TreeService();
        }


        /**
         * 返回分页数据
         *
         * @return type
         */
        public function getInfoByPage($page, $page_size, $param)
        {
            $total     = $this->exhibitorModel->getAllCounts($param);
            $page      = Star_Page::setPage($page, $page_size, $total);
            $list      = $this->exhibitorModel->getInfoByPage($page, $page_size, $param);
            $page_info = compact('page', 'page_size', 'total');
            $page_data = Star_Page::show($page_info);
            return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
        }


        /*
         * 添加数据
         */
        public function add($param)
        {
            return $this->exhibitorModel->insert($param);
        }


        /*
         * 编辑
         */
        public function update($arr, $param)
        {
            return $this->exhibitorModel->update($arr, $param);
        }


        /*
         * 查找
         */

        public function del($arr)
        {
            $data = array(
                'status' => -1,
            );
            return $this->exhibitorModel->update($arr, $data);
        }

        /**
         * 通过id获取信息
         * @param $id
         * @return type
         */
        public function getInfoById($id)
        {
            return $this->exhibitorModel->getInfoById($id);
        }

        /**
         * 通过新闻title获取信息
         * @param $name
         * @return type
         */
        public function getInfoByname($name){
            return $this->exhibitorModel->getNewsInfoByname($name);
        }


        /**
         * 通过栏目名称获取信息
         * @param $name
         * @return type
         */
        public function getListByCategoryName($name){
            return $this->exhibitorModel->getNewsListsByCateName($name);
        }


        /**
         * 通过栏目id获取新闻信息
         * @param $id
         * @return type
         */
        public function getNewsListsByCateId($id){
            return $this->exhibitorModel->getNewsListsByCateId($id);
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
            $total         = $this->exhibitorCategoryModel->getCategoryCount($params);
            $page          = Star_Page::setPage($page, $page_size, $total);
            $page_info     = array(
                'page' => $page,
                'page_size' => $page_size,
                'total' => $total,
            );
            $page_data     = Star_Page::show($page_info);
            $category_list = $this->exhibitorCategoryModel->getCategoryByPage($page, $page_size, $params);
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
        public function getFirstCateByPage($page=1, $page_size=5)
        {
            $res=$this->getCateTreeInCache();
            $total=count($res);
            $page = Star_Page::setPage($page, $page_size, $total);

            if(is_array($res)){
                $start=$page-1;
                $lenth=$page_size;
                $list=array_slice($res,$start*$lenth,$lenth);
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
        public function getCateTreeInCache(){
            $back_menu=Star_Cache::get(DOMAIN_MANAGE.'exhibitor_category');
            if(!empty($back_menu)){
                return $back_menu;
            }else{
                $res=$this->exhibitorCategoryModel->getAllCate();
                $res=$this->tree->generateTree($res,'exhibitor_category_id','pid');
                Star_Cache::set('exhibitor_category',$res);
                return $res;
            }
        }



        /**
         * @param $menu_list
         * @return mixed
         * 加icon样式
         */
        public function addIcon(&$menu_list){
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
                if($list['son_cate']){
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
        public function insertCategory($data){

            Star_Cache::set(DOMAIN_MANAGE.'news_cate','');

            return $this->exhibitorCategoryModel->insert($data);
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
            Star_Cache::set(DOMAIN_MANAGE.'news_cate','');
            return $this->exhibitorCategoryModel->update($where, $param);

        }

        /**
         * 更新分类
         * @param string $where
         * @param array $data
         * @return int
         */
        public function updateCategory($where, $data)
        {
            Star_Cache::set(DOMAIN_MANAGE.'news_cate','');
            return $this->exhibitorCategoryModel->update($where, $data);
        }


        /**
         * 根据ID返回分类信息
         * @param int $category_id
         * @return array
         */
        public function getCategoryById($category_id)
        {
            return $this->exhibitorCategoryModel->getPk($category_id);
        }

        public function getAllCategoryInfo()
        {
            return $this->exhibitorCategoryModel->getAllCategory();
        }

        public function getInfo($parent_id)
        {
            $result = $this->exhibitorCategoryModel->getInfo($parent_id);
            return $result['category_name'];
        }

        public function getParentInfo()
        {
            return $this->exhibitorCategoryModel->getParentInfo();
        }

        public function getNewsIdAByTitle($news_title)
        {
            return $this->news_model->getNewsIdAByTitle($news_title);
        }


        public function getCommentInfoById($comment_id)
        {
            return $this->commentModel->getPk($comment_id);
        }


        public function getLastNews()
        {
            return $this->news_model->getLastNews();
        }


        public function addSelectIcon(&$menuLists){
            foreach ($menuLists as &$item) {
                if ($item['level'] > 1) {
                    $level             = $item['level'] - 1;
                    $item['category_name'] = str_repeat('&nbsp;&nbsp;&nbsp;', $level) ."|—". $item['category_name'];
                }
                if(isset($item['son_cate'])){
                    self::addSelectIcon($item['son_cate']);
                }
            }

            return $menuLists;
        }



        public function getAllCate(){
            $category_list = $this->getCateTreeInCache();
            if($category_list){
                $this->addSelectIcon($category_list);
            }
           return $category_list;
        }


        public function getCategoryByPid($pid){
          return $this->exhibitorCategoryModel->getCategoryByPid($pid);
        }

      public function getPidList(){
        $list=$this->getCategoryByPid(0);

        foreach($list as &$elem){
           $elem['child']=$this->getCategoryByPid($elem['exhibitor_category_id']);
        }

        return $list;
      }


      public function getSpaceList(){
        return array(
          1001 => '光地（最小36平米）',
          1002 => '标准摊位（最小9平米）',
        );
      }

    }
