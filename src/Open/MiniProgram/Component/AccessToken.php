<?php

namespace Weirin\Wechat\Open\MiniProgram\Component;

use Weirin\Wechat\Http;
use Weirin\Wechat\Log;
use Weirin\Wechat\Cache;
use Weirin\Wechat\Urls;
use stdClass;
use function json_encode;
use function json_decode;
use function time;

/**
 *
 * Class AccessToken
 *
 * 第三方平台compoment_access_token是第三方平台的下文中接口的调用凭据，也叫做令牌
 *
 * 过期时间 7200 秒
 * 自动管理 access token ， 过期会重新获取.
 * 该模块 会 调用 yii 的 cache 模块缓存相关数据.
 *
 * @官方文档说明：
 *   component_access_token是公众号的全局唯一票据，公众号调用各接口时都需使用component_access_token。
 *   开发者需要进行妥善保存。
 *   component_access_token的存储至少要保留512个字符空间。
 *   component_access_token的有效期目前为2个小时，需定时刷新，重复获取将导致上次获取的component_access_token失效。
 *
 * @package Wechat
 */
class AccessToken
{
    /**
     * 用来保存 Access Token 的唯一标识，禁止修改.
     */
    const CACHE_KEY = 'OPEN_MINIPROGRAM_COMPONENT_ACCESS_TOKEN';

    /**
     * @inheritdoc
     */
    private static function cacheKey(string $appId)
    {
        return static::CACHE_KEY . '_' . $appId;
    }

    /**
     * @param $componentAppid
     * @param $componentAppsecret
     * @param $componentVerifyTicket
     * @param bool $forceRefresh
     * @return string
     */
    public static function get($componentAppid, $componentAppsecret, $componentVerifyTicket, $forceRefresh = false)
    {
        $cacheKey = static::cacheKey($componentAppid);

        $data = null;
        if (Cache::exists($cacheKey)) {
            $data = json_decode(Cache::get($cacheKey));
        }

        if (!isset($data->component_access_token) || !isset($data->expires_time)) {
            $data = new stdClass();
            $data->expires_time = 0;
            $data->component_access_token = '';
        }

        $time = time();

        //如果当前日期已经超过了过期时间，那么重新获取access token
        if ($time > $data->expires_time || true == $forceRefresh) {

            $url = Urls::api('cgi-bin/component/api_component_token');
            $params = [
                "component_appid" => $componentAppid ,
                "component_appsecret" =>  $componentAppsecret,
                "component_verify_ticket" => $componentVerifyTicket
            ];
            $jsonEncodedParams = json_encode($params);
            $res = json_decode(Http::post($url, $jsonEncodedParams));
            if (isset($res->component_access_token)) {

                $data->expires_time = $time + $res->expires_in - 600; // 在令牌快过期时（比如1小时50分）再进行刷新
                $data->component_access_token = $res->component_access_token;
                Cache::set($cacheKey, json_encode($data));

                // 跟踪日志
                $logMessage = static::getLogMessage($data->component_access_token, $data->expires_time, $componentAppid);
                if ($forceRefresh) {
                    Log::debug("ComponentAccessToken强制刷新: {$logMessage}");
                }  else {
                    Log::debug("ComponentAccessToken正常刷新: {$logMessage}");
                }

                return $data->component_access_token;
            } else {
                Log::debug($jsonEncodedParams . ":" . json_encode($res));
                return false;
            }

        } else {
            return $data->component_access_token;
        }
    }

    /**
     * @param string $accessToken
     * @param int $expireTime
     * @param string $cacheKey
     * @return string
     */
    private static function getLogMessage(string $componentAccessToken, int $expireTime, string $componentAppid)
    {
        return "component_access_token=["
            .  $componentAccessToken . "], expires_time=["
            . $expireTime . "], 修改时间=[" . date("Y-m-d H:i:s", time())
            . "], componentAppid[" . $componentAppid . "]";
    }
}