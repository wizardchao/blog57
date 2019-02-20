<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/8/13
     * Time: 23:03
     */

    class LinkService
    {
        private $linkModel;

        /*
         * 构造函数
         */
        public function __construct()
        {
            $this->linkModel = new LinkModel();
        }


        public function getLinkInfoByPage($page, $page_size, $param)
        {
            $total     = $this->linkModel->getAllCounts($param);
            $page      = Star_Page::setPage($page, $page_size, $total);
            $list      = $this->linkModel->getLinkInfoByPage($page, $page_size, $param);
            $page_info = compact('page', 'page_size', 'total');
            $page_data = Star_Page::show($page_info);
            return array( 'page' => $page_data, 'total' => $total, 'list' => $list );
        }


        public function addLink($param)
        {
            return $this->linkModel->insert($param);
        }


        public function editLink($id, $param)
        {
            $where = array(
                'link_id' => $id,
            );
            $re    = $this->linkModel->update($where, $param);
            if ($re == 0) return $id;
            return $re;
        }


        public function delLink($id)
        {
            $where = array(
                'link_id' => $id,
            );
            $param = array(
                'status' => -1,
            );
            return $this->linkModel->update($where, $param);
        }


        public function getLinkInfo($link_id)
        {
            return $this->linkModel->getLinkInfoById($link_id);
        }
    }