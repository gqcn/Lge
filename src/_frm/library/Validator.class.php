<?php
namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * 表单/数据校验类.
 * $rules = array(
 *     'true_name' => 'required',
 *     'user_pass' => array('required|length:6,24', array('required' => '用户名不能为空', 'length' => '用户名长度为6到24个字符')),
 *
 * );
 *
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
        'required_if'       => '字段不能为空',                // required_if:field,value,...
        'required_with'     => '字段不能为空',                // required_with:foo,bar,...
        'required_with_all' => '字段不能为空',                // required_with_all:foo,bar,...
        'date'              => '日期格式不正确',
        'date_format'       => '日期格式不正确',              // date_format:format
        'email'             => '邮箱地址格式不正确',
        'phone'             => '手机号码格式不正确',
        'ip'                => 'IP地址格式不正确',
        'mac'               => 'MAC地址格式不正确',
        'url'               => 'URL地址格式不正确',
        'length'            => '字段长度为:min到:max个字符',   // length:min,max
        'min_length'        => '字段最小长度为:min',
        'max_length'        => '字段最大长度为:max',
        'between'           => '字段大小为:min到:max',        // between:min,max
        'min'               => '字段最小值为:min',
        'max'               => '字段最大值为:max',
        'json'              => '字段应当为JSON格式',
        'array'             => '字段应当为数组',
        'integer'           => '字段应当为整数',
        'float'             => '字段应当为浮点数',
        'boolean'           => '字段应当为布尔值',
        'same'              => '字段值不合法',                // same:field
        'different'         => '字段值不合法',                // different:field
        'in'                => '字段值不合法',                // in:foo,bar,...
        'not_in'            => '字段值不合法',                // not_in:foo,bar,...

    );

    /**
     * 当前校验的数据数组.
     * @var array
     */
    private static $_currentData = array();

    /**
     * 根据规则验证数组，如果返回值为空那么表示满足规则，否则返回值为错误信息数组.
     *
     * @param array $data  数据数组.
     * @param array $rules 规则数组.
     * @return array
     */
    public static function check(array $data, array $rules) {
        $result             = array();
        self::$_currentData = $data;
        foreach ($rules as $key => $rule) {
            if (isset($data[$key])) {
                $r = self::checkRule($data[$key], $rule);
                if (!empty($r)) {
                    $result[$key] = $r;
                }
            }
        }
        return $result;
    }

    /**
     * 根据单条规则验证数值，如果返回值为空那么表示满足规则，否则返回值为错误信息数组.
     *
     * @param mixed $value 数值.
     * @param mixed $rule  规则.
     * @return array
     */
    private static function checkRule($value, $rule) {
        $result   = array();
        $messages = array();
        if (is_array($rule)) {
            $ruleString = $rule[0];
            $messages   = $rule[1];
        } else {
            $ruleString = $rule;
        }
        $ruleArray = explode('|', $ruleString);
        foreach ($ruleArray as $ruleItem) {
            $ruleMatch   = true;
            $ruleMessage = '';
            $tmpArray    = explode(':', $ruleItem);
            $ruleName    = isset($tmpArray[0]) ? $tmpArray[0] : null;
            $ruleAttr    = isset($tmpArray[1]) ? $tmpArray[1] : null;
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
                case 'in':
                    $ruleMatch = !in_array($value, explode(',', $ruleAttr));
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
            }
            // 错误信息判断
            if (!empty($ruleMessage)) {
                $result[$ruleName] = $ruleMessage;
            } elseif (!$ruleMatch) {
                $result[$ruleName] = isset($messages[$ruleName]) ? $messages[$ruleName] : self::$defaultMessages[$ruleName];
            }
        }
        return $result;
    }
}