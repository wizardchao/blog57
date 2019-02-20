<?php
  require APPLICATION_PATH . '/modules/www/controllers/BaseController.php';
class IndexController extends BaseController
{
    protected $homeService;
    protected $aboutService;
    protected $brochureService;
    protected $subscriptionService;
    private $page_size=10;
    private $page=0;

    public function init()
    {
        parent::init();
        $this->homeService=new HomeService();
        $this->aboutService=new AboutService();
        $this->brochureService=new BrochureService();
        $this->dataDownloadService=new dataDownloadService();
        $this->subscriptionService = new SubscriptionService();
    }


    public function indexAction()
    {
        $param=array(
        'banner_type' => 1,
      );
        $banner_list=$this->homeService->getBannerInfoByPage($this->page, $this->page_size, $param);
        $ad_list=$this->homeService->getAdList();
        $vistor_list=$this->homeService->getHomeVisitorInfo();
        $brochure_list=$this->brochureService->getAllBrochure();
        // $data_list=$this->dataDownloadService->getDataInfoByPage($this->page, 5, array());
        $link_list=$this->linkService->getLinkInfoByPage($this->page, 100,array('link_category_id' => 1002,'link_position_id' => 2,));
// Dd::dump($link_list);
        $this->view->assign(
          array(
              'about_list' => $about_list['list'],
              'param' => $param,
              'banner_list' => ($banner_list['list'])?$banner_list['list']:array(),
              'ad_list' => $ad_list,
              'vistor_list' => $vistor_list,
              'brochure_list' => $brochure_list,
              // 'data_list' => $data_list['list'],
              'home_link_list' => $link_list['list'],
          )
      );
        $this->render('index');
    }


    public function subscribeAction()
    {
        $request            = $this->getRequest();
        $visitor_type  = (int)Star_String::escape($request->getParam('visitor-type'))?:2;
        $subscription_email = trim(Star_String::escape($request->getParam('email')));
        if (empty($visitor_type)) {
            echo "<script>alert('请选择游客类型!');history.back();</script>";
            exit;
        }

        if (!in_array($visitor_type, array(1,2))) {
            echo "<script>alert('游客类型错误!');history.back();</script>";
            exit;
        }

        if (empty($subscription_email)) {
            echo "<script>alert('请填写邮箱地址!');history.back();</script>";
            exit;
            //                return $this->showJson(202, '邮箱未填写！');
        }

        if (!Star_String::isEmail($subscription_email)) {
            echo "<script>alert('邮箱格式错误!');history.back();</script>";
            exit;
            //                return $this->showJson(203, '邮箱格式不正确！');
        }

        $email_count = $this->subscriptionService->getCountByEmail($subscription_email);
        if ($email_count >= 1) {
            echo "<script>alert('你已订阅过!');history.back();</script>";
            exit;
            //                return $this->showJson(222, '你已订阅过！');
        }

        $time_create         = time();
        $status              = 1;
        $subscription_ip     = $_SERVER['REMOTE_ADDR'];
        $subscription_device = $_SERVER['HTTP_USER_AGENT'];
        $param               = compact('visitor_type', 'subscription_email', 'time_create', 'status', 'subscription_ip', 'subscription_device');
        $re                  = $this->subscriptionService->saveDescriptionRecord($param);

        if ($re) {
            $send_re = $this->subscriptionService->sendEmail($subscription_email);
            if ($send_re) {
                echo "<script>alert('已成功订阅邮件!');history.back();</script>";
                exit;
            }
        }

        echo "<script>alert('您未能成功订阅邮件!');history.back();</script>";
        exit;
    }
}
