<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/13
     * Time: 22:09
     */

    class VideoPicService
    {
        private $vpModel;

        /*
         * 构造函数
         */
        public function __construct()
        {
            $this->vpModel = new VideoPicModel();
        }


        public function getVpInfoByPage($page, $page_size, $param)
        {
            $total     = $this->vpModel->getAllCounts($param);
            $page      = Star_Page::setPage($page, $page_size, $total);
            $list      = $this->vpModel->getVpInfoByPage($page, $page_size, $param);
            $page_info = compact('page', 'page_size', 'total');
            $page_data = Star_Page::show($page_info);
            return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
        }


        public function addVp($param)
        {
            return $this->vpModel->insert($param);
        }


        public function editVp($id, $param)
        {
            $where = array(
                'vp_id' => $id,
            );
            $re    = $this->vpModel->update($where, $param);
            if ($re == 0) return $id;
            return $re;
        }


        public function delVp($id)
        {
            $where = array(
                'vp_id' => $id,
            );
            $param = array(
                'status' => -1,
            );
            return $this->vpModel->update($where, $param);
        }


        public function getVpInfo($id)
        {
            return $this->vpModel->getVpInfoById($id);
        }

    }