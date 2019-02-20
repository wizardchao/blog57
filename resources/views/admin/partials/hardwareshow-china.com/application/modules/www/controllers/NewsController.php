<?php
  require APPLICATION_PATH . '/modules/www/controllers/BaseController.php';
class NewsController extends BaseController
{
    protected $newsService;
    protected $videoPicService;
    protected $brochureService;
    private $page_size=12;
    protected $dd;

    public function init()
    {
        parent::init();
        $this->newsService=new NewsService();
        $this->videoPicService=new VideoPicService();
        $this->brochureService=new BrochureService();
        $is_show=1;
        $pid=1042;
        $this->clist=$this->newsService->getAllCategory();
        // Dd::dump($this->clist);
        $this->view->assign(
          array(
              'sideBarType' => 1,
              // 'c_list' => $this->clist,
          )
      );
        $this->dd=new Dd();
    }


    public function indexAction()
    {
        $req = $this->getRequest();
        $page = (int)$req->getParam('page'); // page
        $category_id = (int)$req->getParam('category_id');
        $cate_info=array();
        if ($category_id) {
            $cate_info=$this->newsService->getCategoryById($category_id);
        }
        $param=array(
          'category_id' => $category_id?:5,
        );
        $list=$this->newsService->getNewsInfoByPage($page, $this->page_size, $param);

        foreach ($list['list'] as &$rs) {
            $rs['news_img']=$this->dd->del_img($rs['news_img']);
            if ($rs['type']==1) {
                if ($rs['template_name']) {
                    $rs['news_link']='/news/detail/'.$rs['template_name'].'.html';
                } else {
                    $rs['news_link']='/news/detail/id/'.$rs['news_id'].'.html';
                }
            }
        }

        $this->view->assign(
          array(
            'cate_info' => $cate_info,
              'param' => $param,
              'list' => $list['list'],
              'page' => $list['page']
          )
      );

        switch ($category_id) {
            case 7:
                 $this->render('newsletter');
                 break;
            case 11:
                $this->render('newsletter');
                break;
           case 6:
                 $this->render('industry');
                 break;
           default:
               $this->render('index');
     }
    }


    public function multimedia_galleryAction()
    {
        $req = $this->getRequest();
        $page = (int)$req->getParam('page'); // page
        $page_size=12;
        $param=array(
          'category_id' => 1091,
        );
        $list=$this->videoPicService->getVpInfoByPage($page, $page_size, $param);
        foreach ($list['list'] as &$rs) {
            $rs['vp_img']=$this->dd->del_img($rs['vp_img']);
        }

        $cate_info=array(
          'category_name' => '展会掠影',
        );
        $this->view->assign(
         array(
             'cate_info' => $cate_info,
             'param' => $param,
             'list' => $list['list'],
             'page' => $list['page']
         )
     );
        $this->render('multimedia_gallery');
    }


    public function media_partnersAction()
    {
        $cate_info=$this->brochureService->getAllCateInfo();
        $brochure_list=$this->brochureService->getAllBrochure();

        $param=array(
          'category_id' => 1090,
        );

        $list=array();
        foreach ($brochure_list as $el) {
            $list[$el['category_id']][]=$el;
        }
        $c_info=array(
          'category_name' => '合作媒体',
        );
        $this->view->assign(
       array(
            'cate_info' => $c_info,
            'param' => $param,
           'cate_list' => $cate_info,
           'list' => $list,
       )
   );
        $this->render('media_partners');
    }


    public function detailAction()
    {
        $req = $this->getRequest();
        $id = (int)$req->getParam('id');
        $template_name = $req->getParam('template_name');
        if (empty($id) && empty($template_name)) {
            echo '<script>alert("参数为空！");</script>';
            exit;
        }
        if ($id) {
            $info=$this->newsService->getNewsInfoById($id);
        } else {
            $info=$this->newsService->getNewsInfoByTemplate($template_name);
        }

        if (empty($info)) {
            echo '<script>alert("信息为空！");history.back();</script>';
            exit;
        }

        $info['news_content']=$this->dd->deal_desc($info['news_content']);
        $info['cate_info']=$this->newsService->getCategoryById($info['category_id']);
        $this->view->assign(
     array(
         'info' => $info,
          'param' => array('category_id' => $info['category_id']),
     )
 );
        $this->render('detail');
    }
}
