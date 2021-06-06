<?php

namespace Weirin\Wechat\Open\MiniProgram\Authorizer;

use Weirin\Wechat\Urls;

/**
 * 发送消息管理
 * Trait SendMessageTrait
 * @package Weirin\Wechat\Open\MiniProgram\Authorizer
 */
trait SendMessageTrait
{
    /**
     * 发送统一消息
     * https://developers.weixin.qq.com/miniprogram/dev/api/open-api/uniform-message/sendUniformMessage.html
     * @param $data
     * @return bool
     */
    public function sendUniformMessage(array $data, $authorizerAppid = '')
    {
        if (!$this->checkAccessToken()) {
            return false;
        }
        $accessToken = $this->getAccessToken();
        if($authorizerAppid){
            $accessToken = $this->getAccessToken(false, $authorizerAppid);
        }
        $url = Urls::api('cgi-bin/message/wxopen/template/uniform_send', [
            'access_token' => $accessToken
        ]);
        if($result = $this->httpPost($url, $data)){
            return $result['errcode'] == 0;
        }
        return false;
    }

    /**
     * 发送客服消息
     * https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1433751277
     * @param array $data {"touser":"OPENID","msgtype":"news","news":{...}}
     * @param string $authorizerAppid
     * @return bool
     */
    public function sendCustomMessage(array $data, string $authorizerAppid = '')
    {
        if (!$this->checkAccessToken()) {
            return false;
        }
        $accessToken = $this->getAccessToken();
        if($authorizerAppid){
            $accessToken = $this->getAccessToken(false, $authorizerAppid);
        }
        $url = Urls::api('cgi-bin/message/template/send', [
            'access_token' => $accessToken
        ]);
        if($result = $this->httpPost($url, $data)){
            return $result['errcode'] == 0;
        }
        return false;
    }
}