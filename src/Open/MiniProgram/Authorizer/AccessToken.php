<?php

namespace Weirin\Wechat\Open\MiniProgram\Authorizer;

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
 * Class AuthorizerAccessToken
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
 * Class AccessToken
 * @package Weirin\Wechat\Open\MiniProgram\Authorizer
 */
class AccessToken
{
    /**
     * 用来保存 Access Token 的唯一标识，禁止修改.
     */
    const CACHE_KEY = 'OPEN_MINIPROGRAM_AUTHORIZER_ACCESS_TOKEN';

    /**
     * @inheritdoc
     */
    private static function cacheKey(string $appId)
    {
        return static::CACHE_KEY . '_' . $appId;
    }

    /**
     *
     * 刷新authorizer_access_token
     * 该API用于在授权方令牌（authorizer_access_token）失效时，可用刷新令牌（authorizer_refresh_token）获取新的令牌。
     * 请注意，此处token是2小时刷新一次，开发者需要自行进行token的缓存，避免token的获取次数达到每日的限定额度。
     * 缓存方法可以参考：http://mp.weixin.qq.com/wiki/2/88b2bf1265a707c031e51f26ca5e6512.html
     *
     * @param string $componentAppid
     * @param string $componentAccessToken
     * @param string $authorizerAppid
     * @param bool $forceRefresh
     * @return null
     */
    public static function get(string $componentAppid, string $componentAccessToken, string $authorizerAppid, bool $forceRefresh = false)
    {
        $cacheKey = static::cacheKey($authorizerAppid);

        $data = null;
        if (Cache::exists($cacheKey)) {
            $data = json_decode(Cache::get($cacheKey));
        }

        if (!isset($data->authorizer_access_token) || !isset($data->expires_time) || !isset($data->authorizer_refresh_token)) {
            Log::debug("公众号还没有授权:authorizerAppid[" . $authorizerAppid . "]");
            return null;
        }

        $time = time();

        //如果当前日期已经超过了过期时间，那么重新获取access token
        if ($time > $data->expires_time || true == $forceRefresh) {

            $res = static::refreshAuthAccessToken($componentAppid, $componentAccessToken, $authorizerAppid, $data->authorizer_refresh_token);

            if (isset($res['authorizer_access_token'])) {

                $data->expires_time = $time + $res['expires_in'] - 600; // 在令牌快过期时（比如1小时50分）再进行刷新
                $data->authorizer_access_token = $res['authorizer_access_token'];
                $data->authorizer_refresh_token = $res['authorizer_refresh_token'];

                //把数据缓存起来
                Cache::set($cacheKey, json_encode($data));

                //跟踪日志
                $logMessage = static::getLogMessage($data->authorizer_access_token, $data->expires_time, $authorizerAppid);
                if ($forceRefresh) {
                    Log::debug("AuthorizerAccessToken强制刷新: {$logMessage}");
                }  else {
                    Log::debug("AuthorizerAccessToken正常刷新: {$logMessage}");
                }

                return $data->authorizer_access_token;

            } else {
                Log::error(json_encode($res));
                return null;
            }

        } else {
            return $data->authorizer_access_token;
        }
    }

    /**
     * @param string $accessToken
     * @param int $expireTime
     * @param string $cacheKey
     * @return string
     */
    private static function getLogMessage(string $authorizerAccessToken, int $expireTime, string $authorizerAppid)
    {
        return "authorizer_access_token=[" .  $authorizerAccessToken . "], expires_time=["
            . $expireTime . "], 修改时间=["
            . date("Y-m-d H:i:s", time()) . "], authorizerAppid[" . $authorizerAppid . "]";
    }

    /**
     *
     * 刷新authorizer_access_token
     * 该API用于在授权方令牌（authorizer_access_token）失效时，可用刷新令牌（authorizer_refresh_token）获取新的令牌。
     * 请注意，此处token是2小时刷新一次，开发者需要自行进行token的缓存，避免token的获取次数达到每日的限定额度。
     * 缓存方法可以参考：http://mp.weixin.qq.com/wiki/2/88b2bf1265a707c031e51f26ca5e6512.html
     *
     * @param string $componentAppid
     * @param string $componentAccessToken
     * @param string $authorizerAppid
     * @param string $authorizerRefreshToken
     * @return bool|mixed
     */
    public static function refreshAuthAccessToken(string $componentAppid, string $componentAccessToken, string $authorizerAppid, string $authorizerRefreshToken)
    {
        $url = Urls::api('cgi-bin/component/api_authorizer_token', [
            'component_access_token' => $componentAccessToken
        ]);
        $data = [
            "component_appid" => $componentAppid,
            "authorizer_appid" => $authorizerAppid,
            "authorizer_refresh_token" => $authorizerRefreshToken,
        ];
        $result = Http::post($url, json_encode($data));
        Log::debug("获取（刷新）授权公众号的接口调用凭据（令牌）authorizer_access_token：" .$result );
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || !empty($json['errcode'])) {
                return null;
            }
            return $json;
        }
        return null;
    }


    /**
     * @param string $authorizerAppid
     * @param string $authorizerAccessToken
     * @param int $expiresIn
     * @param string $authorizerRefreshToken
     */
    public static function set(string $authorizerAppid, string $authorizerAccessToken, int $expiresIn, string $authorizerRefreshToken)
    {
        $data = new stdClass();
        $data->expires_time = time() + $expiresIn - 600; // 在令牌快过期时（比如1小时50分）再进行刷新
        $data->authorizer_access_token = $authorizerAccessToken;
        $data->authorizer_refresh_token = $authorizerRefreshToken;
        Cache::set(static::cacheKey($authorizerAppid), json_encode($data));
        Log::debug("设置authorizerAccessToken: " . static::getLogMessage($data->authorizer_access_token, $data->expires_time, $authorizerAppid));
    }
}