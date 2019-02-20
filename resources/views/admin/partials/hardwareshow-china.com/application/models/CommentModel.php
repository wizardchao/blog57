<?php
/**
 * Created by PhpStorm.
 * User: Chaos
 * Date: 2018/1/25
 * Time: 15:40
 */


class CommentModel extends Star_Model_Abstract
{

    protected $_name = 'news_comment';

    protected $_primary = 'comment_id';

    public function insertComment($comment_id)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('comment_id =?', $comment_id)
            ->where('status >=?', 1);
        return $this->fetchRow($select);
    }

    /*
     * 根据comment_id返回结果
     */
    public function getCommentInfoById($comment_id)
    {
        $select = $this->select();
        $select->from($this->getTableName())
            ->where('comment_id =?', $comment_id)
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
    public function getNewsCommentByPage($page, $page_size, Array $param)
    {

        $select = $this->select();
        $select->from($this->getTableName())
            ->where('status >=?', 0)
            ->where('reply_id =?', 0);
        if ($param) {
            if (isset($param['news_id']) && $param['news_id']) {
                $select->where('news_id =?', $param['news_id']);
            }
            if (isset($param['comment_id']) && $param['comment_id']) {
                $select->where('comment_id =?', $param['comment_id']);
            }
        }

        $select->limitPage($page, $page_size)->order(array('time_create DESC', 'news_id ASC'));
        return $this->fetchAll($select);
    }


    /**
     * @param $page
     * @param $page_size
     * @param array $param
     * @return type
     */
    public function getCommentReplyByPage($page, $page_size, Array $param)
    {

        $select = $this->select();
        $select->from($this->getTableName())
            ->where('status >=?', 0)
            ->where('reply_id >?', 0);
        if ($param) {
            if (isset($param['news_id']) && $param['news_id']) {
                $select->where('news_id =?', $param['news_id']);
            }
            if (isset($param['reply_id']) && $param['reply_id']) {
                $select->where('reply_id =?', $param['reply_id']);
            }
            if (isset($param['comment_id']) && $param['comment_id']) {
                $select->where('comment_id =?', $param['comment_id']);
            }
            if (isset($param['flag']) && $param['flag']) {
                if ($param['flag'] == 1) {
                    $select->where('reply_id =?', 0);
                } else {
                    $select->where('reply_id >=', 1);
                }
            }
        }

        $select->limitPage($page, $page_size)->order(array('time_create DESC', 'news_id ASC'));
        return $this->fetchAll($select);
    }


    /*
    * 取出总数
    */
    public function getAllCounts($param = NULL)
    {

        $select = $this->select();
        $select->from($this->getTableName(), "COUNT(1)")
            ->where('status >=?', 0);
        if ($param) {
            if (isset($param['news_id']) && $param['news_id']) {
                $select->where('news_id =?', $param['news_id']);
            }
            if (isset($param['reply_id'])) {
                $select->where('reply_id =?', $param['reply_id']);
            }
            if (isset($param['comment_id']) && $param['comment_id']) {
                $select->where('comment_id =?', $param['comment_id']);
            }

        }
        return $this->fetchOne($select);
    }


    public function getCommentCounts($obj)
    {
        $select = $this->select();
        $select->from($this->getTableName(), "COUNT(1)")
            ->where('news_id =?', $obj['news_id'])
            ->where('status >=?', 0)
            ->where('reply_id =?', 0);
        return $this->fetchOne($select);
    }

    public function getCommentReplyCounts($obj)
    {
        $select = $this->select();
        $select->from($this->getTableName(), "COUNT(1)")
            ->where('news_id =?', $obj['news_id'])
            ->where('status >=?', 0)
            ->where('reply_id =?',$obj['reply_id'] );
        return $this->fetchOne($select);
    }



}

?>