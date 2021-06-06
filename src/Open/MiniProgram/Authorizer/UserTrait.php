<?php

namespace Weirin\Wechat\Open\MiniProgram\Authorizer;

use Weirin\Wechat\Urls;

/**
 * 用户管理
 *
 * Trait UserTrait
 * @package Weirin\Wechat\Open\MiniProgram\Authorizer
 */
trait UserTrait
{
    /**
     * https://api.weixin.qq.com/cgi-bin/user/info?access_token=ACCESS_TOKEN&openid=OPENID
     *
     * 通过普通access token获取关注者详细信息
     * 关于网页授权access_token和普通access_token的区别：
     * https://mp.weixin.qq.com/wiki/4/9ac2e7b1f1d22e9e57260f6553822520.html
     * 已关注：
     * {
     *     "subscribe":1
     *     "openid":"oicCewkZUxj7tv92z57TiX7ehN28",
     *     "nickname":"\u534e\u5347@\u6d41\u5149\u6620\u753b",
     *     "sex":1,"language":"zh_CN",
     *     "city":"\u5357\u5b81",
     *     "province":"\u5e7f\u897f",
     *     "country":"\u4e2d\u56fd",
     *     "headimgurl":"http:\/\/wx.qlogo.cn\/mmopen\/hiamn2bFGxrN76Kq21WD5Tm9Cr91p68h52bWr2rEr9iaUCIib63jsibxDQvNsjPBtxD9HKZwiaAvNfPTpibYQxmefNQw\/0",
     *     "subscribe_time":1462272994,
     *     "remark":"",
     *     "groupid":0,
     *     "tagid_list":[]
     * }
     *
     * 未关注：
     * {
     *     "subscribe":0,
     *     "openid":"oicCewi22xTYCclqDOVEP-g-uawY",
     *     "tagid_list":[]
     * }
     *
     * @param string $openid
     * @return bool|mixed
     */
    public function getUserInfo(string $openid)
    {
        if (!$this->checkAccessToken()) {
            return false;
        }
        $url = Urls::api('cgi-bin/user/info', [
            'access_token' => $this->getAccessToken(),
            'openid' => $openid
        ]);
        if($result = $this->httpGet($url)){
            return $result;
        }
    }

    /**
     * https://api.weixin.qq.com/sns/jscode2session?
     * 获取小程序会话数据:  open_id 和 session_key
     * @param string $code
     * @return bool|mixed
     */
    public function getUserSession(string $code, string $authorizerAppid = '')
    {
        if (!$this->component->checkAccessToken()) {
            return false;
        }
        if($authorizerAppid == ''){
            $authorizerAppid = $this->authorizerAppid;
        }
        $url = Urls::api('sns/component/jscode2session', [
            'appid' => $authorizerAppid,
            'js_code' => $code,
            'grant_type' => 'authorization_code',
            'component_appid' => $this->component->getAppid(),
            'component_access_token' => $this->component->getAccessToken()
        ]);
        if ($result = $this->httpPost($url)) {
            return $result;
        }
    }
}