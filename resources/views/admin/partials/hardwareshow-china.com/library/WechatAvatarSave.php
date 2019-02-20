<?php
    /**
     * Created by PhpStorm.
     * User: Chaos
     * Date: 2018/9/19
     * Time: 17:17
     */

    class WechatAvatarSave
    {
        public function save_avatar($url)
        {
            $header = array(
                'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:45.0) Gecko/20100101 Firefox/45.0',
                'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
                'Accept-Encoding: gzip, deflate',
            );

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            $data = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if ($code == 200) {  //把URL格式的图片转成base64_encode格式的！
                $imgBase64Code = "data:image/jpeg;base64," . base64_encode($data);
            }
            $img_content = $imgBase64Code;  //图片内容
            //echo $img_content;exit;
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $img_content, $result)) {
                $type     = $result[2];//得到图片类型png?jpg?gif?
                if (@mkdir(APPLICATION_PATH . '/../file/avatar')) {
                }
                $add      = '/avatar/' . date('YmdHis', time()) . rand(100, 999) . '.' . $type;
                $new_file = APPLICATION_PATH . '/../file' . $add;
                //                $new_file = "./cs/cs.{$type}";
                if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $img_content)))) {
                    return $add;
                }

                return '';
            }
        }
    }