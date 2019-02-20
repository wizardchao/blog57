<?php


    class UtilsHelper
    {
        protected $loginToken = 'e4ac2f047cf82cff2952c228a837cf16';

        //测试用例
        function demo()
        {
            return 'helper.utils.demo.';
        }


        // 生成随机数
        public function get_rand_number($start, $end, $length)
        {
            $connt = 0;
            $temp  = array();
            while ($connt < $length) {
                $temp[] = mt_rand($start, $end);
                $data   = array_unique($temp);
                $connt  = count($data);
            }
            return implode($data);
        }

        //格式化数组
        public function rule($arr)
        {
            echo "<pre>";
            print_r($arr);
            echo "</pre>";
            exit;
        }


        /**
         * curl get请求
         * @param $url
         * @param int $httpCode
         * @return mixed
         */
        public function curl_get($url, &$httpCode = 0)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            //不做证书校验,部署在linux环境下请改为true
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            $file_contents = curl_exec($ch);
            $httpCode      = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return $file_contents;
        }


        /**
         * @param string $url post请求地址
         * @param array $params
         * @return mixed
         */
        public function curl_post($url, array $params = array())
        {
            $data_string = json_encode($params);
            $ch          = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt(
                $ch, CURLOPT_HTTPHEADER,
                array(
                    'Content-Type: application/json',
                )
            );
            $data = curl_exec($ch);
            curl_close($ch);
            return ($data);
        }


        /**
         * raw post 请求
         * @param $url
         * @param $rawData
         * @return mixed
         */
        public function curl_post_raw($url, $rawData)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $rawData);
            curl_setopt(
                $ch, CURLOPT_HTTPHEADER,
                array(
                    'Content-Type: text',
                )
            );
            $data = curl_exec($ch);
            curl_close($ch);
            return ($data);
        }

        /**
         * md5加密
         */
        public function encrytPwd($pwd)
        {
            return md5($this->loginToken . $pwd . $this->loginToken);
        }


        /**
         * @param $str
         * @return null|string|string[]
         * 解码unicode
         */
        public function decodeUnicode($str)
        {
            return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
                create_function(
                    '$matches',
                    'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
                ),
                $str);
        }


        /**
         * 添加http头
         * @param $url
         * @param bool $https
         * @return string
         */
        public function addHttp($url, $https = false)
        {
            if (preg_match('/^http(s)?:\\/\\/.+/', $url)) {
                return $url;
            } else {
                return $https ? 'https://' . $url : 'http://' . $url;
            }
        }


        //打乱多维数组
        public function rec_assoc_shuffle($list)
        {
            if (!is_array($list)) return $list;

            $keys = array_keys($list);
            shuffle($keys);
            //        $this->rule($keys);exit;
            $random = array();
            foreach ($keys as $key)
                $random[$key] = $list[$key];
            return $random;
        }


        //获取设备型号
        public function getDevice($agent)
        {
            if (true == preg_match("/.+Windows.+/", $agent)) {
                return "web";
            } elseif (true == preg_match("/.+Macintosh.+/", $agent)) {
                return "mac";
            } elseif (true == preg_match("/.+iPad.+/", $agent)) {
                return "iPad";
            } elseif (true == preg_match("/.+iPhone.+/", $agent)) {
                return "iPhone";
            } elseif (true == preg_match("/.+Android.+/", $agent)) {
                return "Android";
            } else {
                return "未知设备";
            }
        }

        /**
         * 检验是不是中文
         * @param $str
         * @return bool
         */
        public function isChinese($str)
        {
            if (preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $str) > 0) {
                return true;
            }
            return false;
        }

        /**
         * 获取随机字符串
         * @param $length
         * @return null|string
         */
        public function getRandChar($length)
        {
            $str    = null;
            $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
            $max    = strlen($strPol) - 1;

            for ($i = 0;
                 $i < $length;
                 $i++) {
                $str .= $strPol[rand(0, $max)];
            }

            return $str;
        }

        /**
         * 排序
         */
        public function treeSort($id, $menu_arr)
        {
            //取出各数组第一位，压入新数组
            $new_arr   = array();
            $new_arr[] = $id;


            //遍历数组，将值放在其上级后面
            foreach ($menu_arr as $value) {
                $count = count($value);
                if ($count > 1) {
                    for ($i = 1; $i < $count; $i++) {
                        if ($value[$i]) {
                            //查找上级位置
                            $search = array_search($value[$i - 1], $new_arr) + 1;
                            if (!in_array($value[$i], $new_arr)) {
                                array_splice($new_arr, $search, 0, $value[$i]);
                            }
                        }
                    }
                }
            }
            unset($menu_arr);
            return $new_arr;
        }


        //去富文本标签

        public function deal_label($content)
        {
            $content1 = htmlspecialchars_decode($content);
            $content2 = str_replace("&nbsp;", "", $content1);//将空格替换成空
            $contents = trim(strip_tags($content2));//函数剥去字符串中的 HTML、XML 以及 PHP 的标签,获取纯文本内容
            return mb_substr($contents, 0, 60, 'utf-8');
        }


        //截取utf8字符

        public function deal_utf($str)
        {
            $str = $this->del_label($str);
            return $this->utfSubstr($str, 0, 60);
        }

        public function del_label($content)
        {
            $content1 = htmlspecialchars_decode($content);
            $content2 = str_replace("&nbsp;", "", $content1);//将空格替换成空
            $contents = trim(strip_tags($content2));//函数剥去字符串中的 HTML、XML 以及 PHP 的标签,获取纯文本内容
            return mb_substr($contents, 0, 60, 'utf-8');
        }

        function utfSubstr($str, $from, $len)
        {
            return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $from . '}' . '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $len . '}).*#s', '$1', $str);
        }

        /**
         * 求出页数
         */
        public function get_page($total, $page_size)
        {
            if ($total == 0) {
                return 0;
            }
            if ($total < $page_size) {
                return 1;
            }
            if ($total > 0) {
                $page_max_size = ($total % $page_size);
                $size          = (int)($total / $page_size);
                $page_max      = ($page_max_size) ? ($size + 1) : $size;
                return $page_max;
            }
        }


        /**
         * 判断页数是否超出范围
         */
        public function is_page($total, $page, $page_size)
        {
            if ($total > 0) {
                $page_max_size = ($total % $page_size);
                $size          = (int)($total / $page_size);
                $page_max      = ($page_max_size) ? ($size + 1) : $size;
                if ($page > $page_max) {
                    return 1;
                }
            }
            return 0;
        }


        //模拟发送请求
        public function getUrlData($post_data, $url)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1); //post数据
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data)); //post的变量
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $output = curl_exec($ch);
            curl_close($ch);
            $sendOK = get_object_vars(json_decode($output));
            return $sendOK;
        }


        public function getMonthList($year, $month)
        {
            //月初月末时间戳
            $Y           = $year;//获取年，示例，真实环境从前端获取数据
            $m           = $month;//获取月，示例，真实环境从前端获取数据
            $month       = $Y . "-" . $m;//当前年月
            $month_start = strtotime($month);//指定月份月初时间戳
            $month_end   = mktime(23, 59, 59, date('m', strtotime($month)) + 1, 00);//指定月份月末时间戳
            return array( "month" => $month, "month_start" => $month_start, "month_end" => $month_end );
        }

    }