<?php
/**
 * Created by PhpStorm.
 * User: Chris_Chiang
 * Date: 2018/7/30
 * Time: 11:00
 */
class EmailController extends Star_Controller_Action
{
    protected $page_size;

    public function init()
    {
        parent::init();
    }
    public function sendAction(){

        $req=$this->getRequest();
        if($req->isPost()){
            $data=$req->getParams();
            $title=$data['title'];
            $content=$data['content'];
            $to=$data['to'];
            $em=new Email();
            try{
                $em->send($title, $content,$to);
            }catch(Exception $e){
                return strval($e->getMessage());
            }


        }

    }



}


