<?php
namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * 图片验证码类
 * @example 
 * require_once('ValiImage.class.php');
 * $valiImage = new ValiImage();
 * $valiImage->createImage();
 */

class Lib_Image_Validator
{
    private $height;          // 验证码图片高度
    private $width;           // 验证码图片宽度
    private $textNum;         // 验证码字符个数
    private $textContent;     // 验证码字符内容
    private $fontColor;       // 字符颜色
    private $randFontColor;   // 随机出的文字颜色
    private $fontSize;        // 字体大小
    private $fontFamily;      // 字体
    private $bgColor;         // 背景颜色
    private $randBgColor;     // 随机出的背景颜色
    private $textLang;        // 字符语言
    private $noisePoint;      // 干扰点数量
    private $noiseLine;       // 干扰线数量
    private $distortion;      // 是否扭曲
    private $distortionImage; // 扭曲图片源
    private $showBorder;      // 是否有边框
    private $seedsType;       // 验证码类型(请查看_strRand方法)
    private $image;           // 验证码图片源

    public function __construct(){
        $this->textNum    = 4;
        $this->fontSize   = 16;
        $this->fontFamily = dirname(__FILE__).'/fonts/arial.ttf';
        $this->textLang   = 'en';
        $this->noisePoint = 30;
        $this->noiseLine  = 3;
        $this->distortion = false;
        $this->showBorder = false;
    }

    /**
     * 设置图片宽度
     *
     * @param int $w
     */
    public function setWidth($w){
        $this->width = $w;
    }

    /**
     * 设置图片高度
     *
     * @param int $h
     */
    public function setHeight($h){
        $this->height = $h;
    }

    /**
     * 设置字符个数
     *
     * @param int $textN
     */
    public function setTextNumber($textN){
        $this->textNum = $textN;
    }

    /**
     * 设置验证码内容类型.
     *
     * @param integer $seeds 内容类型.
     */
    public function setSeedsType($seeds)
    {
        $this->seedsType = $seeds;
    }

    /**
     * 设置字符颜色
     *
     * @param string $fc
     */
    public function setFontColor($fc){
        $this->fontColor = sscanf($fc, '#%2x%2x%2x');
    }

    /**
     * 设置字号
     *
     * @param int $n
     */
    public function setFontSize($n){
        $this->fontSize = $n;
    }

    /**
     * 设置字体
     *
     * @param string $ffUrl
     */
    public function setFontFamily($ffUrl){
        $this->fontFamily = $ffUrl;
    }

    /**
     * 设置字符语言
     *
     * @param string $lang en | cn
     */
    public function setTextLang($lang){
        $this->textLang = $lang;
    }

    /**
     * 设置背景颜色
     *
     * @param string $bc
     */
    public function setBgColor($bc){
        $this->bgColor = sscanf($bc, '#%2x%2x%2x');
    }

    /**
     * 设置干扰点数量
     *
     * @param int $n
     */
    public function setNoisePoint($n){
        $this->noisePoint = $n;
    }

    /**
     * 设置干扰线数量
     *
     * @param int $n
     */
    public function setNoiseLine($n){
        $this->noiseLine = $n;
    }

    /**
     * 设置是否扭曲
     *
     * @param boolean $b
     */
    public function setDistortion($b){
        $this->distortion=$b;
    }


    /**
     * 设置是否显示边框
     *
     * @param boolean $border
     */
    public function setShowBorder($border){
        $this->showBorder = $border;
    }

    /**
     * 初始化验证码图片
     *
     */
    public function initImage(){
        if(empty($this->width)){
            $this->width = floor($this->fontSize*1.3)*$this->textNum + 10;
        }
        if(empty($this->height)){
            $this->height = $this->fontSize*2;
        }
        $this->image = imagecreatetruecolor($this->width, $this->height);
        if(empty($this->bgColor)){
            $this->randBgColor = imagecolorallocate($this->image, mt_rand(100,255), mt_rand(100,255), mt_rand(100,255));
        }else{
            $this->randBgColor = imagecolorallocate($this->image, $this->bgColor[0], $this->bgColor[1], $this->bgColor[2]);
        }
        imagefill($this->image, 0, 0, $this->randBgColor);
    }

    /**
     * 产生随机字符
     *
     * @param string $type en | cn
     * @return string
     */
    public function randText($type){
        $string='';
        switch($type){
            case 'en':
                $string = $this->_strRand($this->textNum);
                break;
            case 'cn':
                for($i=0; $i < $this->textNum; $i++) {
                    $string = $string.','.chr(rand(0xB0,0xCC)).chr(rand(0xA1,0xBB));
                }
                $string = iconv('GB2312', 'UTF-8', $string); //转换编码到utf8
                break;
        }
        //return substr($string, 1);
        return $string;
    }
    
    /**
     * 输出文字到验证码
     *
     */
    public function createText() {
        $textArray = explode(',', $this->randText($this->textLang));
        $this->textContent = join('', $textArray);
        if(empty($this->fontColor)){
            $this->randFontColor = imagecolorallocate($this->image, mt_rand(0,100), mt_rand(0,100), mt_rand(0,100));
        }else{
            $this->randFontColor = imagecolorallocate($this->image, $this->fontColor[0], $this->fontColor[1], $this->fontColor[2]);
        }
        for($i=0; $i < $this->textNum; $i++){
            if (isset($textArray[$i])) {
                $angle = mt_rand(-1, 1)*mt_rand(1, 20);
                imagettftext($this->image, $this->fontSize, $angle, 5 + $i*floor($this->fontSize*1.3), 
                    floor($this->height*0.75), $this->randFontColor, $this->fontFamily, $textArray[$i]);
            }
        }
    }

    /**
     * 生成干扰点
     *
     */
    public function createNoisePoint(){
        for($i=0; $i < $this->noisePoint; $i++){
            $pointColor = imagecolorallocate($this->image, mt_rand(0,255), mt_rand(0,255), mt_rand(0,255));
            imagesetpixel($this->image, mt_rand(0, $this->width), mt_rand(0, $this->height), $pointColor);
        }
    }

    /**
     * 产生干扰线
     *
     */
    public function createNoiseLine(){
        for($i=0; $i < $this->noiseLine; $i++) {
            $lineColor=imagecolorallocate($this->image, mt_rand(0,255), mt_rand(0,255), 20);
            imageline($this->image, 0, mt_rand(0, $this->width), $this->width,mt_rand(0, $this->height), $lineColor);
        }
    }

    /**
     * 扭曲文字
     *
     */
    public function distortionText(){
        $this->distortionImage = imagecreatetruecolor($this->width, $this->height);
        imagefill($this->distortionImage, 0, 0, $this->randBgColor);
        for($x=0; $x < $this->width; $x++){
            for($y=0; $y < $this->height; $y++){
                $rgbColor=imagecolorat($this->image, $x, $y);
                imagesetpixel($this->distortionImage, (int)($x + sin($y/$this->height*2*M_PI-M_PI*0.5)*3), $y, $rgbColor);
            }
        }
        $this->image = $this->distortionImage;
    }

    /**
     * 生成验证码图片
     *
     * @return binary
     */
    public function createImage(){
        $this->initImage(); //创建基本图片
        $this->createText(); //输出验证码字符
        //扭曲文字
        if($this->distortion){
            $this->distortionText();
        } 
        //产生干扰点
        $this->createNoisePoint(); 
        //产生干扰线
        $this->createNoiseLine(); 
        //添加边框
        if($this->showBorder){
            imagerectangle($this->image, 0, 0, $this->width-1, $this->height-1, $this->randFontColor);
        } 
        imagepng($this->image);
        imagedestroy($this->image);
        if($this->distortion){
            imagedestroy($this->distortionImage);
        }
        return $this->textContent;
    }
    
    /**
     * 获得随机的字符
     *
     * @param int $length 字符长度
     * @param int $seeds  字符范围
     * @return string
     */
    private function _strRand($length = 8, $seeds = 2)
    {
        if (isset($this->seedsType)) {
            $seeds = $this->seedsType;
        }
        $seedings[0] = '0123456789';
        $seedings[1] = 'abcdefghijklmnopqrstuvwqyz';
        $seedings[2] = 'abcdefghijklmnopqrstuvwqyz0123456789';
        $seedings[3] = '0123456789abcdef';
        $seedings[4] = 'ABCDEFGHIJKLMNOPQRSTUVWQYZ';
        $seedings[5] = 'ABCDEFGHIJKLMNOPQRSTUVWQYZ0123456789';
        $seedings[6] = 'ABCDEFGHIJKLMNOPQRSTUVWQYZabcdefghijklmnopqrstuvwqyz0123456789';
        if (isset($seedings[$seeds])) {
            $seeds = $seedings[$seeds];
        }
        list($usec, $sec) = explode(' ', microtime());
        $seed = (float)$sec + ((float)$usec * 100000);
        mt_srand($seed);
        $str = '';
        $seeds_count = strlen($seeds);
        for ($i = 0;  $i < $length; $i++) {
            $str .= $seeds{mt_rand(0, $seeds_count - 1)};
        }
        return $str;
    }
}
