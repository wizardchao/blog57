<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/13
     * Time: 20:17
     */

    class DataDownloadService
    {
        private $dataModel;

        /*
         * 构造函数
         */
        public function __construct()
        {
            $this->dataModel = new DataModel();
        }


        public function getDataInfoByPage($page, $page_size, $param)
        {
            $total     = $this->dataModel->getAllCounts($param);
            $page      = Star_Page::setPage($page, $page_size, $total);
            $list      = $this->dataModel->getDataInfoByPage($page, $page_size, $param);
            $page_info = compact('page', 'page_size', 'total');
            $page_data = Star_Page::show($page_info);
            return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
        }


        public function addData($param)
        {
            return $this->dataModel->insert($param);
        }


        public function editData($id, $param)
        {
            $where = array(
                'data_id' => $id,
            );
            $re    = $this->dataModel->update($where, $param);
            if ($re == 0) return $id;
            return $re;
        }


        public function delData($id)
        {
            $where = array(
                'data_id' => $id,
            );
            $param = array(
                'status' => -1,
            );
            return $this->dataModel->update($where, $param);
        }


        public function getDataInfo($data_id)
        {
            return $this->dataModel->getDataInfoById($data_id);
        }
    }