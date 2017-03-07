<?php
namespace Lge;

if(!defined('LGE')){
	exit('Include Permission Denied!');
}

class Controller_Image extends Controller_Base
{
    /**
     * 自动生成符合大小的缩略图片.
     * 
     */
    public function auto()
    {
        $src    = urldecode($this->_get['src']);
        $dst    = urldecode($this->_get['dst']);
        $width  = intval($this->_get['w']);
        $height = intval($this->_get['h']);
        if ($width > 1000 || $height > 1000) {
            echo "error size";
            return ;
        } else {
            $dirPath = rtrim(ROOT_PATH, '/');
            $srcPath = $dirPath.$src;
            $dstPath = $dirPath.$dst;
            if (Lib_FileSys::isImage($srcPath) && file_exists($srcPath)) {
                if (Lib_Image_Utility::makeThumb($srcPath, $dstPath, $width, $height)) {
                    $type = substr($dstPath, strrpos($dstPath, '.') + 1);
                    header("Content-Type:image/{$type}");
                    readfile($dstPath);
                }
            } else {
                echo 'error src path';
                return ;
            }
        }
    }
}
