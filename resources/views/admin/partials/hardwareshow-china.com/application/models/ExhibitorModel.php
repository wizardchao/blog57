<?php
/**
 * Created by PhpStorm.
 * User: Chaos
 * Date: 2018/1/25
 * Time: 15:40
 */


class ExhibitorModel extends Star_Model_Abstract
{

    protected $_name = 'exhibitor';

    protected $_primary = 'id';


    /*
     * 根据news_id返回结果
     */
    public function getInfoById($id)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('id =?', $id)
            ->where('status >=?', 1);
        return $this->fetchRow($select);
    }


    public function getInfoByName($name)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('title =?', $name)
            ->where('status >=?', 0);
        return $this->fetchRow($select);
    }



    public function getListByCategoryName($name){
        $select = $this->select();
        $select->from($this->getTableName() ." AS n")
            ->where('c.status >=?', 0)
            ->joinInner($this->getTableName('exhibitor_category') ." AS c","c.exhibitor_category_id = n.exhibitor_category_id")
            ->where('c.category_name =?', $name);
        return $this->fetchAll($select);

    }



    public function getListByCategoryId($id){
        $select = $this->select();
        $select->from($this->getTableName() ." AS n")
            ->where('c.status >=?', 0)
            ->joinInner($this->getTableName('exhibitor_category') ." AS c","c.exhibitor_category_id = n.exhibitor_category_id")
            ->where('c.exhibitor_category_id =?', $id);
        return $this->fetchAll($select);

    }

    /**
     * 返回有关信息
     *
     * @param type $page
     * @param type $page_size
     * @param type $params
     * @return type
     */
    public function getInfoByPage($page, $page_size, Array $param)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('status >=?', 1);

        if ($param) {
            if (isset($param['id']) && $param['id']) {
                $select->where('id =?', $param['id']);
            }
            if (isset($param['exhibitor_category_id']) && $param['exhibitor_category_id']) {
                $select->where('exhibitor_category_id =?', $param['exhibitor_category_id']);
            }
            if (isset($param['name']) && $param['name']) {
                $select->where('name like "%'.$param['name'].'%" or name_en like "%'.$param['name'].'%"');
            }
            if (isset($param['year']) && $param['year']) {
                $select->where('year =?', $param['year']);
            }

        }

        $select->limitPage($page, $page_size)->order(array('sort_id asc', 'id DESC'));
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

        if ($param) {
            if (isset($param['id']) && $param['id']) {
                $select->where('id =?', $param['id']);
            }
            if (isset($param['exhibitor_category_id']) && $param['exhibitor_category_id']) {
                $select->where('exhibitor_category_id =?', $param['exhibitor_category_id']);
            }
            if (isset($param['title']) && $param['title']) {
                $select->where('title like ?', '%' . $param['title'] . '%');
            }

            if (isset($param['name']) && $param['name']) {
                $select->where('name like "%'.$param['name'].'%" or name_en like "%'.$param['name'].'%"');
            }

            if (isset($param['year']) && $param['year']) {
                $select->where('year =?', $param['year']);
            }
        }

        return $this->fetchOne($select);
    }


    /*
     * 获取id
     */
    public function getIdAByTitle($title)
    {
        $select = $this->select();
        $select->from($this->getTableName(), "id")
            ->where('status >=?', 1)
            ->where('title =?', $title)
            ->limit(1);
        return $this->fetchOne($select);
    }


    /*
     * 获取最新一条新闻
     */
    public function getLastNews()
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('status >=?', 1)
            ->limit(1);
        return $this->fetchRow($select);
    }


}
