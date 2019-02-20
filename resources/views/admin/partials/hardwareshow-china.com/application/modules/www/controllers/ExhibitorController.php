<?php

require APPLICATION_PATH . '/modules/www/controllers/BaseController.php';
class ExhibitorController extends BaseController
{
    protected $exhibitorService;
    protected $aboutService;
    private $page_size=10;
    public function init()
    {
        parent::init();
        $this->exhibitorService=new ExhibitorService();
        $this->aboutService=new AboutService();
        $this->view->assign(
          array(
              'sideBarType' => 2,
          )
      );
        $this->dd=new Dd();
    }


    public function indexAction()
    {
        $request = $this->getRequest();
        $page = (int)$request->getParam('page'); // page
        $name = $request->getParam('name');
        $exhibitor_category_id = (int)$request->getParam('exhibitor_category_id');
        $param=compact('name', 'exhibitor_category_id');
        $list=$this->exhibitorService->getInfoByPage($page, $this->page_size, $param);
        $category_data = $this->exhibitorService->getFirstCateByPage($page, 10);
        $this->exhibitorService->addICon($category_data['list']);

        $this->view->assign(
            array(
              'clist' => $category_data['list'],
                'list' => $list['list'],
                'page' => $list['page'],
            )
        );
        $this->render('index');
    }



    public function product_listAction()
    {
        $list=  $this->exhibitorService->getCategoryByPid(0);
        foreach ($list as &$el) {
            $el['child']=$this->exhibitorService->getCategoryByPid($el['exhibitor_category_id']);
        }
        // Dd::dump($list);
        $this->view->assign(
           array(
               'list' => $list,
               'param' => array(
               'category_id' => 1,
               ),
           )
       );
        $this->render('product_list');
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


    public function listAction(){
      $request = $this->getRequest();
      $page = (int)$request->getParam('page'); // page
      $list=$this->homeService->getGlobalFoodInfoByPage($page, $this->page_size, array());
      $re_list=$list['list'];
      foreach($re_list as &$rs){
        $rs['globalfood_img']=$this->dd->del_img($rs['globalfood_img']);
      }
      $this->view->assign(
      array(
          'list' => $re_list,
          'page' => $list['page'],
      )
  );
  // Dd::dump($list);
      $this->render('list');
    }
}
