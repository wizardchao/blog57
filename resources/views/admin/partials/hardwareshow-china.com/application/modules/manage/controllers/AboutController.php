<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/6/4
     * Time: 16:42
     */

    require APPLICATION_PATH . '/modules/manage/controllers/CommonController.php';

    class AboutController extends CommonController
    {
        protected $page_size;
        protected $aboutService;

        public function init()
        {
            parent::init();
            $this->page_size    = 10;
            $this->aboutService = new AboutService();
        }


        /**
         * 关于我们列表
         */
        public function about_listAction()
        {
            $request     = $this->getRequest();
            $page        = (int)$request->getParam('page');
            $about_id    = $request->getParam('about_id');
            $about_title = $request->getParam('about_title');
            $pid         = (int)$request->getParam('pid');
            $param       = array(
                'about_id' => $about_id,
                'about_name' => $about_title,
                'pid' => $pid,
            );

            $about_info = $this->aboutService->checkPage($page, $this->page_size, $param);

            $about_list = $about_info['list'];
            $page       = $about_info['page'];

            foreach ($about_list as &$list) {
                $list['link'] = ($list['controller'] && $list['action']) ? '/' . $this->request->getModuleName() . '/' . $list['controller'] . '/' . $list['action'] : '';
                switch ($list['about_level']) {
                    case 1:
                        $label_class   = '';
                        $list['class'] = "fz14 fwb";
                        break;
                    case 2:
                        $label_class   = '<i class="level-label">—</i>';
                        $list['class'] = "fz14";
                        break;
                    default:
                        $level         = $list['about_level'] - 2;
                        $list['class'] = '';
                        $label_class   = str_repeat('<i class="level-label"></i>', $level) . '<i class="level-label">—</i>';

                }
                $list['about_title'] = $label_class . $list['about_title'];
                unset($label_class);
            }

            $data = array(
                'about_list' => $about_list,
                'page' => $page,
                'param' => $param,
            );
            $this->view->assign($data);
            $this->render('list');
        }


        /**
         *添加相关栏目
         */
        public function about_addAction()
        {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $pid              = (int)$request->getParam('pid');
                $about_title      = trim(Star_String::escape($request->getParam('about_title')));
                $is_banner        = (int)Star_String::escape($request->getParam('is_banner'));
                $banner_img       = Star_String::escape($request->getParam('banner_img'));
                $banner_m_img       = Star_String::escape($request->getParam('banner_m_img'));
                $banner_pad_img       = Star_String::escape($request->getParam('banner_pad_img'));
                $banner_title     = Star_String::escape($request->getParam('banner_title'));
                $banner_link      = Star_String::escape($request->getParam('banner_link'));
                $banner_quick_title       = Star_String::escape($request->getParam('banner_quick_title'));
                $banner_quick_link       = Star_String::escape($request->getParam('banner_quick_link'));
                $type             = Star_String::escape($request->getParam('type'));
                $about_content    = stripslashes(Star_String::escape($request->getParam('content')));
                $link             = Star_String::escape($request->getParam('link'));
                $page_keywords    = Star_String::escape($request->getParam('page_keywords'));
                $page_description = Star_String::escape($request->getParam('page_description'));
                $page_title       = Star_String::escape($request->getParam('page_title'));
                $template_name       = Star_String::escape($request->getParam('template_name'));
                $desc_title       = Star_String::escape($request->getParam('desc_title'));
                $sort_id          = (int)(Star_String::escape($request->getParam('sort_id')));
                $template          = (int)(Star_String::escape($request->getParam('template')));
                $is_show          = (int)(Star_String::escape($request->getParam('is_show')));
                $home_show          = (int)(Star_String::escape($request->getParam('home_show')));
                if (empty($about_title)) {
                    return $this->showWarning('栏目名不能为空！');
                }

                $time_create = time();
                $time_update = time();
                $status      = 1;
                $param       = compact('desc_title','home_show','banner_quick_link', 'banner_quick_link', 'banner_m_img', 'banner_pad_img', 'is_show', 'pid', 'template_name', 'about_title', 'is_banner', 'banner_img', 'banner_title', 'type', 'about_content', 'link', 'page_keywords', 'page_description', 'page_title', 'sort_id', 'time_update', 'time_create', 'status', 'template');

                $re = $this->aboutService->insertAbout($param);

                if ($re) {
                    if ($pid) {
                        $pabout_info    = $this->aboutService->getaboutInfoById($pid);
                        $about_relation = $pabout_info['about_relation'] . ',' . $re;
                        $arr_count      = explode(',', $about_relation);
                        $level          = count($arr_count);
                    //                        $this->aboutService->cleanCache();
                    } else {
                        $about_relation = $re;
                        $level          = 1;
                    }
                    $re = $this->aboutService->updateAbout($re, array(
                        'about_relation' => $about_relation,
                        'about_level' => $level,
                    ));

                    if ($re) {
                        return $this->showMessage('恭喜您，添加菜单成功。', '/manage/about/about_list');
                    }
                } else {
                    return $this->showWarning('对不起，添加菜单失败。');
                }
            }

            //            $about_info = $this->aboutService->();
            $param = array(
                'sort_id' => 255,
                'type' => 1,
            );

            $plist = $this->aboutService->getAllChildrenInfo();

            $this->view->assign(
                array(
                    'param' => $param,
                    'plist' => $plist,
                )
            );
            $this->render('info');
        }


        /**
         * 编辑相关栏目
         */
        public function about_editAction()
        {
            $aboutService = new AboutService();
            $request      = $this->getRequest();
            $about_id     = (int)$request->getParam('about_id');
            if ($request->isPost()) {
                $pid              = (int)$request->getParam('pid');
                $about_title      = trim(Star_String::escape($request->getParam('about_title')));
                $is_banner        = (int)Star_String::escape($request->getParam('is_banner'));
                $banner_img       = Star_String::escape($request->getParam('banner_img'));
                $banner_m_img       = Star_String::escape($request->getParam('banner_m_img'));
                $banner_pad_img       = Star_String::escape($request->getParam('banner_pad_img'));
                $banner_quick_title       = Star_String::escape($request->getParam('banner_quick_title'));
                $banner_quick_link       = Star_String::escape($request->getParam('banner_quick_link'));
                $banner_title     = Star_String::escape($request->getParam('banner_title'));
                $banner_link      = Star_String::escape($request->getParam('banner_link'));
                $type             = Star_String::escape($request->getParam('type'));
                $about_content    = stripslashes(Star_String::escape($request->getParam('content')));
                $link             = Star_String::escape($request->getParam('link'));
                $page_keywords    = Star_String::escape($request->getParam('page_keywords'));
                $page_description = Star_String::escape($request->getParam('page_description'));
                $page_title       = Star_String::escape($request->getParam('page_title'));
                $desc_title       = Star_String::escape($request->getParam('desc_title'));
                $sort_id          = (int)(Star_String::escape($request->getParam('sort_id')));
                $template          = (int)(Star_String::escape($request->getParam('template')));
                $template_name       = Star_String::escape($request->getParam('template_name'));
                $is_show          = (int)(Star_String::escape($request->getParam('is_show')));
                $home_show          = (int)(Star_String::escape($request->getParam('home_show')));

                if (empty($about_title)) {
                    return $this->showWarning('栏目名不能为空！');
                }

                $time_update = time();
                $param       = compact('desc_title','home_show','banner_quick_link', 'banner_quick_title', 'banner_m_img', 'banner_pad_img', 'is_show', 'pid', 'template_name', 'about_title', 'is_banner', 'banner_img', 'banner_title', 'type', 'about_content', 'link', 'page_keywords', 'page_description', 'page_title', 'sort_id', 'time_update', 'template');
                $arr=compact('about_id');
                $about_update_info = $aboutService->updateAbout($arr, $param);
                if (isset($about_update_info) && is_numeric($about_update_info)) {
                    return $this->showMessage('恭喜您,修改栏目成功。', '/manage/about/about_list');
                } else {
                    return $this->showWarning('对不起，修改栏目失败。');
                }
            }

            $plist = $this->aboutService->getAllChildrenInfo();
            $about_info      = $aboutService->getAboutById($about_id);
            $this->view->assign(
                array(
                    'plist' => $plist,
                    'param' => $about_info,
                )
            );
            $this->render('info');
        }


        /**
         * 删除相关栏目
         */
        public function about_delAction()
        {
            $request = $this->getRequest();
            $about_id          = (int)$request->getParam('about_id');
            $about_update_info = $this->aboutService->delAbout($about_id);
            if ($about_update_info) {
                return $this->showMessage('恭喜您，删除成功。', '/manage/about/about_list');
            } else {
                return $this->showWarning('很遗憾，删除失败。', '/manage/about/about_list');
            }
        }
    }
