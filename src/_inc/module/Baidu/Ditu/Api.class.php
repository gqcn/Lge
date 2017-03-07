<?php
namespace Lge;

/**
 * 百度地图API封装.
 */
class Module_Baidu_Ditu_Api extends BaseModule
{

    /**
     * 获得实例.
     *
     * @return Module_Baidu_Ditu_Api
     */
    public static function instance()
    {
        return self::instanceInternal(__CLASS__);
    }

    public function getGpsByAddress($address)
    {
        $redis    = Instance::redis('cache');
        $redisKey = md5($address);
        $result   = $redis->get($redisKey);
        if (empty($result)) {
            $return  = array();
            $config  = Config::get();
            $http    = new Lib_Network_Http();
            $url     = "{$config['BaiduDitu']['url']}geocoder/v2/?ak={$config['BaiduDitu']['ak']}&";
            $url    .= "address=".urlencode($address)."&output=json";
            $result  = $http->get($url);
            $array   = json_decode($result, true);
            if (empty($array['status']) && !empty($array['result'])) {
                $return = array($array['result']['location']['lng'], $array['result']['location']['lat']);
            }
            $result     = json_encode($return);
            $logContent = "{$address} -> ".$result;
            $this->log($logContent, 'baidu/getGpsByAddress');
            $redis->setex($redisKey, 3600, $result);
        } else {
            $return = json_decode($result, true);
        }

        return $return;
    }

    /**
     * 将坐标转换为百度坐标.
     *
     * @param $lng
     * @param $lat
     * @param $from
     * @param $to
     * @return array
     */
    public function convertGps($lng, $lat, $from = 3, $to = 5)
    {
        $return = array();
        $config = Config::get();
        $http   = new Lib_Network_Http();
        $url    = "{$config['BaiduDitu']['url']}geoconv/v1/?ak={$config['BaiduDitu']['ak']}&";
        $url   .= "coords={$lng},{$lat}&from={$from}&to={$to}&output=json";
        $result = $http->get($url);
        $array  = json_decode($result, true);
        if (empty($array['status']) && !empty($array['result'])) {
            $return = array($array['result'][0]['x'], $array['result'][0]['y']);
        }
        $logContent = json_encode(array($lng, $lat)).' -> '.json_encode($return);
        $this->log($logContent, 'BaiduApi/convertgps');
        return $return;
    }

    /*
     * 根据经纬度获取地址信息
     * @param float $lng 经度
     * @param float $lat 维度
     *
     * @return arr|bool
     *
     * 示例
     * Array
        (
            [adcode] => 510106
            [city] => 成都市
            [country] => 中国
            [direction] => 附近
            [distance] => 6
            [district] => 金牛区
            [province] => 四川省
            [street] => 西北桥东街
            [street_number] => 6号
            [country_code] => 0
        )

     */

    public function getAddressByGps($lng = 'float 经度', $lat = 'float 纬度'){
        $config = Config::get();
        $http   = new Lib_Network_Http();
        //http://api.map.baidu.com/geocoder/v2/?ak=E4805d16520de693a3fe707cdc962045
        //&callback=renderReverse&location=39.983424,116.322987&output=json&pois=1
        $url    = "{$config['BaiduDitu']['url']}geocoder/v2/?ak={$config['BaiduDitu']['ak']}&";
        $url   .= "callback=renderReverse&location={$lat},{$lng}&output=json&pois=0";
        $res    = $http->get($url);
        preg_match('/\((.+)\)/', $res, $matches);
        if(!empty($matches[1])){
            $json = json_decode($matches[1], true);
            if($json['status'] == 0){
                return $json['result']['addressComponent'];
            }
        }
        return false;
    }


    /*
     * 获取百度静态图
     * @param float $lng 经度
     * @param float $lat 维度
     * @param int   zoom 放大等级 1-19
     * @param int   isWx 是否微信坐标，是的话会自动转换为百度坐标
     *
     *
     * @return str
     */
   /* public function getStaticImg($lng = 'float 经度', $lat = 'float 纬度', $zoom = 15 ,$isWx = 1){
         http://api.map.baidu.com/staticimage/v2?ak=E4805d16520de693a3fe707cdc962045
         &mcode=666666&center=116.403874,39.914888&width=300&height=200&zoom=11
        if($isWx == 1){
           list($lng, $lat) = $this->convertGps($lng, $lat);
        }
        $config = Config::get();
        $url    = "{$config['BaiduDitu']['url']}staticimage/v2?ak={$config['BaiduDitu']['ak']}";
        $url   .= "&mcode=666666&center={$lng},{$lat}&zoom={$zoom}&copyright=1&markers={$lng},{$lat}";
        return $url;
    }*/

    /**
     * 生成百度地图静态图片
     * @param string $startLng     起点经度
     * @param string $startLat     起点纬度
     * @param string $endLng       终点经度
     * @param string $endLat       终点纬度
     * @param string $address      地址（在没有起点坐标时，可以使用地址）
     * @param int    $zoom         地图放大等级
     * @param int    $showDistance 是否在地图上展示距离
     * @return string url
     */
    public function getStaticImg($startLng = '起点经度',
                                 $startLat = '起点纬度',
                                 $endLng   = '终点经度',
                                 $endLat   = '终点纬度',
                                 $address  = '地址',
                                 $zoom     = 15,
                                 $showDistance = 1){
        header("Content-type: text/html; charset=utf-8");
        if(!empty($startLng) && !empty($startLat)){
            $coordinates = $startLng . "," .$startLat;
        } else if(!empty($address)) {
            $coordinates = $address;
        }
        // 微信地址转换为百度地址
        if ($showDistance) {
            $distance = Module_Position::instance()->getDistanceBetween2Points($startLng, $startLat, $endLng, $endLat); //计算两点之间的距离
            $distance = number_format($distance / 1000, 2);
        }
        $distance = !empty($distance) ? $distance : '(未知)';
        $config   = Config::get();
        $url      = "{$config['BaiduDitu']['url']}staticimage/v2?ak={$config['BaiduDitu']['ak']}";
        // 是否隐藏工人经纬度
        if (!empty($endLng)) {
            $url .= "&mcode=666666&center={$coordinates}&zoom={$zoom}&copyright=1&markers={$coordinates}|{$endLng},{$endLat}&markerStyles=m,A,0xff0000|m,B,0xff0000";
        } else {
            $url .= "&mcode=666666&center={$coordinates}&zoom={$zoom}&copyright=1&markers={$coordinates}&markerStyles=m,A,0xff0000";
        }
        if ($showDistance) {
            $url .= "&paths={$endLng},{$endLat};{$coordinates}&pathStyles=0xff0000,2,1&labels={$coordinates}&labelStyles=直线距离{$distance}km,1,14,0xffffff,0x000fff,1";
        }
        return $url;
    }
}
