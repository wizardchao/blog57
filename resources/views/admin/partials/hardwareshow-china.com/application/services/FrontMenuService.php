<?php

/**
 * Created by PhpStorm.
 * User: Chris_Chiang
 * Date: 2018/7/26
 * Time: 12:11
 */
class FrontMenuService
    {

        protected $FrontMenu;
        public $tree;


        /*
        * 构造函数
        */
        public function __construct()
        {
            $this->FrontMenu     = new FrontMenuModel();
            $this->tree=new TreeService();

        }

        public function getMenuInfoById($menu_id)
        {
            return $this->FrontMenu->getInfoById($menu_id);
        }

        /**
         * 获取父级相同菜单
         */
        public function getplist($pid)
        {
            return $this->FrontMenu->getplist($pid);
        }

        /**
         * 添加菜单icon
         * @param $menuLists
         * @return mixed
         */
        public function addSelectIcon(&$menuLists){
            foreach ($menuLists as &$item) {
                if ($item['menu_level'] > 1) {
                    $level             = $item['menu_level'] - 1;
                    $item['menu_name'] = str_repeat('&nbsp;&nbsp;&nbsp;', $level) ."|—". $item['menu_name'];
                }
                if(isset($item['son_cate'])){
                    self::addSelectIcon($item['son_cate']);
                }
            }

            return $menuLists;
        }


        /**
         * 获取下拉菜单列表
         * @return mixed
         */
        public function menuLists()
        {
            $res=$this->getCateTreeInCache();
            $this->addSelectIcon($res);
            return $res;
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

        $back_menu=Star_Cache::get(DOMAIN_MANAGE.'front_menu');
        if(!empty($back_menu)){
            return $back_menu;
        }else{
            $res=$this->FrontMenu->getAllCate();
            $res=$this->tree->generateTree($res,'id','pid');
            Star_Cache::set('front_menu',$res);
            return $res;
        }

    }




        /**返回所有菜单信息
         * @param $param
         * @return type
         */

        public function getInfoByAll($param)
        {
            return $this->FrontMenu->getInfoByAll($param);
        }

        /**
         * @param $page
         * @param $page_size
         * @param $param
         * @return array
         */
        public function getManageInfoByPage($page, $page_size, $param)
        {
            $total     = $this->FrontMenu->getAllCounts($param);
            $page      = Star_Page::setPage($page, $page_size, $total);
            $list      = $this->FrontMenu->getInfoByPage($page, $page_size, $param);
            $page_info = compact('page', 'page_size', 'total');
            $page_data = Star_Page::show($page_info);
            return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
        }


        /**
         * 给菜单添加link
         * @param $menu_list
         * @return mixed
         *
         */
        public function addLink(&$menu_list){
            foreach ($menu_list as &$list) {
                $list['link'] = ($list['controller'] && $list['action']) ? DOMAIN_WWW.'/' .$list['controller'] . '/' . $list['action'] : '';
                unset($list['controller']);
                unset($list['action']);
                if($list['son_cate']){
                    self::addLink($list['son_cate']);
                }
            }
            return $menu_list;
        }



        /**
         * @param $menu_list
         * @return mixed
         * 给菜单增加空格样式
         */
        public function addIcon(&$menu_list){
            foreach ($menu_list as &$list) {
                $list['link'] = ($list['controller'] && $list['action']) ? '/' . $list['controller'] . '/' . $list['action'] : '';
                switch ($list['menu_level']) {
                    case 1:
                        $label_class   = '';
                        $list['class'] = "fz14 fwb";
                        break;
                    case 2:
                        $label_class   = '<i class="level-label">—</i>';
                        $list['class'] = "fz14";
                        break;
                    default:
                        $level         = $list['menu_level'] - 2;
                        $list['class'] = '';
                        $label_class   = str_repeat('<i class="level-label"></i>', $level) . '<i class="level-label">—</i>';

                }
                $list['menu_name'] = $label_class . $list['menu_name'];
                if($list['son_cate']){
                    self::addIcon($list['son_cate']);
                }
            }
            return $menu_list;
        }





        /*
        * 管理菜单删除
        */

        public function menuAdd($param)
        {
            Star_Cache::set(DOMAIN_MANAGE.'front_menu','');
            return $this->FrontMenu->insert($param);

        }


        public function updateMenuListOrder($param){
            $where = array(
                'id' => $param['id'],
            );
            Star_Cache::set(DOMAIN_MANAGE.'front_menu','');
            return $this->FrontMenu->update($where, $param);
        }
        /*
        * 读取管理菜单(framework left)
        */

        public function menuEdit($menu_id, $param)
        {
            $where = array(
                'id' => $menu_id,
            );
            Star_Cache::set(DOMAIN_MANAGE.'front_menu','');
            return $this->FrontMenu->update($where, $param);
        }



        public function menuDel($menu_id)
        {
            $where = "menu_relation LIKE " . "'%{$menu_id}%'";

            $param = array(
                'status' => -1,
            );
            Star_Cache::set(DOMAIN_MANAGE.'front_menu','');
            return $this->FrontMenu->update($where, $param);
        }


        public function search_menu($param)
        {
            $list= $this->FrontMenu->getInfoByAll($param);
            if($list){
                @$this->addIcon($list);
            }
            return $list;

        }



    }
