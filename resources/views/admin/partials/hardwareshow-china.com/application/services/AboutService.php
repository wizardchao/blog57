<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/1/25
     * Time: 15:48
     */

    class AboutService
    {
        protected $about_model;
        protected $aboutCateModel;


        /*
        * 构造函数
        */
        public function __construct()
        {
            $this->about_model    = new AboutModel();
            $this->aboutCateModel = new AboutCategoryModel();
        }


        /*
         * 添加数据
         */
        public function insertAbout($param)
        {
            return $this->about_model->insert($param);
        }


        /*
         * 编辑信息
         */
        public function updateAbout($arr, $param)
        {
            return $this->about_model->update($arr, $param);
        }


        /**
         * @param $arr
         * @param $param
         */
        public function editAbout($arr, $param)
        {
            $about_id = $arr['about_id'];
        }


        /*
         * 通过id获取about
         */
        public function getAboutInfoById($about_id)
        {
            return $this->about_model->getAboutById($about_id);
        }


        /*
         * 删除
         */
        public function delAbout($arr)
        {
            $data = array(
                'status' => -1,
            );
            return $this->about_model->update($arr, $data);
        }


        /*
         * 查找栏目 所有信息
         */
        public function getAboutAllInfo()
        {
            //        return $this->about_model->getAboutAllInfo();
            $about_info = $this->about_model->getAboutAllInfo();
            foreach ($about_info as &$item) {
                $link         = "/about/index?id=" . $item['about_id'];
                $item['link'] = ($item['type'] == 1) ? $link : $item['link'];
            }
            return $about_info;
        }


        public function getAboutCate()
        {
            return $this->aboutCateModel->getAboutAll();
        }


        public function getAboutCateInfoByPage($page, $page_size, $param)
        {
            $total     = $this->aboutCateModel->getAllCounts($param);
            $page      = Star_Page::setPage($page, $page_size, $total);
            $list      = $this->aboutCateModel->getAboutInfoByPage($page, $page_size, $param);
            $page_info = compact('page', 'page_size', 'total');
            $page_data = Star_Page::show($page_info);
            return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
        }


        //增加广告分类
        public function insertAboutCate($param)
        {
            return $this->aboutCateModel->insert($param);
        }


        //查找广告分类
        public function getAboutCateInfoById($about_category_id)
        {
            return $this->aboutCateModel->getAboutCateInfoById($about_category_id);
        }


        //编辑广告分类
        public function updateAboutCate($arr, $param)
        {
            return $this->aboutCateModel->update($arr, $param);
        }


        //删除广告分类
        public function delAboutCate($arr)
        {
            $param = array(
                'status' => -1,
            );
            return $this->aboutCateModel->update($arr, $param);
        }


        //获取全部分类内容
        public function getCateAll()
        {
            return $this->aboutCateModel->getAboutAll();
        }

        /**
         * 通过id获取about信息
         * @param $about_id
         * @return type
         */
        public function getAboutById($about_id)
        {
            return $this->about_model->getAboutById($about_id);
        }


        /**
         * 通过about_title获取about信息
         * @param $name
         * @return type
         */
        public function getAboutByName($name)
        {
            return $this->about_model->getAboutByName($name);
        }


        /**
         * 通过cateID获取about详情
         * @param $about_category_id
         * @return type
         */
        public function getAboutByCateId($about_category_id)
        {
            return $this->about_model->getAboutByCateId($about_category_id);
        }


        /**
         * 通过cate的title获取about的信息
         * @param $about_category_title
         * @return type
         */
        public function getAboutByCateName($about_category_title)
        {
            return $this->about_model->getAboutByCateName($about_category_title);
        }


        /**
         * @param $page
         * @param $page_size
         * @param $param
         * @return array
         */
        public function checkPage($page, $page_size, $param)
        {
            if (empty($param['menu_id']) && empty($param['menu_name']) && empty($param['pid'])) {
                return $this->getParentByPage($page, 10, $param);
            }
            return $this->getAboutInfoByPage($page, $page_size, $param);
        }

        public function getParentByPage($page, $page_size, $param)
        {
            $total     = $this->about_model->getAllParentCounts($param);
            $page      = Star_Page::setPage($page, $page_size, $total);
            $list      = $this->getAllChildren($page, $page_size, $param);
            $page_info = compact('page', 'page_size', 'total');
            $page_data = Star_Page::show($page_info);
            return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
        }


        /*
           * 管理菜单编辑
           */

        public function getAllChildren($page, $page_size, $param)
        {
            $list   = $this->about_model->getParentInfoByPage($page, $page_size, $param);
            $re_arr = array();
            foreach ($list as $item) {
                $id                         = $item['about_id'];
                $child_arr                  = $this->about_model->getAllChildren($item['about_id']);
                $menu_arr                   = array();
                $new_arr[$item['about_id']] = $item;
                foreach ($child_arr as $value) {
                    $new_arr[$value['about_id']] = $value;
                    $menu_arr[]                  = explode(',', $value['about_relation']);
                }

                $sort_arr = UtilsHelper::treeSort($id, $menu_arr);
                foreach ($sort_arr as $key => $value) {
                    $re_arr[$value] = $new_arr[$value];
                }

                //                $re_arr[]  = $item;
                //                $child_arr = $this->about_model->getAllChildren($item['about_id']);
                //                $re_arr    = array_merge($re_arr, $child_arr);
                //                unset($child_arr);
            }

            return $re_arr;
        }


        /**
         * 返回分页数据
         *
         * @return array
         */
        public function getAboutInfoByPage($page, $page_size, $param)
        {
            $total     = $this->about_model->getAllCounts($param);
            $page      = Star_Page::setPage($page, $page_size, $total);
            $list      = $this->about_model->getAboutInfoByPage($page, $page_size, $param);
            $page_info = compact('page', 'page_size', 'total');
            $page_data = Star_Page::show($page_info);
            return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
        }


        public function getAllChildrenInfo()
        {
            $page       = 0;
            $page_size  = 50;
            $param      = array();
            $about_info = $this->getAllChildren($page, $page_size, $param);
            foreach ($about_info as &$info) {
                if ($info['about_level'] > 1) {
                    $level               = $info['about_level'] - 1;
                    $info['about_title'] = trim(str_repeat('&nbsp;&nbsp;&nbsp;', $level) . "|—" . $info['about_title']);
                }
            }
            return $about_info;
        }


        /**
         * @param $about_id
         * @return int
         */
        public function delAbouts($about_id)
        {
            $where = "about_relation LIKE " . "'%{$about_id}%'";

            $param = array(
                'status' => -1,
            );
            return $this->about_model->update($where, $param);
        }


        public function sortTree($arr)
        {
            foreach ($arr as &$val) {
            }
        }


        public function getAboutChild($arr)
        {
            $pid     = $arr['pid'];
            $new_arr = array();
            while (($pid >= 0) && isset($pid)) {
                array_unshift($new_arr, $arr['about_title']);
                $arr = $this->about_model->getAboutById($pid);
                $pid = $arr['pid'];
            }

            return $new_arr;
        }


        public function getSecondChild($pid, $is_show=0)
        {
            $is_show=isset($is_show)?:0;
            $list=$this->about_model->getAboutChildren($pid, $is_show);
            foreach($list as &$elem){
              $elem['href']=$elem['template_name']?'/about/'.$elem['template_name'].'.html':'/about/id/'.$elem['about_id'].'.html';
            }
            return $list;
            // return $this->about_model->getAboutChildren($pid, $is_show);
        }


        public function getAboutInfoByTemplate($template_name)
        {
            return $this->about_model->getAboutInfoByTemplate($template_name);
        }


        public function getParents($relation)
        {
            $arr=explode(',', $relation);
            $list=array(
            'level' => count($arr),
          );

            foreach ($arr as $el) {
                $list['list'][]=$this->getAboutById($el);
            }
            unset($arr);
            return $list;
        }
    }
