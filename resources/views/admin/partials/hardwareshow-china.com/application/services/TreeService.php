<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/6/1
     * Time: 19:23
     */

    class TreeService
    {
        protected $manageMenu;
        protected $utilHelpers;

        /*
           * 构造函数
           */
        public function __construct()
        {
            $this->manageMenu  = new ManageMenuModel();
            $this->utilHelpers = new UtilsHelper();
        }


        /**
         *产生树形结构
         *
         *
         *
         */
        function generateTree($list, $pk = 'id', $pid = 'pid')
        {
            $tree     = array();
            $packData = array();
            foreach ($list as $data) {
                $packData[$data[$pk]] = $data;
            }
            foreach ($packData as $key => $val) {
                if ($val[$pid] == 0) {
                    //代表跟节点, 重点一
                    $tree[] = &$packData[$key];
                } else {
                    //找到其父类,重点二
                    $packData[$val[$pid]]['son_cate'][] = &$packData[$key];
                }

            }
            return $tree;
        }

    }
