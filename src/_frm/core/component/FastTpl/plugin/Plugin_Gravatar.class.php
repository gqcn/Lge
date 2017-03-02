<?php
/**
 * Gravatar全球通用头像API管理对象.
 * 
 * @author john
 */

class Plugin_Gravatar
{
    
    /**
     * 根据邮件地址获取用户头像.
     *
     * @param  string $email 邮件地址.
     * @param  string $s     头像大小，默认80px[1-512].
     * @param  string $d     默认的图片集[ 404| mm | identicon | monsterid | wavatar]
     * @param  string $r     最大额定值 [ g | pg | r | x ]
     * @param  boole $img    true返回图片内容，false返回图片地址.
     * @param  array $atts   附加参数.
     * @return string
     */
    public function get($email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array() ) {
        $url  = 'http://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($email)));
        $url .= "?s={$s}&d={$d}&r={$r}";
        if ($img) {
            $url = '<img src="' . $url . '"';
            foreach ($atts as $key => $val)
                $url .= ' '.$key.'="'.$val.'"';
            $url .= ' />';
        }
        return $url;
    }
}