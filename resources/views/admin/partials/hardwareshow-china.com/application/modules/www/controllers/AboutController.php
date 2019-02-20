<?php
  require APPLICATION_PATH . '/modules/www/controllers/BaseController.php';
class AboutController extends BaseController
{
    private $page_size=10;
    private $page=0;
    protected $aboutService;
    protected $exhibitionService;
    protected $applyService;

    public function init()
    {
        parent::init();
        $this->aboutService=new AboutService;
        $this->exhibitionService=new ExhibitorService();
        $this->applyService=new ApplyService();
    }


    public function indexAction()
    {
        $request = $this->getRequest();
        $page = (int)$request->getParam('page'); // page
        $about_id = (int)$request->getParam('id'); // page
        $template_name=$request->getParam('template_name');

        if (empty($about_id) && empty($template_name)) {
            echo '<script>alert("参数为空！");</script>';
            exit;
        }
        $about_info=$about_id?$this->aboutService->getAboutById($about_id):$this->aboutService->getAboutInfoByTemplate($template_name);
        if (empty($about_info)) {
            echo '<script>alert("信息为空！");</script>';
            exit;
        }

        if ($about_info['link'] && $about_info['type']==2) {
            $this->redirect($about_info['link']);
        }

        $about_info['about_content']=$this->dd->deal_desc($about_info['about_content']);
        if ($about_info['template']==2) {
            $about_info['banner_img']=$this->dd->del_img($about_info['banner_img']);
            $about_info['banner_m_img']=$this->dd->del_img($about_info['banner_m_img']);
            $about_info['banner_pad_img']=$this->dd->del_img($about_info['banner_pad_img']);
        }
        $p_list=$this->aboutService->getParents($about_info['about_relation']);
        $is_show=1;
        $c_list=$this->aboutService->getSecondChild($about_info['pid'], $is_show);
        $this->view->assign(
          array(
              'c_list' => $c_list,
              'p_list' => $p_list,
              'info' => $about_info,
          )
      );
        // Dd::dump($about_info);
        if ($about_info['template']==1) {
            $this->render('index');
        } else {
            $this->render('cihs');
        }
    }


    public function applyAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $company_name=Star_String::escape($request->getParam('company_name'));
            $area=Star_String::escape($request->getParam('area'));
            $country=Star_String::escape($request->getParam('country'));
            $address=Star_String::escape($request->getParam('address'));
            $contact=Star_String::escape($request->getParam('contact'));
            $mobile=Star_String::escape($request->getParam('mobile'));
            $gender=(int)Star_String::escape($request->getParam('gender'))?:1;
            $tel=Star_String::escape($request->getParam('tel'));
            $email=Star_String::escape($request->getParam('email'));
            $other=Star_String::escape($request->getParam('other'));
            $desc=$request->getParam('desc');
            $space=$request->getParam('space');

            if (empty($company_name)) {
                echo '<script>alert("请填写公司信息！");history.back();</script>';
                exit;
            }

            if (empty($area)) {
                echo '<script>alert("请填写城市信息！");history.back();</script>';
                exit;
            }

            if (empty($country)) {
                echo '<script>alert("请填写国家信息！");history.back();</script>';
                exit;
            }

            if (empty($address)) {
                echo '<script>alert("请填写地址信息！");history.back();</script>';
                exit;
            }
            if (empty($contact)) {
                echo '<script>alert("请填写联系人信息！");history.back();</script>';
                exit;
            }
            if (empty($tel)) {
                echo '<script>alert("请填写电话信息！");history.back();</script>';
                exit;
            }

            if (empty($email)) {
                echo '<script>alert("请填写邮箱信息！");history.back();</script>';
                exit;
            }

            if (!Star_String::isEmail($email)) {
                echo '<script>alert("请填写正确的邮箱格式！");history.back();</script>';
                exit;
            }


            $submit_ip=$_SERVER['REMOTE_ADDR'];
            $tm_create=$tm_update=time();
            $status=1;
            $desc=implode(',', $desc);
            $space=implode(',', $space);
            $param=compact('mobile','gender','contact','space','desc', 'status', 'submit_ip', 'email', 'tel', 'address', 'country', 'area', 'address', 'company_name', 'tm_update', 'tm_create', 'other');
            $re=$this->applyService->insertExhibition($param);
            if ($re) {
                echo '<script>alert("您已提交申请，请稍后！");history.back();</script>';
                exit;
            }
        }

        $about_id = 1060; // page
        $about_info=$this->aboutService->getAboutById($about_id);
        if (empty($about_info)) {
            echo '<script>alert("信息为空！");</script>';
            exit;
        }


        $about_info['about_content']=$this->dd->deal_desc($about_info['about_content']);
        $p_list=$this->aboutService->getParents($about_info['about_relation']);
        $is_show=1;
        $c_list=$this->aboutService->getSecondChild($about_info['pid'], $is_show);


        $exhibitor_cate_list=$this->exhibitionService->getAllCategoryInfo();
        // Dd::dump($exhibitor_cate_list);
        $p_ex_list=$this->exhibitionService->getPidList();
        $space_list=$this->exhibitionService->getSpaceList();
        $this->view->assign(
        array(
           'space_list' => $space_list,
          'p_ex_list' => $p_ex_list,
            'exhibitor_cate_list' => $exhibitor_cate_list,
            'c_list' => $c_list,
            'p_list' => $p_list,
            'info' => $about_info,
        )
    );
        $this->render('apply');
    }


    public function regAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $contact          = Star_String::escape($request->getParam('contact'));
            $gender           = Star_String::escape($request->getParam('gender'));
            $tel              = Star_String::escape($request->getParam('tel'));
            $email            = Star_String::escape($request->getParam('email'));
            $company          = Star_String::escape($request->getParam('company'));
            $position         = Star_String::escape($request->getParam('position'));
            $website          = Star_String::escape($request->getParam('website'));
            $company_purchase = Star_String::escape($request->getParam('company_purchase'));
            $company_business = $request->getParam('company_business') ? implode(',', $request->getParam('company_business')) : '';
            $purchase         = $request->getParam('purchase') ? implode(',', $request->getParam('purchase')) : '';
            $submit_ip        = $_SERVER['REMOTE_ADDR'];

            $param  = array(
            'contact' => $contact,
            'gender' => $gender,
            'tel' => $tel,
            'email' => $email,
            'company' => $company,
            'position' => $position,
            'website' => $website,
            'company_purchase' => $company_purchase,
            'company_business' => $company_business,
            'purchase' => $purchase,
            'tm_create' => time(),
            'submit_ip' => $submit_ip,
            'status' => 1,
        );
            $result = $this->applyService->insertAudience($param);
            if ($result) {
                echo '<script>alert("提交成功！");location.href="/about/reg/";</script>';
            }
            exit;
        }
        $about_id = 1069; // page
        $about_info=$this->aboutService->getAboutById($about_id);
        if (empty($about_info)) {
            echo '<script>alert("信息为空！");</script>';
            exit;
        }

        $about_info['about_content']=$this->dd->deal_desc($about_info['about_content']);
        $p_list=$this->aboutService->getParents($about_info['about_relation']);
        $is_show=1;
        $c_list=$this->aboutService->getSecondChild($about_info['pid'], $is_show);


        $exhibitor_cate_list=$this->exhibitionService->getAllCategoryInfo();

        $this->view->assign(
      array(
          'exhibitor_cate_list' => $exhibitor_cate_list,
          'c_list' => $c_list,
          'p_list' => $p_list,
          'info' => $about_info,
      )
  );
        $this->render('reg');
    }
}
