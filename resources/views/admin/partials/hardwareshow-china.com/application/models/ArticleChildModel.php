<?php
/*
* author: chason
* date: 2018-08-13
*/
class ArticleChildModel extends Star_Model_Abstract
{

    protected $_name = 'article_child';
    protected $_primary = 'article_child_id';


    /**
     * 通过id获取信息
     * @param $about_id
     * @return type
     */
    public function getById($id)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('article_child_id =?', $id)
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
    public function getList($page, $page_size, Array $params)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('status >=?', 1);

        if (isset($params['article_id']) && $params['article_id']) {
            $select->where('article_id =?', $params['article_id']);
        }

        $select->limitPage($page, $page_size)->order('sort_id asc', 'article_child_id DESC');
        return $this->fetchAll($select);
    }


    /**
     * 返回所有信息
     */
    public function getAll($params)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('status >=?', 1);

        if (isset($params['article_id']) && $params['article_id']) {
            $select->where('article_id =?', $params['article_id']);
        }

        $select->order("sort_id asc", "article_child_id DESC");
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
        if (isset($param['article_id']) && $param['article_id']) {
            $select->where('article_id =?', $param['article_id']);
        }
        return $this->fetchOne($select);
    }


}