<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/6/1
     * Time: 16:22
     */
    require APPLICATION_PATH . '/modules/manage/controllers/CommonController.php';

    class NewsController extends CommonController
    {
        public function init()
        {
            parent::init();
            $this->newsService = new NewsService();
        }


        /**
         * 新闻列表
         */
        public function news_listAction()
        {
            $request   = $this->getRequest();
            $page      = (int)$request->getParam('page');
            $page_size = 20; //每页显示数
            $param     = array();
            if ($request->isGet()) {
                $news_title   = Star_String::escape($request->getParam('news_title'));
                $news_id      = Star_String::escape($request->getParam('news_id'));
                $is_recommend = Star_String::escape($request->getParam('is_recommend'));
                $is_up        = Star_String::escape($request->getParam('is_up'));
                $home_show    = Star_String::escape($request->getParam('home_show'));
                $category_id  = Star_String::escape($request->getParam('category_id'));
                $param        = array(
                    'news_title' => $news_title,
                    'news_id' => $news_id,
                    'category_id' => $category_id,
                    'is_recommend' => $is_recommend,
                    'is_up' => $is_up,
                    'home_show' => $home_show,
                );
            }

            $category_info = $this->newsService->getAllCate();
            $news_info     = $this->newsService->getNewsInfoByPage($page, $page_size, $param);
            $news_list     = $news_info['list'];

            foreach ($news_list as &$val){
                $newService             = new NewsService();
                $result                 = ($val['category_id']) ? $newService->getCategoryById($val['category_id']) : 0;
                $val['category_name']     = ($result) ? $result['category_name'] : " ";
                $obj                    = array(
                    'news_id' => $val['news_id'],
                );
                $obj['up_type']         = 1;
                $obj['flag']            = 1;
                $val['thumb_up_counts'] = $newService->getThumbCounts($obj);

                $obj['up_type']           = 2;
                $val['thumb_down_counts'] = $newService->getThumbCounts($obj);
                unset($obj['flag']);
                $val['comment_counts'] = $newService->getCommentCounts($obj);
            }
//            array_walk($news_list, function (&$val, $key, $param) {
//                $newService             = new NewsService();
//                $result                 = ($val['category_id']) ? $newService->getCategoryById($val['category_id']) : 0;
//                $val[$param['key']]     = ($result) ? $result['category_name'] : " ";
//                $obj                    = array(
//                    'news_id' => $val['news_id'],
//                );
//                $obj['up_type']         = 1;
//                $obj['flag']            = 1;
//                $val['thumb_up_counts'] = $newService->getThumbCounts($obj);
//
//                $obj['up_type']           = 2;
//                $val['thumb_down_counts'] = $newService->getThumbCounts($obj);
//                unset($obj['flag']);
//                $val['comment_counts'] = $newService->getCommentCounts($obj);
//            }, array( 'key' => 'category_name' ));

            $page_info = $news_info['page'];

            $this->view->assign(
                array(
                    'category_info' => $category_info,
                    'param' => $param,
                    'news_list' => $news_list,
                    'page' => $page_info,
                    'cur_page' => $page,
                ));
            $this->render('list');
        }


        /**
         * 添加新闻
         */
        public function news_addAction()
        {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $news_title       = Star_String::escape($request->getParam('news_title'));
                $news_content     = stripslashes($request->getParam('content'));
                $is_recommend     = (int)Star_String::escape($request->getParam('is_recommend'));
                $is_up            = (int)Star_String::escape($request->getParam('is_up'));
                $home_show        = Star_String::escape($request->getParam('home_show'));
                $page_keywords    = Star_String::escape($request->getParam('page_keywords'));
                $page_description = Star_String::escape($request->getParam('page_description'));
                $sort_id          = Star_String::escape($request->getParam('sort_id'));
                $img_url          = Star_String::escape($request->getParam('pic'));
                $category_id      = Star_String::escape($request->getParam('category_id'));
                $type             = Star_String::escape($request->getParam('type'));
                $news_link        = Star_String::escape($request->getParam('news_link'));
                $template_name        = Star_String::escape($request->getParam('template_name'));
                $news_tm_publish  = Star_String::escape($request->getParam('news_tm_publish'));
                $news_m_img  = Star_String::escape($request->getParam('news_m_img'));
                $news_pad_img  = Star_String::escape($request->getParam('news_pad_img'));
                $news_pc_img  = Star_String::escape($request->getParam('news_pc_img'));

                if (empty($news_title)) {
                    return $this->showWarning('标题不能为空！');
                }

                if (empty($news_tm_publish)) {
                    $news_tm_publish = date('Y-m-d');
                }

                $param = array(
                    'news_link' => $news_link,
                    'category_id' => $category_id?:5,
                    'news_title' => $news_title,
                    'news_content' => $news_content,
                    'is_recommend' => $is_recommend,
                    'is_up' => $is_up,
                    'home_show' => $home_show,
                    'page_keywords' => $page_keywords,
                    'page_description' => $page_description,
                    'news_img' => $img_url,
                    'type' => $type,
                    'sort_id' => ($sort_id) ? $sort_id : 255,
                    'news_tm_publish' => $news_tm_publish,
                    'template_name' => $template_name,
                    'news_pc_img' => $news_pc_img,
                    'news_pad_img' => $news_pad_img,
                    'news_m_img' => $news_m_img,
                    'time_create' => time(),
                    'time_update' => time(),
                );


                $news_info = $this->newsService->insertNews($param);
                if ($news_info) {
                    return $this->showMessage('恭喜您，添加新闻成功。', '/manage/news/news_list');
                } else {
                    return $this->showWarning('对不起，添加新闻失败。');
                }
            }

            $param         = array(
                'type' => 1,
                'sort_id' => 255,
                'news_tm_publish' => date('Y-m-d'),
            );
            $category_info = $this->newsService->getAllCate();

            $this->view->assign(
                array(
                    'category_info' => $category_info,
                    'param' => $param,
                ));
            //print_r($category_info);exit;
            $this->render('info');
        }


        /*
         * 编辑新闻
         */
        public function news_editAction()
        {
            $category_info = $this->newsService->getAllCate();
            $request       = $this->getRequest();
            $page          = (int)$request->getParam('cur_page');
            $s_news_id     = $request->getParam('s_news_id');
            $s_news_title  = $request->getParam('s_news_title');
            $s_news_cate   = $request->getParam('s_news_cate_id');
            $news_id       = (int)$request->getParam('news_id');

            if ($request->isPost()) {
                $news_title       = Star_String::escape($request->getParam('news_title'));
                $news_content     = stripslashes(Star_String::escape($request->getParam('content')));
                $is_recommend     = (int)Star_String::escape($request->getParam('is_recommend'));
                $is_up            = (int)Star_String::escape($request->getParam('is_up'));
                $home_show        = Star_String::escape($request->getParam('home_show'));
                $page_keywords    = Star_String::escape($request->getParam('page_keywords'));
                $page_description = Star_String::escape($request->getParam('page_description'));
                $sort_id          = Star_String::escape($request->getParam('sort_id'));
                $category_id      = Star_String::escape($request->getParam('category_id'));
                $img_url          = Star_String::escape($request->getParam('pic'));
                $type             = Star_String::escape($request->getParam('type'));
                $news_tm_publish  = Star_String::escape($request->getParam('news_tm_publish'));
                $template_name        = Star_String::escape($request->getParam('template_name'));
                $news_link        = Star_String::escape($request->getParam('news_link'));
                $news_m_img  = Star_String::escape($request->getParam('news_m_img'));
                $news_pad_img  = Star_String::escape($request->getParam('news_pad_img'));
                $news_pc_img  = Star_String::escape($request->getParam('news_pc_img'));
                if (empty($news_title)) {
                    return $this->showWarning('标题不能为空！');
                }

                if (empty($news_tm_publish)) {
                    $news_tm_publish = date('Y-m-d');
                }
                $param = array(
                    'news_link' => $news_link,
                    'news_img' => $img_url,
                    'category_id' => $category_id?:5,
                    'news_title' => $news_title,
                    'news_content' => $news_content,
                    'is_recommend' => $is_recommend,
                    'is_up' => $is_up,
                    'home_show' => $home_show,
                    'page_keywords' => $page_keywords,
                    'page_description' => $page_description,
                    'news_tm_publish' => $news_tm_publish,
                    'template_name' => $template_name,
                    'sort_id' => ($sort_id) ? $sort_id : 255,
                    'type' => $type,
                    'news_pc_img' => $news_pc_img,
                    'news_pad_img' => $news_pad_img,
                    'news_m_img' => $news_m_img,
                    'time_update' => time(),
                );
                $arr   = array(
                    'news_id' => $news_id,
                );

                $news_update_info = $this->newsService->updateNews($arr, $param);
                if (isset($news_update_info) && is_numeric($news_update_info)) {
                    $url = " /manage/news/news_list?page=$page&news_id=$s_news_id&news_title=$s_news_title&category_id=$s_news_cate";
                    return $this->showMessage('恭喜您,修改新闻成功。', $url);
                } else {
                    return $this->showWarning('对不起，修改新闻失败。');
                }
            }

            $news_info = $this->newsService->getNewsInfoById($news_id);

            $this->view->assign(
                array(
                    'category_info' => $category_info,
                    'param' => $news_info,
                ));
            //print_r($news_info);exit;
            $this->render('info');
        }


        /**
         * 删除新闻
         */
        public function news_delAction()
        {
            $request          = $this->getRequest();
            $news_id          = (int)$request->getParam('news_id');
            $arr              = array(
                'news_id' => $news_id,
            );
            $news_update_info = $this->newsService->delNews($arr);
            if ($news_update_info) {
                return $this->showMessage('恭喜您，删除成功。', '/manage/news/news_list');
            } else {
                return $this->showWarning('很遗憾，删除失败。', '/manage/news/news_list');
            }
        }


        /**
         * 分类列表
         */
        public function cate_listAction()
        {
            $request   = $this->getRequest();
            $page      = (int)$request->getParam('page');  //        $category_name = Star_String::escape($request->getParam('category_name'));
            $page_size = 20;

            $category_data = $this->newsService->getFirstCateByPage($page, $page_size);
            $this->newsService->addICon($category_data['list']);
            $list = array( 'category_list' => $category_data['list'], );
            $this->view->assign($list);
            $this->render('cate_list');
        }


        /**
         * 添加分类
         */
        public function cate_addAction()
        {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $category_name = Star_String::escape($request->getParam('category_name'));
                $template        = (int)$request->getParam('template');
                $number        = (int)$request->getParam('number');
                $sort_id       = (int)$request->getParam('sort_id');
                $is_show       = (int)$request->getParam('is_show');
                $parent_id     = (int)$request->getParam('pid');
                $template_name        = Star_String::escape($request->getParam('template_name'));

                if (empty($category_name)) {
                    return $this->showWarning('分类名称不能为空');
                }

                $category_data = array(
                    'category_name' => $category_name,
                    'template' => $template,
                    'pid' => $parent_id,
                    'number' => $number,
                    'sort_id' => $sort_id,
                    'is_show' => $is_show,
                    'template_name' => $template_name,
                    'add_time' => time(),
                );
                $category_id   = $this->newsService->insertCategory($category_data);
                if ($category_id) {
                    if ($parent_id == 0) {
                        $relation = $category_id;
                        $level    = 1;
                    } else {
                        $p_info   = $this->newsService->getCategoryById($parent_id);
                        $relation = $p_info['relation'] . ',' . $category_id;
                        $level    = $p_info['level'] + 1;
                        unset($p_info);
                    }
                    $update_re = $this->newsService->updateCategory($category_id, array( 'relation' => $relation, 'level' => $level, ));
                    if ($update_re) {
                        return $this->showMessage('恭喜您，添加成功', '/manage/news/cate_list');
                    }
                } else {
                    return $this->showWarning('很遗憾，添加失败');
                }
            }
            $category_info = $this->newsService->getAllCate();

            $this->view->assign('category_info', $category_info);
            $this->view->assign('category', array(
                'template' => 1,
            ));
            $this->render('cate_info');
        }


        /**
         * 编辑分类
         */
        public function cate_editAction()
        {
            $parent_info = $this->newsService->getParentInfo();

            $request     = $this->getRequest();
            $category_id = (int)$request->getParam('category_id');
            $category    = $this->newsService->getCategoryById($category_id);

            if (empty ($category)) {
                return $this->showWarning('分类不存在');
            }

            if ($request->isPost()) {
                $category_name = Star_String::escape($request->getParam('category_name'));
                $category_key  = Star_String::escape($request->getParam('category_key'));
                $template      = (int)$request->getParam('template');
                $number        = (int)$request->getParam('number');
                $sort_id       = (int)$request->getParam('sort_id');
                $is_show       = (int)$request->getParam('is_show');
                $parent_id     = (int)$request->getParam('parent_id');
                $template_name        = Star_String::escape($request->getParam('template_name'));

                if (empty($category_name)) {
                    return $this->showWarning('分类名称不能为空');
                }

                if ($parent_id == $category_id) {
                    return $this->showWarning('父类编号与子类编号不能相同！');
                }

                $category_data = array(
                    'category_name' => $category_name,
                    'template' => $template,
                    'category_key' => $category_key,
                    'template_name' => $template_name,
                    //                    'parent_id' => $parent_id,
                    //                    'number' => $number,
                    'sort_id' => $sort_id,
                    'is_show' => $is_show,
                    'add_time' => time(),
                );
                $rs            = $this->newsService->updateCategory($category_id, $category_data);
                if ($rs) {
                    return $this->showMessage('恭喜你，编辑成功', '/manage/news/cate_list');
                } else {
                    return $this->showWarning('很遗憾，编辑失败');
                }
            }
            $category_info = $this->newsService->getAllCate();
            $this->view->assign('category_info', $category_info);
            $this->view->assign('parent_info', $parent_info);
            $this->view->assign('category', $category);
            $this->render('cate_info');
        }


        /**
         *删除分类
         */
        public function cate_delAction()
        {
            $request     = $this->getRequest();
            $category_id = (int)$request->getParam('category_id');

            $category = $this->newsService->getCategoryById($category_id);
            if (empty($category)) {
                return $this->showWarning('分类不存在');
            }

            $rs = $this->newsService->deleteCategory($category_id);
            if ($rs) {
                return $this->showMessage('删除成功');
            } else {
                return $this->showWarning('删除失败');
            }
        }


        /**
         * 新闻评论详情功能
         */
        public function news_commsAction()
        {
            $request   = $this->getRequest();
            $page      = (int)$request->getParam('page');
            $page_size = 10; //每页显示数
            $param     = $request->getParams();

            $param = array(
                'news_title' => $param['news_title'],
                'news_id' => $param['news_id'],
                'category_id' => $param['category_id'],
            );

            $category_info = $this->newsService->getAllCate();
            $news_info     = $this->newsService->getNewsInfoByPage($page, $page_size, $param);
            $news_list     = $news_info['list'];

//            array_walk($news_list, function (&$val, $key, $param) {
//                $newService         = new NewsService();
//                $result             = ($val['category_id']) ? $newService->getCategoryById($val['category_id']) : 0;
//                $val[$param['key']] = ($result) ? $result['category_name'] : " ";
//                $obj                = array(
//                    'news_id' => $val['news_id'],
//                );
//                //获取向上点赞，标记为flag来判断是对文章点赞还是，对评论点赞，文章点赞，flag为1
//                $obj['up_type']         = 1;
//                $obj['flag']            = 1;
//                $val['thumb_up_counts'] = $newService->getThumbCounts($obj);
//                //获取向下点赞
//                $obj['up_type']           = 2;
//                $val['thumb_down_counts'] = $newService->getThumbCounts($obj);
//                unset($obj['flag']);
//                //获取评论数
//                $val['comment_counts'] = $newService->getCommentCounts($obj);
//            }, array( 'key' => 'category_name' ));

            $page_info = $news_info['page'];
            //print_r($news_list);exit;
            $this->view->assign(
                array(
                    'category_info' => $category_info,
                    'param' => $param,
                    'news_list' => $news_list,
                    'page' => $page_info,
                    'cur_page' => $page,
                ));
            $this->render('comm_list');
        }


        public function comm_detailAction()
        {

            $page_size = 15;
            $request   = $this->getRequest();
            $page      = $request->getParam('page');
            if ($request->isGet()) {
                $comms = $request->getParams();
                foreach ($comms as $key => &$value) {
                    $value = Star_String::escape($value);
                }
            }
            if ($comms['news_id']) {
                $news = $this->newsService->getNewsInfoById($comms['news_id']);
            }


            $res = $this->newsService->getCommentByPage($page, $page_size, $comms);

            //print_r($res);exit;
            $res['news_title'] = $news['news_title'];
            $this->view->assign(
                array(

                    'page' => $res['page'],
                    'comm_list' => $res['list'],
                    'news_title' => $res['news_title'],
                    'param' => array( 'comment_id' => $comms['comment_id'] ),
                    'orig_com' => $comms['content'],
                    'referer' => $_SERVER["HTTP_REFERER"],
                ));
            if (isset($res['list'][0]['reply_id']) && ($res['list'][0]['reply_id'] == 0)) {
                $this->render('comm_detail');
            } else {
                $this->render('reply_detail');
            }


        }


        public function vote_detailAction()
        {

            $page_size = 15;
            $request   = $this->getRequest();
            $page      = $request->getParam('page');
            if ($request->isGet()) {
                $comms = $request->getParams();
                foreach ($comms as $key => &$value) {
                    $value = Star_String::escape($value);
                }
            }
            if ($comms['news_id']) {
                $news = $this->newsService->getNewsInfoById($comms['news_id']);
            }


            $res = $this->newsService->getThumbUpByPage($page, $page_size, $comms);
            //print_r($res);exit;
            $res['news_title'] = $news['news_title'];
            $this->view->assign(
                array(

                    'page' => $res['page'],
                    'comm_list' => $res['list'],
                    'news_title' => $res['news_title'],
                    'param' => array( 'comment_id' => $comms['comment_id'] ),
                ));
            $this->render('vote_detail');
        }


        public function del_commAction()
        {
            $request = $this->getRequest();
            if ($request->isGet()) {
                $comms = $request->getParams();
                foreach ($comms as $key => &$value) {
                    $value = Star_String::escape($value);
                }
            }
            $comment_id = $comms['comment_id'];
            $res        = $this->newsService->deleteComment($comment_id);
            if ($res) {
                return $this->showMessage('删除成功');
            }
        }


        public function del_thumb_upAction()
        {
            $request = $this->getRequest();
            if ($request->isGet()) {
                $comms = $request->getParams();
                foreach ($comms as $key => &$value) {
                    $value = Star_String::escape($value);
                }
            }
            $comment_id = $comms['up_id'];
            $res        = $this->newsService->deleteThumbUp($comment_id);
            if ($res) {
                return $this->showMessage('删除成功');
            }
        }


    }
