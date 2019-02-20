<?php
    require 'Star/Application/Bootstrap/Bootstrap.php';

    class Entrance extends Star_Application_Bootstrap_Bootstrap
    {

        // 初始化 view
        protected function _initView()
        {
            $image_server = Star_Config::get('resources.upload.serverName');
            $this->view->assign('upload_image_server', $image_server);
        }


        // 常量配置
        protected function _initConst()
        {
            require APPLICATION_PATH . '/configs/constants.php';

            $ip     = $_SERVER['SERVER_ADDR'];
            $ip_arr = array( '106.14.93.16' );
            if (in_array($ip, $ip_arr)) {
                require APPLICATION_PATH . '/configs/development.php';
            } else {
                if (Star_Config::get('resources.frontController.de') == 1) {
                    require APPLICATION_PATH . '/configs/development.php';
                } else {
                    require APPLICATION_PATH . '/configs/production.php';
                }
                unset($file);
            }
        }

          protected function _initRoute(){
            $aboutID = new Star_Controller_Router_Route_Regex(
                "about/id/(\d+).html",
                array(
                    "controller" => "about",
                    "action" => "index",
                ),
                array(
                    1 => 'id',
                )
            );

            $aboutName = new Star_Controller_Router_Route_Regex(
                "about/([a-zA-Z_0-9]+).html",
                array(
                    "controller" => "about",
                    "action" => "index",
                ),
                array(
                    1 => 'template_name',
                )
            );


            $newsID = new Star_Controller_Router_Route_Regex(
                "news/index/category_id/(\d+).html",
                array(
                    "controller" => "news",
                    "action" => "index",
                ),
                array(
                    1 => 'category_id',
                )
            );

            $newsDetailID = new Star_Controller_Router_Route_Regex(
                "news/detail/id/(\d+).html",
                array(
                    "controller" => "news",
                    "action" => "detail",
                ),
                array(
                    1 => 'id',
                )
            );

            $newsName= new Star_Controller_Router_Route_Regex(
                "news/detail/([a-zA-Z_0-9]+).html",
                array(
                    "controller" => "news",
                    "action" => "detail",
                ),
                array(
                    1 => 'template_name',
                )
            );

            $newsGallery = new Star_Controller_Router_Route_Regex(
                "news/multimedia_gallery.html",
                array(
                    "controller" => "news",
                    "action" => "multimedia_gallery",
                )
            );

            $newsMediaPartners= new Star_Controller_Router_Route_Regex(
                "news/media_partners.html",
                array(
                    "controller" => "news",
                    "action" => "media_partners",
                )
            );

            $event= new Star_Controller_Router_Route_Regex(
                "event/index.html",
                array(
                    "controller" => "event",
                    "action" => "index",
                )
            );

            $exhibitor= new Star_Controller_Router_Route_Regex(
                "exhibitor/index.html",
                array(
                    "controller" => "exhibitor",
                    "action" => "index",
                )
            );


            $product_list=new Star_Controller_Router_Route_Regex(
                "exhibitor/product_list.html",
                array(
                    "controller" => "exhibitor",
                    "action" => "product_list",
                )
            );


            $eventID = new Star_Controller_Router_Route_Regex(
                "event/desc/about_id/(\d+).html",
                array(
                    "controller" => "event",
                    "action" => "desc",
                ),
                array(
                    1 => 'about_id',
                )
            );


            $eventName = new Star_Controller_Router_Route_Regex(
                "event/desc/([a-zA-Z_0-9]+).html",
                array(
                    "controller" => "event",
                    "action" => "desc",
                ),
                array(
                    1 => 'template_name',
                )
            );


            $eventDetailId = new Star_Controller_Router_Route_Regex(
                "event/detail/(\d+).html",
                array(
                    "controller" => "event",
                    "action" => "detail",
                ),
                array(
                    1 => 'id',
                )
            );

            $eventDetailName = new Star_Controller_Router_Route_Regex(
                "event/detail/([a-zA-Z_0-9]+).html",
                array(
                    "controller" => "event",
                    "action" => "detail",
                ),
                array(
                    1 => 'template_name',
                )
            );


            $exhibitorID = new Star_Controller_Router_Route_Regex(
                "exhibitor/desc/about_id/(\d+).html",
                array(
                    "controller" => "exhibitor",
                    "action" => "desc",
                ),
                array(
                    1 => 'about_id',
                )
            );

            $exhibitorName = new Star_Controller_Router_Route_Regex(
                "exhibitor/desc/([a-zA-Z_0-9]+).html",
                array(
                    "controller" => "exhibitor",
                    "action" => "desc",
                ),
                array(
                    1 => 'template_name',
                )
            );


            $this->front->addRouter("aboutID", $aboutID);
            $this->front->addRouter("aboutName", $aboutName);
            $this->front->addRouter("newsID", $newsID);
            $this->front->addRouter("newsName", $newsName);
            $this->front->addRouter("newsDetailID", $newsDetailID);
            $this->front->addRouter("newsGallery", $newsGallery);
            $this->front->addRouter("newsMediaPartners", $newsMediaPartners);
            $this->front->addRouter("event", $event);
            $this->front->addRouter("exhibitor", $exhibitor);
            $this->front->addRouter("product_list", $product_list);
            $this->front->addRouter("eventID", $eventID);
            $this->front->addRouter("eventName", $eventName);
            $this->front->addRouter("eventDetailId", $eventDetailId);
            $this->front->addRouter("eventDetailName", $eventDetailName);
            $this->front->addRouter("exhibitorID", $exhibitorID);
            $this->front->addRouter("exhibitorName", $exhibitorName);
          }



    }
