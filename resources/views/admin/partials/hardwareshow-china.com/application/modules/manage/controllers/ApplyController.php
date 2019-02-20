<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/6/4
     * Time: 16:42
     */
    require APPLICATION_PATH . '/modules/manage/controllers/CommonController.php';

    class ApplyController extends CommonController
    {
        protected $page_size_exhibition;
        protected $page_size_audience;
        protected $applyService;



        public function init()
        {
            parent::init();
            $this->page_size_exhibition = 10;
            $this->page_size_audience   = 10;
            $this->applyService         = new ApplyService();
            $this->exhibitorService = new ExhibitorService();
        }


        // 展位预定列表
        public function exhibition_listAction()
        {
            $req  = $this->getRequest();
            $page = (int)$req->getParam('page'); // page

            // search
            $param = array();
            if ($req->isGet()) {
                $param = array(
                    'company_name' => Star_String::escape($req->getParam('company_name')),
                    'tel' => Star_String::escape($req->getParam('tel')),
                    'mobile' => Star_String::escape($req->getParam('mobile')),
                    'contact' => Star_String::escape($req->getParam('contact')),
                );
            }

            // get list
            $getInfo = $this->applyService->getExhibitionByPage($page, $this->page_size_exhibition, $param);

            // view
            $this->view->assign(
                array(
                    'param' => $param,
                    'list' => $getInfo['list'],
                    'page' => $getInfo['page'],
                )
            );
            $this->render('exhibition_list');
        }


        // 展位预定详情
        public function exhibition_detailAction()
        {
            $req         = $this->getRequest();
            $id          = Star_String::escape($req->getParam('id'));
            $detail_info = $this->applyService->getExhibitionById($id);
            $detail_info['desc'] = explode(',', $detail_info['desc']);
            $detail_info['space'] = explode(',', $detail_info['space']);
            $category_info = $this->exhibitorService->getAllCategoryInfo();
            $space_list=$this->exhibitorService->getSpaceList();
            $p_ex_list=$this->exhibitorService->getPidList();
            // $foodservice     = new FoodService();
            // $other_food_list = $foodservice->getOtherFood();

            $this->view->assign(
                array(
                  'p_ex_list' => $p_ex_list,
                   'space_list' => $space_list,
                    'other_food_list' => $category_info,
                    'param' => $detail_info,
                )
            );
            $this->render('exhibition_detail');
        }


        /*
        * 展位预定 del
        */
        public function exhibition_delAction()
        {
            $req = $this->getRequest();
            $id  = (int)$req->getParam('id');

            $arr = array(
                'id' => $id,
            );
            if ($this->applyService->delExhibition($arr)) {
                header('Location: /manage/apply/exhibition_list');
            }
        }


        // 观众登记列表
        public function audience_listAction()
        {
            $req  = $this->getRequest();
            $page = (int)$req->getParam('page'); // page

            // search
            $param = array();
            if ($req->isGet()) {
                $param = array(
                    'contact' => Star_String::escape($req->getParam('contact')),
                    'tel' => Star_String::escape($req->getParam('tel')),
                    'company' => Star_String::escape($req->getParam('company')),
                );
            }

            // get list
            $getInfo = $this->applyService->getAudienceByPage($page, $this->page_size_audience, $param);

            // view
            $this->view->assign(
                array(
                    'param' => $param,
                    'list' => $getInfo['list'],
                    'page' => $getInfo['page'],
                )
            );
            $this->render('audience_list');
        }


        //  买家邀约详情
        public function audience_detailAction()
        {
            $req         = $this->getRequest();
            $id          = Star_String::escape($req->getParam('id'));
            $detail_info = $this->applyService->getAudienceById($id);

            $this->view->assign(
                array(
                    'param' => $detail_info,
                )
            );
            $this->render('audience_detail');
        }


        // 买家邀约编辑
        public function audience_updateAction()
        {
            $req = $this->getRequest();
            $id  = Star_String::escape($req->getParam('id'));

            if ($req->isPost()) {
                $h_hotel      = Star_String::escape($req->getParam('h_hotel'));
                $h_room       = Star_String::escape($req->getParam('h_room'));
                $h_guest1     = Star_String::escape($req->getParam('h_guest1'));
                $h_guest2     = Star_String::escape($req->getParam('h_guest2'));
                $h_date_start = Star_String::escape($req->getParam('h_date_start'));
                $h_date_end   = Star_String::escape($req->getParam('h_date_end'));
                $h_time       = Star_String::escape($req->getParam('h_time'));
                $h_payment    = Star_String::escape($req->getParam('h_payment'));
                $h_baoxiao    = Star_String::escape($req->getParam('h_baoxiao'));

                $getInfo = $this->applyService->updateAudience(
                    array(
                        'id' => $id,
                    ),
                    array(
                        'h_hotel' => $h_hotel,
                        'h_room' => $h_room,
                        'h_guest1' => $h_guest1,
                        'h_guest2' => $h_guest2,
                        'h_date_start' => $h_date_start,
                        'h_date_end' => $h_date_end,
                        'h_time' => $h_time,
                        'h_payment' => $h_payment,
                        'h_baoxiao' => $h_baoxiao,
                        'h_tm_update' => time(),
                    )
                );
                if ($getInfo) return $this->showMessage('操作成功！', '/manage/apply/audience_list/');
            }
        }


        /*
        * 观众登记 del
        */
        public function audience_delAction()
        {
            $req = $this->getRequest();
            $id  = (int)$req->getParam('id');

            $arr = array(
                'id' => $id,
            );
            if ($this->applyService->delAudience($arr)) {
                header('Location: /manage/apply/audience_list');
            }
        }


    }
