<?php


    require APPLICATION_PATH . '/modules/manage/controllers/CommonController.php';

    class WebSetController extends CommonController
    {
        protected $web_set_service;
        public function init()
        {
            parent::init();
            $this->web_set_service = new WebSetService();
        }


        /**
         * 系统设置方法
         */
        public function systemAction(){

            $request = $this->getRequest();
            if ($request->isGet()) {

                $gets = $request->getParams();
                foreach ($gets as $key => &$value) {
                    $value = Star_String::escape($value);
                }

                if(isset($gets['set'])){
                    switch ($gets['set']) {
                        case "base":
                            $this->renders($gets='base');
                            break;
                        case 'secure':
                            $this->renders($gets='secure');
                            break;
                        case 'email':
                            $this->renders($gets='email');
                            break;
                        case 'baidumap':
                            $this->renders($gets='baidumap');
                            break;
                        case 'qiniu':
                            $this->renders($gets='qiniu');
                            break;
                    }
                }else{
                   $this->renders($gets='base');
                }
            }elseif($request->isPost()){
                $posts = $request->getParams();
                foreach ($posts as $key => &$value) {
                    $value = Star_String::escape($value);
                }
                 $res=$this->web_set_service->saveSyetemInfo($posts);
                 $param=$res;
               if($res) {
                   $this->view->assign(
                       array(
                           'param' => $param,
                           'on'=>$posts['set_info']
                       ));

                   $this->render($posts['set_info'].'_info');
               }


            }

        }


        /**
         * @param $gets
         * @return Star_Controller_Action
         * 渲染模板
         */
        public function renders($gets){
            $res=$this->web_set_service->getSyetemInfo($gets);
            //print_r($res);exit;
            if(isset($res)&&!empty($res)){
                $param=$res;
            }else{
                $param=array();
            }

            $this->view->assign(
                array(
                    'param' => $param,
                    'on'=>$gets
                ));
            return $this->render($gets.'_info');
        }

    }