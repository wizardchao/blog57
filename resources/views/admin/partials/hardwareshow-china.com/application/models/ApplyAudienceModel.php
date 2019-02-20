<?php
/*
* author: chason
* date: 2018-08-13
*/
class ApplyAudienceModel extends Star_Model_Abstract
{

    protected $_name = 'apply_audience';
    protected $_primary = 'id';


    /**
     * 通过id获取信息
     * @param $id
     * @return type
     */
    public function getById($id)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('id =?', $id)
            ->where('status >=?', 1);
        return $this->fetchRow($select);
    }


    /**
     * 返回有关信息
     *
     * @param type $page
     * @param type $page_size
     * @param type $params
     * @return type
     */
    public function getList($page, $page_size, Array $param)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('status >=?', 1);

        if (isset($param['tel']) && $param['tel']) {
            $select->where('tel =?', $param['tel']);
        }

        if (isset($param['company']) && $param['company']) {
            $select->where('company like ?', '%'.$param['company'].'%');
        }

        if (isset($param['contact']) && $param['contact']) {
            $select->where('contact like ?', '%'.$param['contact'].'%');
        }

        $select->limitPage($page, $page_size)->order('id DESC');
        return $this->fetchAll($select);
    }


    /**
     * 返回所有信息
     */
    public function getAll(Array $param)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('status >=?', 1);

        if (isset($param['tel']) && $param['tel']) {
            $select->where('tel =?', $param['tel']);
        }

        if (isset($param['company']) && $param['company']) {
            $select->where('company like ?', '%'.$param['company'].'%');
        }

        if (isset($param['contact']) && $param['contact']) {
            $select->where('contact like ?', '%'.$param['contact'].'%');
        }

        $select->order("id DESC");
        return $this->fetchAll($select);
    }


    /*
    * 取出总数
     */
    public function getAllCounts($param = NULL)
    {
        $select = $this->select();
        $select->from($this->getTableName(), "COUNT(1)")
            ->where('status >=?', 1);
        if (isset($param['tel']) && $param['tel']) {
            $select->where('tel =?', $param['tel']);
        }

        if (isset($param['company']) && $param['company']) {
            $select->where('company like ?', '%'.$param['company'].'%');
        }

        if (isset($param['contact']) && $param['contact']) {
            $select->where('contact like ?', '%'.$param['contact'].'%');
        }
        return $this->fetchOne($select);
    }


}