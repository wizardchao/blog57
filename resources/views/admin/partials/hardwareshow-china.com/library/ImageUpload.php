<?php
/**
 * @package library
 */

/**
 * 图片上传
 * 
 * @package library
 * @author zqy
 * @version 1.0
 */
class ImageUpload
{
	protected $server_name = ''; //图片访问域名
    
    protected $image_base_path = '';
    
    protected $upload_dir = '';
    
    protected $thumb_dir_name = 'thumbs'; //缩略图片目录名
    
    protected $max_size = '5242880'; //文件大小限制 默认5M 
    
    public function __construct($options = array()) {
        if (isset($options['server_name']) && $options['server_name'])
        {
            $this->setServerName($options['server_name']);
        }
        
        if (isset($options['image_base_path']) && $options['image_base_path'])
        {
            $this->setImageBasePath($options['image_base_path']);
        }
        
        if (isset($options['max_size']) && $options['max_size'] > 0)
        {
            $this->setMaxSize($options['max_size']);
        }
    }
    
    public function setServerName($server_name)
    {
        $server_name && $this->server_name = $server_name;
        return $this;
    }
    
    public function setImageBasePath($image_base_path)
    {
        $image_base_path && $this->image_base_path = $image_base_path;
        return $this;
    }
    
    public function setMaxSize($max_size)
    {
        $max_size > 0 && $this->max_size = $max_size;
        return $this;
    }
    
    /**
     * 图片上传
     * 
     * @param type $image_source
     * @return type 
     */
    public function upload($image_source)
    {
        $image_size = $image_source['size'];
        $image_name = $this->getImageName();
        $image_path = $this->getImagePath($image_name);
        $image_url = $this->getImageUrl($image_path);
        $image_storge_path = $this->getStorgePath($image_path);
        $image_storge_dir = dirname($image_storge_path);
        
        if ($image_size > $this->max_size)
        {
            return array(
                'error' => 3,
                'message' => '图片大小超过' . floor($this->max_size / (1024*1024)) . 'M,请重新选择上传图片',
            );
        }
        
        if ($this->dirIsExist($image_storge_dir) == false)
        {
            if ($this->makeDir($image_storge_dir) == false)
            {
                return array(
                    'error' => 2,
                    'message' => $image_storge_dir . '目录创建失败'
                );
            }
        }
        //print_r($image_source['tmp_name']);
        //print_r($image_storge_path);exit;
        if (move_uploaded_file($image_source['tmp_name'], $image_storge_path))
        {
            $image_info = getimagesize($image_storge_path);
            return array(
                'error' => 0,
                'message' => '上传成功',
                'data' => array(
                    'image_url' => $image_url, //图片访问路径
                    'image_storge_path' => $image_storge_path, //图片存储路径
                    'image_size' => $image_size,
                    'image_width' => $image_info[0],
                    'image_height' => $image_info[1],
                )
            );
        } else {
            return array(
                'error' => 1,
                'message' => '文件上传失败'
            );
        }
    }
    
    /**
    * 生成缩略图
    * @param string     源图绝对完整地址{带文件名及后缀名}
    * @param string     目标图绝对完整地址{带文件名及后缀名}
    * @param int        缩略图宽{0:此时目标高度不能为0，目标宽度为源图宽*(目标高度/源图高)}
    * @param int        缩略图高{0:此时目标宽度不能为0，目标高度为源图高*(目标宽度/源图宽)}
    * @param int        是否裁切{宽,高必须非0}
    * @param int/float  缩放{0:不缩放, 0<this<1:缩放到相应比例(此时宽高限制和裁切均失效)}
    * @return boolean
    */
    public function imgToThumb($image_path, $width = 75, $height = 75, $cut = 0, $proportion = 0)
    {
        if(!is_file($image_path))
        {
            return false;
        }
        
        $suffix = pathinfo($image_path, PATHINFO_EXTENSION); //返回图片后缀
        $thumb_image_name = $this->getImageName($suffix); //返回缩略图片名称
        $thumb_image_path = $this->getThumbPath($thumb_image_name); //返回图片相对路径
        $thumb_storge_path = $this->getStorgePath($thumb_image_path); //返回图片存储路径
        $thumb_image_url = $this->getImageUrl($thumb_image_path); //返回图片访问路径
        $thumb_dir = dirname($thumb_storge_path);
        
        if ($this->dirIsExist($thumb_dir) == false)
        {
            $this->makeDir($thumb_dir);
        }
        
        
        $otfunc = 'image' . ($suffix == 'jpg' ? 'jpeg' : $suffix);
        $image_sorce = getimagesize($image_path);
        $image_width = $image_sorce[0];
        $image_height = $image_sorce[1];
        $type  = strtolower(substr(image_type_to_extension($image_sorce[2]), 1));
        $create_image_fun = 'imagecreatefrom' . ($type == 'jpg' ? 'jpeg' : $type);

        $thumb_height = $height;
        $thumb_width = $width;
        $x = $y = 0;

        /**
        * 缩略图不超过源图尺寸（前提是宽或高只有一个）
        */
        if(($width> $image_width && $height> $image_height) || ($height> $image_height && $width == 0) || ($width> $image_width && $height == 0))
        {
            $proportion = 1;
        }
        if($width> $image_width)
        {
            $thumb_width = $width = $image_width;
        }
        if($height> $image_height)
        {
            $thumb_height = $height = $image_height;
        }

        if(!$width && !$height && !$proportion)
        {
            return false;
        }
        if(!$proportion)
        {
            if($cut == 0)
            {
                if($thumb_width && $thumb_height)
                {
                    if($thumb_width/$image_width> $thumb_height/$image_height)
                    {
                        $thumb_width = $image_width * ($thumb_height / $image_height);
                        $x = 0 - ($thumb_width - $width) / 2;
                    }
                    else
                    {
                        $thumb_height = $image_height * ($thumb_width / $image_width);
                        $y = 0 - ($thumb_height - $height) / 2;
                    }
                }
                else if($thumb_width xor $thumb_height)
                {
                    if($thumb_width && !$thumb_height)  //有宽无高
                    {
                        $propor = $thumb_width / $image_width;
                        $height = $thumb_height  = $image_height * $propor;
                    }
                    else if(!$thumb_width && $thumb_height)  //有高无宽
                    {
                        $propor = $thumb_height / $image_height;
                        $width  = $thumb_width = $image_width * $propor;
                    }
                }
            }
            else
            {
                if(!$thumb_height)  //裁剪时无高
                {
                    $height = $thumb_height = sprintf('%0.2f', $width / $image_width) * $image_height;
                }
                if(!$thumb_width)  //裁剪时无宽
                {
                    $width = $thumb_width = sprintf('%0.2f',$height / $image_height) * $image_width;
                }
                $propor = min(max($thumb_width / $image_width, $thumb_height / $image_height), 1);
                $thumb_width = (int)round($image_width * $propor);
                $thumb_height = (int)round($image_height * $propor);
                $x = ($width - $thumb_width) / 2;
                $y = ($height - $thumb_height) / 2;
            }
        }
        else
        {
            $proportion = min($proportion, 1);
            $height = $thumb_height = $image_height * $proportion;
            $width  = $thumb_width = $image_width * $proportion;
        }

        $src = $create_image_fun($image_path);
        $dst = imagecreatetruecolor($width ? $width : $thumb_width, $height ? $height : $thumb_height);
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);

        if(function_exists('imagecopyresampled'))
        {
            imagecopyresampled($dst, $src, $x, $y, 0, 0, $thumb_width, $thumb_height, $image_width, $image_height);
        }
        else
        {
            imagecopyresized($dst, $src, $x, $y, 0, 0, $thumb_width, $thumb_height, $image_width, $image_height);
        }
        $otfunc($dst, $thumb_storge_path);
        imagedestroy($dst);
        imagedestroy($src);
        $thumb_image_size = filesize($thumb_storge_path);
        $thumb_image_info = getimagesize($thumb_storge_path);
        return array(
            'image_url' => $thumb_image_url,
            'image_store_path' => $thumb_storge_path,
            'image_size' => $thumb_image_size,
            'image_width' => $thumb_image_info[0],
            'image_height' => $thumb_image_info[1],
        );
    }


    public function checkSize($size)
    {
        if ($size > $this->max_size)
        {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 返回图片文件名
     * 
     * @param type $suffix
     * @return type 
     */
    protected function getImageName($suffix = 'jpg')
    {
        $str = str_shuffle('abcdefghijklmnopqrtsxyz0123456789');
        return md5(microtime() . $str) . '.' . $suffix;
    }

    /**
     * 返回图片目录名
     * 
     * @return type 
     */
    protected function getImageDirName()
    {
        return str_replace('-', '/', Star_Date::date());
    }

    /**
     * 返回图片相对路径
     * 
     * @param type $image_name
     * @return type 
     */
    protected function getImagePath($image_name)
    {
        $dir_name = $this->getImageDirName();
        return '/images/' . $dir_name . '/' . $image_name;
    }
    
    /**
     * 返回图片缩略图相对路径
     * 
     * @param type $image_name
     * @return type 
     */
    protected function getThumbPath($image_name)
    {
        $thumb_dir_name = $this->thumb_dir_name;
        $dir_name = $this->getImageDirName();
        return '/' . $thumb_dir_name . '/' . $dir_name . '/' . $image_name;
    }
    
    protected function getServerName()
    {
        return $this->server_name;
    }
    
    /**
     * 图片访问路径
     * 
     * @param type $file_name
     * @return type 
     */
    protected function getImageUrl($image_path)
    {
        $server_name = $this->getServerName();
        return $server_name  . $image_path;
    }
    
    /**
     * 图片存储路径
     * 
     * @param type $file_name 
     */
    protected function getStorgePath($image_path)
    {
        return $this->image_base_path . $image_path;
    }
        
    /**
     * 验证目录是否存在
     * 
     * @param type $image_storge_dir
     * @return type 
     */
    protected function dirIsExist($image_storge_dir)
    {
        return is_dir($image_storge_dir);
    }
    
    /**
     * 创建存储目录
     * 
     * @param type $image_storge_path 
     */
    protected function makeDir($image_storge_dir)
    {
        if (is_dir($image_storge_dir) == false)
        {
            return mkdir($image_storge_dir, 0777, true);
        } else {
            return false;
        }
    }
}

