<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/9/24
     * Time: 0:27
     */
    require APPLICATION_PATH . '/modules/api/controllers/CommonController.php';

    class ExpressController extends CommonController
    {
        protected $expressService;

        public function init()
        {
            parent::init();
            $this->expressService = new ExpressService();
        }

        public function companyAction()
        {
            $company_info = $this->expressService->getAllExpressCompany();
            $list         = array();
            foreach ($company_info as $el) {
                $list[] = array(
                    'id' => (int)$el['id'],
                    'key' => $el['key'],
                    'name' => $el['name'],
                );
            }

            return $this->showJson(200, $list);
        }


        public function queryAction()
        {
            $request = $this->getRequest();
            $type    = $request->getParam('type');
            $no      = $request->getParam('no');

            if (empty($type)) {
                return $this->showJson(202, '物流公司类型不能为空！');
            }

            if (empty($no)) {
                return $this->showJson(203, '物流单号不能为空！');
            }

            $beginToday = 'Wildcubic'.mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $count = (int)Star_Cache::get($beginToday);
            if ($count > 300) return $this->showJson(203, '系统维护中，请稍后再试！');
            Star_Cache::set($beginToday, ++$count);

            $host         = "https://wuliu.market.alicloudapi.com";//api访问链接
            $path         = "/kdi";//API访问后缀
            $method       = "GET";
            $appcode      = EXPRESS_APPCODE;  //替换成自己的阿里云appcode
            $express_info = $this->expressService->getExpressInfo($host, $path, $method, $appcode, $no, $type);

            if ($express_info['status'] == 0 && $express_info['msg'] == 'ok') {
                $data                   = $express_info['result'];
                $data['issign']         = (int)$data['issign'];
                $data['deliverystatus'] = (int)$data['deliverystatus'];
                $data['deliverydesc']   = $this->expressService->getExpressStatusByDeliverystatus($data['deliverystatus']);
                //                $data['expPhone']       = (int)$data['expPhone'];
                return $this->showJson(200, $data);
            }
            return $this->showJson($express_info['status'], $express_info['msg']);
        }

    }