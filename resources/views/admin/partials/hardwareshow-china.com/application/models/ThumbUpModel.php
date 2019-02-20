<?php
/**
 * Created by PhpStorm.
 * User: Chaos
 * Date: 2018/1/25
 * Time: 15:40
 */


class ThumbUpModel extends Star_Model_Abstract
{

    protected $_name = 'news_thumb_up';

    protected $_primary = 'up_id';

    /*
     * 根据up_id返回结果
     */
    public function getThumbUpInfoById($up_id)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('up_id =?', $up_id)
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
    public function getThumbUpByPage($page, $page_size, Array $param)
    {

        $select = $this->select();
        $select->from($this->getTableName())
            ->where('status >=?', 0);
        if ($param) {
            if (isset($param['news_id']) && $param['news_id']) {
                $select->where('news_id =?', $param['news_id']);
            }

            if (isset($param['comment_id']) ) {
                $select->where('comment_id =?', $param['comment_id']);
            }
            if (isset($param['up_id']) && $param['up_id']) {
                $select->where('up_id =?', $param['up_id']);
            }
            if (isset($param['up_type']) && $param['up_type']) {
                $select->where('up_type =?', $param['up_type']);
            }
        }
        $select->limitPage($page, $page_size)->order(array('time_update DESC', 'time_create DESC'));
        return $this->fetchAll($select);
    }


    /*
    * 取出总数
    */
    public function getAllCount($param = NULL)
    {
        //print_r($param);exit;
        $select = $this->select();
        $select->from($this->getTableName(), "COUNT(1)")
            ->where('status >=?', 0);
        if ($param) {
            if (isset($param['news_id']) && $param['news_id']) {
                $select->where('news_id =?', $param['news_id']);
            }
            if (isset($param['comment_id'])) {
                $select->where('comment_id =?', $param['comment_id']);
            }
            if (isset($param['up_id']) && $param['up_id']) {
                $select->where('up_id =?', $param['up_id']);
            }
            if (isset($param['up_type']) && $param['up_type']) {
                $select->where('up_type =?', $param['up_type']);
            }

        }
        return $this->fetchOne($select);
    }


    public function getThumbCounts($obj)
    {
        $select = $this->select();
        $select->from($this->getTableName(), "COUNT(1)")
            ->where('news_id =?', $obj['news_id'])
            ->where('status >=?', 0);
        if ($obj['flag'] == 1) {
            $select->where('comment_id =?', 0);
        }
        if ($obj['reply_id']) {
            $select->where('comment_id =?', $obj['reply_id']);
        }

        if( isset($obj['up_type']) && $obj['up_type']){
            $select->where('up_type =?', $obj['up_type']);
        }
        return $this->fetchOne($select);
    }


}

?>