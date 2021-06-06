<?php

namespace Weirin\Wechat\MiniApp;

use Weirin\Wechat\AccessToken;
use Weirin\Wechat\Http;
use Weirin\Wechat\Log;

/**
 * Class PHPSDK
 * @package Wechat\MiniApp
 */
class PHPSDK
{
    const JSCODE2SESSION_URL = 'https://api.weixin.qq.com/sns/jscode2session?';

    const API_URL_PREFIX = 'https://api.weixin.qq.com/cgi-bin';

    const WXA_API_URL_PREFIX = 'https://api.weixin.qq.com/wxa';

    // 客服消息
    const CUSTOM_SEND_URL = '/message/custom/send?';
    const MSGTYPE_TEXT = 'text';
    const MSGTYPE_IMAGE = 'image';

    // 模板消息
    const TEMPLATE_SEND_URL = '/message/wxopen/template/send?'; // 发送消息
    const ADD_TEMPLATE_URL = '/wxopen/template/add?'; // 添加模板
    const GET_TEMPLATE_LIST_URL = '/wxopen/template/list?'; // 获取帐号下已存在的模板列表
    const DEL_TEMPLATE_URL = '/wxopen/template/del?'; // 删除指定ID的模板

    // 订阅消息
    const SUBSCRIBE_SEND_URL = '/message/subscribe/send?'; // 发送模板

    // 统一服务消息
    const UNIFORM_MESSAGE_SEND_URL = '/message/wxopen/template/uniform_send?';

    // 获取小程序码
    const GET_WXACODE_UNLIMIT_URL = '/getwxacodeunlimit?'; // 适用于需要的码数量极多的业务场景，永久有效，数量暂无限制

    private $appid;
    private $appsecret;
    private $access_token;
    public  $debug =  false;
    private $_logcallback;

    /**
     * @param $options
     */
    public function __construct($options)
    {
        $this->appid = isset($options['appid'])?$options['appid']:'';
        $this->appsecret = isset($options['appsecret'])?$options['appsecret']:'';
        $this->debug = isset($options['debug'])?$options['debug']:false;
        $this->_logcallback = isset($options['logcallback'])?$options['logcallback']:false;
    }

    /**
     * 获取小程序会话数据:  open_id和session_key
     * @param $code
     * @return bool|mixed
     */
    public function getSession($code)
    {
        $url = self::JSCODE2SESSION_URL . 'appid=' . $this->appid . '&secret=' . $this->appsecret . '&js_code=' . $code . '&grant_type=authorization_code';
        $result = Http::post($url, []);
        if ($result) {
            $json = json_decode($result, true);
            return $json;
        }
        return false;
    }

    /**
     * 发送模板消息
     * @param $data
     * @return bool|mixed
     */
    public function sendTemplate($data)
    {
        if (!$this->access_token && !$this->checkAuth()) {
            return false;
        }

        Log::debug("sendTemplate: access_token=[" .  $this->access_token . "]");

        $result = $this->safeHttpPost(self::API_URL_PREFIX . self::TEMPLATE_SEND_URL, json_encode($data));

        if ($result) {
            $json = json_decode($result, true);
            return $json;
        }
        return false;
    }

    /**
     * 发送模板消息
     * @param $data
     * @return bool|mixed
     */
    public function sendSubscribeMessage($data)
    {
        if (!$this->access_token && !$this->checkAuth()) {
            return false;
        }

        Log::debug("sendTemplate: access_token=[" .  $this->access_token . "]");

        $result = $this->safeHttpPost(self::API_URL_PREFIX . self::SUBSCRIBE_SEND_URL, json_encode($data));

        if ($result) {
            $json = json_decode($result, true);
            return $json;
        }

        return false;
    }

    /**
     * @return bool|mixed
     */
    public function checkAuth($forceRefresh = false)
    {
        $result = $this->getAccessToken($forceRefresh);
        if ($result) {
            $this->access_token = $result;
            return  $this->access_token;
        }
        return false;
    }

    /**
     * 获取文件中的AccessToken
     * @return mixed
     */
    private function getAccessToken($forceRefresh = false)
    {
        $this->access_token = AccessToken::get($this->appid, $this->appsecret, $forceRefresh);
        return $this->access_token;
    }

    /**
     * 封装一个较为稳妥的Http post请求接口
     * 说明: 可以自动纠正一次40001错误(access_token无效)
     * @param $urlHeader
     * @param $param
     * @return bool|mixed
     */
    public function safeHttpPost($urlHeader, $param)
    {
        $result = Http::post($urlHeader . 'access_token=' . $this->access_token, $param);
        if ($result) {
            $json = json_decode($result);
            Log::debug("safeHttpPost: result=>" . $result);
            if(isset($json->errcode) && $json->errcode == 40001) {
                Log::debug("safeHttpPost: 检测到access_token无效错误, 自动恢复!");
                if ($this->checkAuth(true)) {
                    $result = Http::post($urlHeader . 'access_token=' . $this->access_token, $param);
                }
            }
        }
        return $result;
    }

    /**
     * 封装一个较为稳妥的Http get请求接口
     * 说明: 可以自动纠正一次40001错误(access_token无效)
     * @param $urlHeader
     * @return bool|mixed
     */
    private function safeHttpGet($urlHeader)
    {
        $result = Http::get($urlHeader . 'access_token=' . $this->access_token);
        if ($result) {
            $json = json_decode($result);
            Log::debug("safeHttpGet: result=>" . $result);
            if (isset($json->errcode) && $json->errcode == 40001) {
                Log::WARN("safeHttpGet: 检测到access_token无效错误, 自动恢复!");
                if ($this->checkAuth(true)) {
                    $result = Http::get($urlHeader . 'access_token=' . $this->access_token);
                }
            }
        }
        return $result;
    }

    /**
     * 发送客服消息
     * @param array $data 消息结构{"touser":"OPENID","msgtype":"news","news":{...}}
     * @return boolean|array
     */
    public function sendCustomService($data)
    {
        if (!$this->access_token && !$this->checkAuth()) {
            return false;
        }

        $result = $this->safeHttpPost(self::API_URL_PREFIX.self::CUSTOM_SEND_URL, self::jsonEncode($data));
        Log::debug("{$result}");
        if ($result) {
            $json = json_decode($result,true);
            return $json;
        }
        return false;
    }

    /**
     * 发送客服文本消息接口
     *
     * @param $openid
     * @param $text
     * @return boolean|array
     */
    public function sendTextMessage($openid, $text)
    {
        $data = [
            'touser' => $openid,
            'msgtype' => self::MSGTYPE_TEXT,
            'text' => [
                'content' => $text
            ]
        ];
        return $this->sendCustomService($data);
    }

    /**
     * 组合模板并添加至帐号下的个人模板库
     * @param $data
     * @return bool|mixed
     */
    public function addTemplate($data)
    {
        if (!$this->access_token && !$this->checkAuth()) {
            return false;
        }

        $result = $this->safeHttpPost(self::API_URL_PREFIX . self::ADD_TEMPLATE_URL, json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            return $json;
        }
        return false;
    }

    /**
     * 获取已添加的模板列表
     * @return boolean|array
     */
    public function getTemplateList($data)
    {
        if (!$this->access_token && !$this->checkAuth()) {
            return false;
        }

        $result = $this->safeHttpPost(self::API_URL_PREFIX . self::GET_TEMPLATE_LIST_URL, json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            return $json;
        }
        return false;
    }

    /**
     * 删除微信公众号后台指定的模板
     * @param $data
     * @return bool|mixed
     */
    public function delTemplateId($data)
    {
        if (!$this->access_token && !$this->checkAuth())
            return false;
        $result = $this->safeHttpPost(self::API_URL_PREFIX . self::DEL_TEMPLATE_URL, json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            return $json;
        }
        return false;
    }

    /**
     * 微信api不支持中文转义的json结构
     * @param array $arr
     * @return string
     */
    private static function jsonEncode($arr)
    {
        $parts = array ();
        $is_list = false;
        //Find out if the given array is a numerical array
        $keys = array_keys ( $arr );
        $max_length = count ( $arr ) - 1;
        if (($keys[0] === 0)
            && ($keys [$max_length] === $max_length )) { //See if the first key is 0 and last key is length - 1
            $is_list = true;
            for($i = 0; $i < count ( $keys ); $i ++) { //See if each key correspondes to its position
                if ($i != $keys [$i]) { //A key fails at position check.
                    $is_list = false; //It is an associative array.
                    break;
                }
            }
        }
        foreach ( $arr as $key => $value ) {
            if (is_array ( $value )) { //Custom handling for arrays
                if ($is_list)
                    $parts [] = self::jsonEncode ( $value ); /* :RECURSION: */
                else
                    $parts [] = '"' . $key . '":' . self::jsonEncode ( $value ); /* :RECURSION: */
            } else {
                $str = '';
                if (! $is_list)
                    $str = '"' . $key . '":';
                //Custom handling for multiple data types
                if (is_numeric ( $value ) && $value<2000000000)
                    $str .= $value; //Numbers
                elseif ($value === false)
                    $str .= 'false'; //The booleans
                elseif ($value === true)
                    $str .= 'true';
                else
                    $str .= '"' . addslashes ( $value ) . '"'; //All other things
                // :TODO: Is there any more datatype we should be in the lookout for? (Object?)
                $parts [] = $str;
            }
        }
        $json = implode ( ',', $parts );
        if ($is_list)
            return '[' . $json . ']'; //Return numerical JSON
        return '{' . $json . '}'; //Return associative JSON
    }

    /**
     * 返回对应微信公众号的唯一AppID
     * @return string
     */
    public function getAppID()
    {
        return $this->appid;
    }

    /*
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/uniform-message/uniformMessage.send.html
     * @param string $openid
     * @param array $weappTemplateMsg
     * @param array $mpTemplateMsg
     */
    public function sendUniformMessage(string $openid, array $weappTemplateMsg = [], array $mpTemplateMsg = [])
    {
        if (!$this->access_token && !$this->checkAuth()) {
            return false;
        }

        $params = [];

        $params['touser'] = $openid;

        if ($weappTemplateMsg) {
            $params['weapp_template_msg'] = $weappTemplateMsg;
        }

        $params['mp_template_msg'] = $mpTemplateMsg;

        $result = $this->safeHttpPost(self::API_URL_PREFIX.self::UNIFORM_MESSAGE_SEND_URL, self::jsonEncode($params));
        Log::debug("{$result}");
        if ($result) {
            $json = json_decode($result,true);
            return $json;
        }
        return false;
    }

    /*
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/qr-code/wxacode.getUnlimited.html
     * @param string $scene
     * @param string $page
     * @param float $width
     * @param boolean $autoColor
     * @param array $lineColor
     * @param boolean $isHyaline
     */
    function getWxacodeUnlimit(string $scene, string $page = null, float $width = null, bool $autoColor = false, array $lineColor = [], bool $isHyaline = false)
    {
        if (!$this->access_token && !$this->checkAuth()) {
            return false;
        }

        $params = [];

        $params['scene'] = $scene;

        if ($page) {
            $params['page'] = $page;
        }

        if ($width) {
            $params['width'] = $width;
        }

        if ($autoColor) {
            $params['auto_color'] = $autoColor;
        }

        if ($lineColor) {
            $params['line_color'] = $lineColor;
        }

        if ($isHyaline) {
            $params['is_hyaline'] = $isHyaline;
        }

        $result = $this->safeHttpPost(self::WXA_API_URL_PREFIX.self::GET_WXACODE_UNLIMIT_URL, self::jsonEncode($params));

        return $result;
    }
}