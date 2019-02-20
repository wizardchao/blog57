<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/15
     * Time: 9:41
     */

    class VisitorService
    {
        protected $visitorModel;


        /*
        * 构造函数
        */
        public function __construct()
        {
            $this->visitorModel    = new VisitorModel();
        }


        public function getVisitorInfoByPage($page, $page_size, $param)
        {
            $total     = $this->visitorModel->getAllCounts($param);
            $page      = Star_Page::setPage($page, $page_size, $total);
            $list      = $this->visitorModel->getVisitorInfoByPage($page, $page_size, $param);
            $page_info = compact('page', 'page_size', 'total');
            $page_data = Star_Page::show($page_info);
            return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
        }


        public function addVisitor($param)
        {
            return $this->visitorModel->insert($param);
        }


        public function editVisitor($id, $param)
        {
            $where = array(
                'visitor_id' => $id,
            );
            $re    = $this->visitorModel->update($where, $param);
            if ($re == 0) return $id;
            return $re;
        }


        public function delVisitor($id)
        {
            $where = array(
                'visitor_id' => $id,
            );
            $param = array(
                'status' => -1,
            );
            return $this->visitorModel->update($where, $param);
        }


        public function getVisitorInfo($link_id)
        {
            return $this->visitorModel->getVisitorInfoById($link_id);
        }
    }