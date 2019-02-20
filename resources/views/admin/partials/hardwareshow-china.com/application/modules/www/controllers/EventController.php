<?php

require APPLICATION_PATH . '/modules/www/controllers/BaseController.php';
class EventController extends BaseController
{
    protected $activityService;
    protected $aboutService;
    private $page_size=10;
    protected $dd;
    private $sideBarType=3;  //活动

    public function init()
    {
        parent::init();
        $this->activityService=new ActivityService();
        $this->aboutService=new AboutService();
        $this->dd=new Dd();

        $this->view->assign(
          array(
              'sideBarType' =>  $this->sideBarType,
          )
      );
    }


    public function indexAction()
    {
        $request = $this->getRequest();
        $page = (int)$request->getParam('page'); // page
        $activity_category_id = (int)$request->getParam('activity_category_id');
        $param=array(
          'activity_category_id' => $activity_category_id?:1001,
        );
        $list=$this->activityService->getActivityPage($page, $this->page_size, $param);
        foreach($list['list'] as &$val){
          $val['img']=$this->dd->del_img($val['img']);
        }
       $param['category_id']=$param['activity_category_id'];
       $arr=array(
         1001 => '行业交流会',
         1002 => '商务对接',
         1003 => '主题展区',
       );
       $param['title']=$arr[$param['category_id']];
        $this->view->assign(
          array(
              'list' => $list['list'],
              'page' => $list['page'],
              'param' => $param,
          )
      );
        $this->render('index');
    }


    public function descAction()
    {
        $request = $this->getRequest();
        $about_id = (int)$request->getParam('about_id'); // page
        if (empty($about_id)) {
            echo '<script>alert("参数为空！");</script>';
            exit;
        }
        $about_info=$this->aboutService->getAboutById($about_id);
        if (empty($about_info)) {
            echo '<script>alert("信息为空！");</script>';
            exit;
        }

        $about_info['about_content']=$this->dd->deal_desc($about_info['about_content']);
        $p_list=$this->aboutService->getParents($about_info['about_relation']);
        $is_show=1;
        $c_list=$this->aboutService->getSecondChild($about_info['pid'], $is_show);

        $this->view->assign(
        array(
            'c_list' => $c_list,
            'p_list' => $p_list,
            'info' => $about_info,
            'param' => array(
              'category_id' => $about_id,
            ),
        )
    );
        $this->render('desc');
    }


    public function detailAction()
    {
        $req = $this->getRequest();
        $id = (int)$req->getParam('id'); // id
        $template_name = $req->getParam('template_name');
        if (empty($id) && empty($template_name)) {
            echo '<script>alert(“参数不能为空！”);history.back();</script>';
            exit;
        }
        $info=$this->activityService->getActivityById($id);
        if (empty($info)) {
            echo '<script>alert(“信息为空！”);history.back();</script>';
            exit;
        }
        $info['desc']=$this->dd->deal_desc($info['desc']);
        $info['banner_img']=$this->dd->del_img($info['banner_img']);
        $info['cate_info']=$this->activityService->getCategoryById($info['activity_category_id']);
        $this->view->assign(
          array(
              'info' => $info,
              'param' => array(),
             )
         );
        $this->render('detail');
    }
}
