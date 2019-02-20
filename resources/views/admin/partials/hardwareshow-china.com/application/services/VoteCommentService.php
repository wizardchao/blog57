<?php
/**
 * Created by PhpStorm.
 * User: Chris_Chiang
 * Date: 2018/6/29
 * Time: 12:55
 */
class VoteCommentService
{
    /*
    * 构造函数
    */
    public function __construct()
    {
        $this->news_model = new NewsModel();
        $this->thumb_up_model = new ThumbUpModel();
        $this->comment_model = new CommentModel();
        $this->utilHelpers = new UtilsHelper();
    }


    public function addNewsComment($data){
        $data['reply_id']=0;
        //print_r($data);exit;
        return $this->comment_model->insert($data);
    }



    public function addCommentReply($data){
        return $this->comment_model->insert($data);
    }


    public function addVote($data){
        //需要预留查重逻辑，一个人不能同时对同一篇文章多次点赞。
        if(isset($data['thumb_up_counts'])){
            $data['up_type']=1;
           unset($data['thumb_up_counts']) ;
        }else if(isset($data['thumb_down_counts'])){
            $data['up_type']=2;
            unset($data['thumb_down_counts']) ;
        }else{
            $data['up_type']=1;
        }
        return $this->thumb_up_model->insert($data);
    }

}
