<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/27
     * Time: 15:14
     */

    class FoodService
    {
        /*
         * 构造函数
         */
        public function __construct()
        {
        }


        public function getOtherFood()
        {
            $param = array(
                array(
                    'id' => 1001,
                    'title' => '精细食品及进口食品',
                ),
                array(
                    'id' => 1002,
                    'title' => '乳制品',
                ),
                array(
                    'id' => 1003,
                    'title' => '甜食及休闲食品',
                ),
                array(
                    'id' => 1004,
                    'title' => '肉质品',
                ),
                array(
                    'id' => 1005,
                    'title' => '海鲜',
                ),
                array(
                    'id' => 1006,
                    'title' => '冷冻食品',
                ),
                array(
                    'id' => 1007,
                    'title' => '茶和咖啡',
                ),
                array(
                    'id' => 1008,
                    'title' => '饮料（非酒精类）',
                ),
                array(
                    'id' => 1009,
                    'title' => '酒类',
                ),
                array(
                    'id' => '1010',
                    'title' => '有机食品',
                ),
                array(
                    'id' => '1011',
                    'title' => '水果及蔬菜',
                ),
                array(
                    'id' => 1012,
                    'title' => '清真食品',
                ),
                array(
                    'id' => 1013,
                    'title' => '橄榄油及食用油',
                ),
                array(
                    'id' => 1014,
                    'title' => '自动贩卖机',
                ),
                array(
                    'id' => 1015,
                    'title' => '餐饮服务',
                ),
                array(
                    'id' => 1016,
                    'title' => '面包及烘培产品',
                ),
            );

            return $param;
        }
    }