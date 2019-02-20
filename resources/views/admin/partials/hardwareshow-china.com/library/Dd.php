<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/9/1
     * Time: 12:22
     */

    class Dd
    {
        public function del_img($img)
        {
            return ($img) ? DOMAIN_FILE . $img : '';
        }


        public function dump($arr)
        {
            if (is_array($arr)) {
                echo "<pre>";
                print_r($arr);
                echo "<pre>";
            } else {
                echo $arr;
            }
            exit;
        }


        public function arr_json($arr)
        {
            if (is_array($arr)) {
                return base64_encode(json_encode($arr));
            }
            return $arr;
        }


        public function json_arr($string)
        {
            if ($string) {
                return json_decode(base64_decode($string, true), true);
            }
        }


        public function deal_time($time)
        {
            return ($time) ? date('Y-m-d H:i:s', $time) : '';
        }


        public function deal_desc($desc)
        {
            return ($desc) ? htmlspecialchars_decode($desc) : '';
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


        public function toArray($xml)
        {
            //禁止引用外部xml实体
            libxml_disable_entity_loader(true);
            $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
            return $result;
        }


        public function createString($n)
        {
            if ($n <= 1) {
                return $n;
            }
            $str = 1;
            for ($i = 2; $i <= $n; $i++) {
                $str .= '-' . $i;
            }
            return trim($str);
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


        public function dealExplode($str)
        {
            if (empty($str)) {
                return array();
            }
            $arr  = explode(',', $str);
            $list = array();
            foreach ($arr as $rs) {
                $rs     = str_replace('<', '', $rs);
                $list[] = (int)trim(str_replace('>', '', $rs));
            }
            return $list;
        }


        public function dealImplode($list)
        {
            if (!is_array($list)) {
                return $list;
            }
            $arr = array();
            foreach ($list as $rs) {
                $arr[] = '<' . $rs . '>';
            }

            $str = implode(',', $arr);
            return $str;
        }

        public function IntToChr($index, $start = 65)
        {
            $str = '';
            if (floor($index / 26) > 0) {
                $str .= IntToChr(floor($index / 26) - 1);
            }
            return $str . chr($index % 26 + $start);
        }

        public function unique_rand($min, $max, $num)
        {
            $count=0;
            $return =array();
            while ($count<$num) {
                $return[]=mt_rand($min, $max);
                $return=array_flip(array_flip($return));
                $count=count($return);
            }

            shuffle($return);
            return $return;
        }


        //file_cert_pem,file_key_pem两个退款必须的文件
        public function curl_post_ssl($url, $vars, $file_cert_pem, $file_key_pem, $second = 30, $aHeader = array())
        {
            $ch = curl_init();
            //超时时间
            curl_setopt($ch, CURLOPT_TIMEOUT, $second);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            //这里设置代理，如果有的话
            //curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');
            //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            //以下两种方式需选择一种

            //第一种方法，cert 与 key 分别属于两个.pem文件
            //默认格式为PEM，可以注释
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
//        curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/cert.pem');
            curl_setopt($ch, CURLOPT_SSLCERT, $file_cert_pem);
            //默认格式为PEM，可以注释
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
//        curl_setopt($ch,CURLOPT_SSLKEY,getcwd().'/private.pem');
            curl_setopt($ch, CURLOPT_SSLKEY, $file_key_pem);

            //第二种方式，两个文件合成一个.pem文件
//        curl_setopt($ch, CURLOPT_SSLCERT, getcwd() . '/all.pem');

            if (count($aHeader) >= 1) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
            }


            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
            $data = curl_exec($ch);
            if ($data) {
                curl_close($ch);
                return $data;
            } else {
                $error = curl_errno($ch);
//            echo "call faild, errorCode:$error\n";
                curl_close($ch);
                return false;
            }
        }



        public function GetRandStr($length)
        {
            $str='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $len=strlen($str)-1;
            $randstr='';
            for ($i=0;$i<$length;$i++) {
                $num=mt_rand(0, $len);
                $randstr .= $str[$num];
            }
            return $randstr;
        }


        public function deal_order_no($order_no,$count)
        {
            // $final_str=substr($order_no, -1);
            $before_str=substr($order_no, 0,strlen($order_no)-1);
            $final_str='';
            for($i;$i<=$count;$i++){
               if(empty($final_str)){
                 $final_str=$i;
               }else{
                 $final_str.='-'.$i;
               }
            }
            return $before_str.'_'.$final_str;
        }
    }
