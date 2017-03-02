<?php
include(__DIR__.'/includes/JSMin.class.php');
include(__DIR__.'/includes/CSSMin.class.php');
/**
 * CSS、JS打包压缩工具，支持多个文件以及目录打包压缩.
 * 
 * @author john
 *
 * @version v0.1 2014-03-11
 */
class Lib_Compress_Compressor
{
    /**
     * 打包压缩css或者js内容.
     * 
     * @param string $content 内容.
     * @param string $type    类型(js|css).
     * 
     * @return string
     */
    static public function minify($content, $type = 'js')
    {
        $result = '';
        switch ($type) {
            case 'js':
                $result = JSMin::minify($content);
                break;
            case 'css':
                $result = CSSMin::minify($content);
                break;
            default:
                $result = $content;
                break;
        }
        return $result;
    }

    /**
     * 打包压缩多个css或者js文件.
     * 
     * @param array $files 文件地址构成的数组
     * @param string $type    类型(js|css).
     * 
     * @return string
     */
    static public function minifyFiles($files, $type = 'js')
    {
        $content = '';
        switch ($type) {
            case 'js':
                $content = JSMin::minifyFiles($files);
                break;
                
            default:
                foreach ($files as $file) {
                    $content .= self::minify(file_get_contents($file), $type);
                }
                break;
        }
        return $content; 
    }
}

