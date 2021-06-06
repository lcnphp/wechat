<?php

namespace Weirin\Wechat;

use Weirin\Wechat\Log as WechatLog;
use stdClass;

/**
 * Class JSSDK
 * @package Wechat
 */
class JSSDK
{
    private $appId;
    private $appSecret;

    private $cache;

    const CACHE_TICKET_ID = 'WECHAT_TICKET';

    /**
     * @param $options
     */
    public function __construct($options)
    {
        $this->appId = isset($options['appid'])?$options['appid']:'';
        $this->appSecret = isset($options['appsecret'])?$options['appsecret']:'';
    }

    /**
     * @return array
     */
    public function getSignContext($url = false)
    {
        $jsapiTicket = $this->getJsApiTicket();

        // 注意 URL 一定要动态获取，不能 hardcode.
        if (!$url) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        }

        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signContext = new stdClass();
        $signContext->appId = $this->appId;
        $signContext->nonceStr = $nonceStr;
        $signContext->timestamp = $timestamp;
        $signContext->url = $url;
        $signContext->signature = $signature;
        $signContext->rawString = $string;

        return $signContext;
    }

    /**
     * @param int $length
     * @return string
     */
    public function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * @return mixed
     */
    public function getJsApiTicket()
    {
        // jsapi_ticket
        $data = null;

        $cacheTicketKey = self::CACHE_TICKET_ID . '_' . $this->appId;
        if(Cache::exists($cacheTicketKey)){
            $data = json_decode(Cache::get($cacheTicketKey));
        }
        if(!$data){
            $data = new stdClass();
            $data->expire_time = null;
            $data->access_token = null;
        }

        WechatLog::debug("getJsApiTicket: cacheTicketKey[" . $cacheTicketKey . "]: " . json_encode($data));
        if ($data->expire_time < time()) {

            $accessToken = AccessToken::get($this->appId, $this->appSecret);//API 的access token

            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = json_decode(Http::get($url));

            WechatLog::debug("getJsApiTicket: result=>" . json_encode($res));
            // 检测到40001access_token失效错误立刻强制刷新,纠正错误[weigong-2016-06-16 16:25:27]
            if(isset($res->errcode) && $res->errcode == 40001) {
                WechatLog::WARN("getJsApiTicket: access_token失效, 自动恢复!");
                $accessToken = AccessToken::get($this->appId, $this->appSecret, true); // 强制刷新access_token
                $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
                $res = json_decode(Http::get($url));
            }

            $ticket = null;
            if (isset($res->ticket)) {

                $ticket = $res->ticket;

                $data->expire_time = time() + 7000;
                $data->jsapi_ticket = $ticket;

                Cache::set($cacheTicketKey, json_encode($data));

                WechatLog::debug("jsapi_ticket刷新: jsapi_ticket=[" .  $data->jsapi_ticket . "], expire_time=["
                    . $data->expire_time . "], 修改时间=[" . date("Y-m-d H:i:s", time()) . "], cacheTicketKey["
                    . $cacheTicketKey . "]");
            }

        } else {
            $ticket = $data->jsapi_ticket;
        }

        return $ticket;
    }
}

