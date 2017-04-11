<?php
namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * 表单/数据校验类.

    $data  = array(
        'username'  => '',
        'userpass'  => '1234567',
        'userpass2' => '123456',
    );
    // 规则格式1
    $rules = array(
        'username'  => 'required',
        'userpass'  => array('required', '用户密码不能为空'),
        'userpass2' => array('required|same:userpass', array('请再次输入密码进行确认', '您两次输入的密码不一致')),
    );

    // 规则格式2
    $rules = array(
        'username'  => 'required',
        'userpass'  => array('required', '用户密码不能为空'),
        'userpass2' => array('required|same:userpass', array(
            'required' => '请再次输入密码进行确认',
            'same'     => '您两次输入的密码不一致')
        ),
    );

    校验规则如下：
    required           格式：required                      说明：必需参数
    required_if        格式：required_if:field,value,...   说明：必需参数(当给定字段值与所给任意值相等时)
    required_with      格式：required_with:foo,bar,...     说明：必需参数(当所给定任意字段值不为空时)
    required_with_all  格式：required_with_all:foo,bar,... 说明：必须参数(当所给定所有字段值都不为空时)
    date               格式：date                          说明：参数日期类型(使用strtotime进行判断)
    date_format        格式：date_format:format            说明：判断日期是否为制定格式，format为PHP标准的日期格式
    email              格式：email                         说明：邮件
    phone              格式：phone                         说明：手机号
    ip                 格式：ip                            说明：IP地址(IPv4|IPv6)
    mac                格式：mac                           说明：MAC地址
    url                格式：url                           说明：URL
    length             格式：length:min,max                说明：参数长度为min到max
    min_length         格式：min_length:min                说明：参数长度最小为min
    max_length         格式：max_length:max                说明：参数长度最大为max
    between            格式：between:min,max               说明：参数大小为min到max
    min                格式：min:min                       说明：参数最小为min
    max                格式：max:max                       说明：参数最大为max
    json               格式：json                          说明：JSON
    array              格式：array                         说明：数组
    integer            格式：integer                       说明：整数
    float              格式：float                         说明：浮点数
    boolean            格式：boolean                       说明：布尔值(1,true,on,yes:true | 0,false,off,no,"":false)
    same               格式：same:field                    说明：参数值必需与field参数的值相同
    different          格式：different:field               说明：参数值不能与field参数的值相同
    in                 格式：in:foo,bar,...                说明：参数值应该在foo,bar,...中
    not_in             格式：not_in:foo,bar,...            说明：参数值不应该在foo,bar,...中
    regex              格式：regex:pattern                 说明：参数值应当满足正则匹配规则pattern(使用preg_match判断)

 * @author John
 */
class Lib_Validator
{
    /**
     * 默认校验错误提示信息.
     * @var array
     */
    public static $defaultMessages = array(
        'required'          => '字段不能为空',
        'required_if'       => '字段不能为空',
        'required_with'     => '字段不能为空',
        'required_with_all' => '字段不能为空',
        'date'              => '日期格式不正确',
        'date_format'       => '日期格式不正确',
        'email'             => '邮箱地址格式不正确',
        'phone'             => '手机号码格式不正确',
        'ip'                => 'IP地址格式不正确',
        'mac'               => 'MAC地址格式不正确',
        'url'               => 'URL地址格式不正确',
        'length'            => '字段长度为:min到:max个字符',
        'min_length'        => '字段最小长度为:min',
        'max_length'        => '字段最大长度为:max',
        'between'           => '字段大小为:min到:max',
        'min'               => '字段最小值为:min',
        'max'               => '字段最大值为:max',
        'json'              => '字段应当为JSON格式',
        'array'             => '字段应当为数组',
        'integer'           => '字段应当为整数',
        'float'             => '字段应当为浮点数',
        'boolean'           => '字段应当为布尔值',
        'same'              => '字段值不合法',
        'different'         => '字段值不合法',
        'in'                => '字段值不合法',
        'not_in'            => '字段值不合法',
        'regex'             => '字段值不合法',
    );

    /**
     * 当前校验的数据数组.
     * @var array
     */
    private static $_currentData = array();

    /**
     * 根据规则验证数组，如果返回值为空那么表示满足规则，否则返回值为错误信息数组.
     *
     * @param array   $data            数据数组.
     * @param array   $rules           规则数组.
     * @param integer $returnErrorType 返回错误的类型(
     *     0：保持字段及规则名称的三级关联数组；
     *     1：仅返回错误信息，构成数组返回；
     *     2：仅返回错误信息，如果$returnWhenError为true或者仅有一条错误时，返回错误字符串；
     * ).
     * @param bool    $returnWhenError 当错误产生时立即返回错误并停止检测(这个时候返回的是第一个错误).
     * @return array|string
     */
    public static function check(array $data, array $rules, $returnErrorType = 0, $returnWhenError = false) {
        $result             = array();
        self::$_currentData = $data;
        foreach ($rules as $key => $rule) {
            if (isset($data[$key])) {
                $r = self::checkRule($data[$key], $rule, $returnWhenError);
                if (!empty($r)) {
                    $result[$key] = $r;
                    if ($returnWhenError) {
                        break;
                    }
                }
            }
        }
        if (!empty($result) && $returnErrorType > 0) {
            $tempArray = array();
            foreach ($result as $field => $item) {
                if (is_array($item)) {
                    foreach ($item as $k => $v) {
                        $tempArray[] = $v;
                    }
                }
            }
            if ($returnErrorType == 2 && count($tempArray) == 1) {
                $result = $tempArray[0];
            } else {
                $result = $tempArray;
            }
        }
        return $result;
    }

    /**
     * 根据单条规则验证数值，如果返回值为空那么表示满足规则，否则返回值为错误信息数组.
     *
     * @param mixed $value           数值.
     * @param mixed $rule            规则.
     * @param bool  $returnWhenError 当错误产生时立即返回错误并停止检测(这个时候返回的是第一个错误).
     * @return array
     */
    private static function checkRule($value, $rule, $returnWhenError = false) {
        $result   = array();
        $messages = array();
        if (is_array($rule)) {
            $ruleString = $rule[0];
            $messages   = $rule[1];
            if (!is_array($messages)) {
                $messages = array($messages);
            }
        } else {
            $ruleString = $rule;
        }
        if (preg_match("/(regex:\/.+\/\w*)/", $ruleString, $ruleStringMatch)) {
            $tempString  = preg_replace("/(regex:\/.+\/\w*)/", '', $ruleString);
            $ruleArray   = explode('|', $tempString);
            $ruleArray[] = $ruleStringMatch[1];
        } else {
            $ruleArray   = explode('|', $ruleString);
        }
        foreach ($ruleArray as $ruleIndex => $ruleKey) {
            if (empty($ruleKey)) {
                continue;
            }
            $ruleMatch   = true;
            $ruleMessage = '';
            preg_match("/^(\w+):{0,1}(.*)/", $ruleKey, $ruleKeyMatch);
            $ruleName = isset($ruleKeyMatch[1]) ? $ruleKeyMatch[1] : null;
            $ruleAttr = isset($ruleKeyMatch[2]) ? $ruleKeyMatch[2] : null;
            if (empty($ruleName)) {
                continue;
            }
            switch ($ruleName) {
                // 必须字段
                case 'required':
                    $ruleMatch = !empty($value);
                    break;

                // 必须字段(当给定字段值与所给任意值相等时)
                case 'required_if':
                    $tmpArray = explode(',', $ruleAttr);
                    if (isset($tmpArray[0]) && isset(self::$_currentData[$tmpArray[0]])) {
                        $fieldValue = self::$_currentData[$tmpArray[0]];
                        unset($tmpArray[0]);
                        if (in_array($fieldValue, $tmpArray) && empty($value)) {
                            $ruleMatch = false;
                        }
                    }
                    break;

                // 必须字段(当所给定任意字段值不为空时)
                case 'required_with':
                    $ruleMatch = false;
                    $tmpArray  = explode(',', $ruleAttr);
                    foreach ($tmpArray as $v) {
                        if (!empty(self::$_currentData[$v])) {
                            $ruleMatch = true;
                            break;
                        }
                    }
                    break;

                // 必须字段(当所给定所有字段值都不为空时)
                case 'required_with_all':
                    $tmpArray  = explode(',', $ruleAttr);
                    foreach ($tmpArray as $v) {
                        if (empty(self::$_currentData[$v])) {
                            $ruleMatch = false;
                            break;
                        }
                    }
                    break;

                // 日期格式(使用strtotime判断)
                case 'date':
                    if (!($value instanceof \DateTime) || strtotime($value) === false) {
                        $ruleMatch = false;
                    } else {
                        $date      = date_parse($value);
                        $ruleMatch = checkdate($date['month'], $date['day'], $date['year']);
                    }
                    break;

                // 给定日期判断格式
                case 'date_format':
                    $parsed    = date_parse_from_format($ruleAttr, $value);
                    $ruleMatch = ($parsed['error_count'] === 0 && $parsed['warning_count'] === 0);
                    break;

                // 两字段值应相同(非敏感字符判断，非类型判断)
                case 'same':
                    $ruleMatch = (isset(self::$_currentData[$ruleAttr]) && $value == self::$_currentData[$ruleAttr]);
                    break;

                // 两字段值不应相同(非敏感字符判断，非类型判断)
                case 'different':
                    $ruleMatch = (!isset(self::$_currentData[$ruleAttr]) || $value != self::$_currentData[$ruleAttr]);
                    break;

                // 字段值应当在指定范围中
                case 'in':
                    $ruleMatch = in_array($value, explode(',', $ruleAttr));
                    break;

                // 字段值不应当在指定范围中
                case 'not_in':
                    $ruleMatch = !in_array($value, explode(',', $ruleAttr));
                    break;

                // 自定义正则判断
                case 'regex':
                    $ruleMatch = preg_match($ruleAttr, $value);
                    break;

                /*
                 * 验证所给手机号码是否符合手机号的格式.
                 * 移动：134、135、136、137、138、139、150、151、152、157、158、159、182、183、184、187、188、178(4G)、147(上网卡)；
                 * 联通：130、131、132、155、156、185、186、176(4G)、145(上网卡)；
                 * 电信：133、153、180、181、189 、177(4G)；
                 * 卫星通信：  1349
                 * 虚拟运营商：170
                 */
                case 'phone':
                    $ruleMatch = preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $value) ? true : false;
                    break;

                // 长度范围
                case 'length':
                    $length   = mb_strlen($value, 'utf-8');
                    $tmpArray = explode(',', $ruleAttr);
                    $min      = isset($tmpArray[0]) ? $tmpArray[0] : null;
                    $max      = isset($tmpArray[1]) ? $tmpArray[1] : null;
                    if ($length < $min || $length > $max) {
                        $ruleMatch = false;
                        if (isset($messages[$ruleName])) {
                            $ruleMessage = $messages[$ruleName];
                        } elseif (isset($messages[$ruleIndex])) {
                            $ruleMessage = $messages[$ruleIndex];
                        } else {
                            $ruleMessage = self::$defaultMessages[$ruleName];
                            $ruleMessage = str_ireplace(array(':min', ':max'), array($min, $max), $ruleMessage);
                        }
                    }
                    break;

                // 最小长度
                case 'min_length':
                    $length    = mb_strlen($value, 'utf-8');
                    $minLength = $ruleAttr;
                    if ($length < $minLength) {
                        $ruleMatch = false;
                        if (isset($messages[$ruleName])) {
                            $ruleMessage = $messages[$ruleName];
                        } elseif (isset($messages[$ruleIndex])) {
                            $ruleMessage = $messages[$ruleIndex];
                        } else {
                            $ruleMessage = self::$defaultMessages[$ruleName];
                            $ruleMessage = str_ireplace(':min', $minLength, $ruleMessage);
                        }
                    }
                    break;

                // 最大长度
                case 'max_length':
                    $length    = mb_strlen($value, 'utf-8');
                    $maxLength = $ruleAttr;
                    if ($length > $maxLength) {
                        $ruleMatch = false;
                        if (isset($messages[$ruleName])) {
                            $ruleMessage = $messages[$ruleName];
                        } elseif (isset($messages[$ruleIndex])) {
                            $ruleMessage = $messages[$ruleIndex];
                        } else {
                            $ruleMessage = self::$defaultMessages[$ruleName];
                            $ruleMessage = str_ireplace(':max', $maxLength, $ruleMessage);
                        }
                    }
                    break;

                // 大小范围
                case 'between':
                    $tmpArray = explode(',', $ruleAttr);
                    $min      = isset($tmpArray[0]) ? $tmpArray[0] : null;
                    $max      = isset($tmpArray[1]) ? $tmpArray[1] : null;
                    if ($value < $min || $value > $max) {
                        $ruleMatch = false;
                        if (isset($messages[$ruleName])) {
                            $ruleMessage = $messages[$ruleName];
                        } elseif (isset($messages[$ruleIndex])) {
                            $ruleMessage = $messages[$ruleIndex];
                        } else {
                            $ruleMessage = self::$defaultMessages[$ruleName];
                            $ruleMessage = str_ireplace(array(':min', ':max'), array($min, $max), $ruleMessage);
                        }
                    }
                    break;

                // 最小值
                case 'min':
                    $min = $ruleAttr;
                    if ($value < $min) {
                        $ruleMatch = false;
                        if (isset($messages[$ruleName])) {
                            $ruleMessage = $messages[$ruleName];
                        } elseif (isset($messages[$ruleIndex])) {
                            $ruleMessage = $messages[$ruleIndex];
                        } else {
                            $ruleMessage = self::$defaultMessages[$ruleName];
                            $ruleMessage = str_ireplace(':min', $min, $ruleMessage);
                        }
                    }
                    break;

                // 最大值
                case 'max':
                    $max = $ruleAttr;
                    if ($value > $max) {
                        $ruleMatch = false;
                        if (isset($messages[$ruleName])) {
                            $ruleMessage = $messages[$ruleName];
                        } elseif (isset($messages[$ruleIndex])) {
                            $ruleMessage = $messages[$ruleIndex];
                        } else {
                            $ruleMessage = self::$defaultMessages[$ruleName];
                            $ruleMessage = str_ireplace(':max', $max, $ruleMessage);
                        }
                    }
                    break;

                // 数组
                case 'array':   $ruleMatch = is_array($value);              break;
                // json
                case 'json':    $ruleMatch = json_decode($value) !== false; break;
                // 整数
                case 'integer': $ruleMatch = filter_var($value, FILTER_VALIDATE_INT)     !== false; break;
                // 小数
                case 'float':   $ruleMatch = filter_var($value, FILTER_VALIDATE_FLOAT)   !== false; break;
                // 布尔值(1,true,on,yes:true | 0,false,off,no,"":false)
                case 'boolean': $ruleMatch = filter_var($value, FILTER_VALIDATE_BOOLEAN) !== false; break;
                // 邮件
                case 'email':   $ruleMatch = filter_var($value, FILTER_VALIDATE_EMAIL)   !== false; break;
                // URL
                case 'url':     $ruleMatch = filter_var($value, FILTER_VALIDATE_URL)     !== false; break;
                // IP
                case 'ip':      $ruleMatch = filter_var($value, FILTER_VALIDATE_IP)      !== false; break;
                // MAC地址
                case 'mac':     $ruleMatch = filter_var($value, FILTER_VALIDATE_MAC)     !== false; break;

                default:
                    exception('Invalid rule name:'.$ruleName);
                    break;
            }
            // 错误信息判断
            if (!empty($ruleMessage)) {
                $result[$ruleName] = $ruleMessage;
            } elseif (!$ruleMatch) {
                if (isset($messages[$ruleIndex])) {
                    $result[$ruleName] = $messages[$ruleIndex];
                } else {
                    $result[$ruleName] = isset($messages[$ruleName]) ? $messages[$ruleName] : self::$defaultMessages[$ruleName];
                }
            }

            // 是否在错误产生的时候停止检测
            if (!empty($result) && $returnWhenError) {
                break;
            }
        }
        return $result;
    }
}