<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/15
     * Time: 17:25
     */

    class AdService
    {
        protected $adModel;
        protected $aboutModel;
        protected $dd;

        /*
        * 构造函数
        */
        public function __construct()
        {
            $this->adModel    = new AdModel();
            $this->aboutModel = new AboutModel();
            $this->dd=new Dd();
        }

        public function getAdInfoByPage($page, $page_size, $param)
        {
            $total     = $this->adModel->getAllCounts($param);
            $page      = Star_Page::setPage($page, $page_size, $total);
            $list      = $this->adModel->getList($page, $page_size, $param);
            $page_info = compact('page', 'page_size', 'total');
            $page_data = Star_Page::show($page_info);
            return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
        }


        public function addAd($param)
        {
            return $this->adModel->insert($param);
        }


        public function editAd($id, $param)
        {
            $where = array(
                'ad_id' => $id,
            );
            $re    = $this->adModel->update($where, $param);
            if ($re == 0) {
                return $id;
            }
            return $re;
        }


        public function delAd($id)
        {
            $where = array(
                'ad_id' => $id,
            );
            $param = array(
                'status' => -1,
            );
            return $this->adModel->update($where, $param);
        }


        public function getAdInfo($link_id)
        {
            return $this->adModel->getById($link_id);
        }

        public function getAdCate()
        {
            $about_info = $this->aboutModel->getAboutParent();
            $param      = array(
                array(
                    'id' => 1,
                    'title' => '首页',
                ),
                array(
                    'id' => 2,
                    'title' => '底部',
                ),
            );

            $arr = array(
                '1017', '1041', '1042', '1001',
            );
            foreach ($about_info as $info) {
                if (in_array($info['about_id'], $arr)) {
                    $param[] = array(
                        'id' => $info['about_id'],
                        'title' => $info['about_title'],
                    );
                }
            }
            return $param;
        }


        public function getAdCateInfo()
        {
            $param=array(
                'ad_switch' => 1,
            );
            $info = $this->adModel->getAll($param);
            $arr  = array();
            foreach ($info as $el) {
                $arr[$el['ad_key']] = $el;
            }

            return $arr;
        }


        public function getFooterAd()
        {
            $param=array(
                'ad_switch' => 1,
                'ad_key' => 'H',
            );

            $page=0;
            $page_size=4;
            $ad_info=$this->adModel->getList($page, $page_size, $param);
            foreach($ad_info as &$rs){
              $rs['ad_img']=$this->dd->del_img($rs['ad_img']);
              $arr=explode('.',$rs['ad_img']);
              $rs['is_svg']=end($arr)=='svg'?1:0;
            }
            return $ad_info;
        }


        public function getAll($param=array())
        {
            return   $this->adModel->getAll($param);
        }
    }
